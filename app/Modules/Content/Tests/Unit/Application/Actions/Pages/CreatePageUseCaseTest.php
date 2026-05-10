<?php

namespace App\Modules\Content\Tests\Unit\Application\Actions\Pages;

use App\Modules\Content\Application\Actions\Pages\CreatePageUseCase;
use App\Modules\Content\Application\DTOs\Page\PageCreateData;
use App\Modules\Content\Application\Interfaces\PageRepositoryInterface;
use App\Modules\Content\Domain\Entities\PageEntity;
use App\Modules\Shared\Infrastructure\Exceptions\AccessDeniedException;
use Mockery;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Tests\Trait\MockPermission;

class CreatePageUseCaseTest extends TestCase
{
    use MockPermission;

    private PageRepositoryInterface $pageRepo;
    private CreatePageUseCase $useCase;

    public function getModuleName(): string { return 'content'; }
    public function getEntityName(): string { return 'data'; }

    protected function setUp(): void
    {
        parent::setUp();
        $this->pageRepo = Mockery::mock(PageRepositoryInterface::class);
        $this->useCase = new CreatePageUseCase($this->pageRepo);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    #[Test]
    public function creates_page_with_minimal_data_and_returns_saved_entity(): void
    {
        $dto = new PageCreateData(
            title: 'Test Page',
            slug: 'test-page',
        );

        $this->pageRepo->shouldReceive('save')
            ->once()
            ->with(Mockery::type(PageEntity::class))
            ->andReturnUsing(function (PageEntity $page) {
                $page->id = 42;
                return $page;
            });

        $result = $this->useCase->execute($dto, $this->mockUserPermission(create: true));

        $this->assertSame(42, $result->id);
        $this->assertSame('Test Page', $result->title);
        $this->assertSame('test-page', (string) $result->slug);
        $this->assertTrue($result->contentType->isSimple());
        $this->assertTrue($result->status->isDraft());
        $this->assertNull($result->meta);
        $this->assertNull($result->template);
    }

    #[Test]
    public function creates_page_with_all_optional_fields(): void
    {
        $dto = new PageCreateData(
            title: 'Full Page',
            slug: 'full-page',
            contentType: 'widget_based',
            content: '<p>Hello</p>',
            status: 'published',
            meta: ['title' => 'SEO Title', 'description' => 'SEO Desc'],
            template: 'landing',
            authorId: 123,
        );

        $this->pageRepo->shouldReceive('save')
            ->once()
            ->with(Mockery::type(PageEntity::class))
            ->andReturnUsing(function (PageEntity $page) {
                $page->id = 99;
                // Имитируем, что у опубликованной страницы есть publishedAt
                return $page;
            });

        $result = $this->useCase->execute($dto, $this->mockUserPermission(create: true));

        $this->assertTrue($result->isWidgetBased());
        $this->assertSame('<p>Hello</p>', $result->content);
        $this->assertTrue($result->isPublished());
        $this->assertSame('SEO Title', $result->meta->getTitle());
        $this->assertSame('landing', (string) $result->template);
        $this->assertSame(123, $result->getAuthorId());
    }

    #[Test]
    public function throws_access_denied_when_missing_create_permission(): void
    {
        $dto = new PageCreateData(title: 'Test', slug: 'test');

        $this->pageRepo->shouldNotReceive('save');

        $this->expectException(AccessDeniedException::class);
        $this->useCase->execute($dto, $this->mockUserPermission()); // create: false
    }
}
