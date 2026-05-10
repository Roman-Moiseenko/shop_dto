<?php

namespace App\Modules\Content\Tests\Unit\Application\Actions\Pages;

use App\Modules\Content\Application\Actions\Pages\UpdatePageUseCase;
use App\Modules\Content\Application\DTOs\Page\PageUpdateData;
use App\Modules\Content\Application\Interfaces\PageRepositoryInterface;
use App\Modules\Content\Domain\Entities\PageEntity;
use App\Modules\Content\Domain\ValueObjects\ContentType;
use App\Modules\Content\Domain\ValueObjects\Meta;
use App\Modules\Content\Domain\ValueObjects\PageStatus;
use App\Modules\Content\Domain\ValueObjects\PageTemplate;
use App\Modules\Content\Infrastructure\Exceptions\PageNotFoundException;
use App\Modules\Shared\Domain\ValueObjects\Slug;
use App\Modules\Shared\Infrastructure\Exceptions\AccessDeniedException;
use Mockery;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Tests\Trait\MockPermission;

class UpdatePageUseCaseTest extends TestCase
{
    use     MockPermission;

    private PageRepositoryInterface $pageRepo;
    private UpdatePageUseCase $useCase;

    public function getModuleName(): string { return 'content'; }
    public function getEntityName(): string { return 'data'; }

    protected function setUp(): void
    {
        parent::setUp();
        $this->pageRepo = Mockery::mock(PageRepositoryInterface::class);
        $this->useCase = new UpdatePageUseCase($this->pageRepo);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    private function createPage(): PageEntity
    {
        $page = new PageEntity(
            'Old Title',
            new Slug('old-slug'),
            ContentType::simple(),
            'Old content',
            PageStatus::draft(),
            new Meta(['title' => 'Old SEO', 'description' => 'Old desc']),
            1,
            PageTemplate::default()
        );
        $page->id = 42;
        return $page;
    }

    #[Test]
    public function updates_all_fields_successfully(): void
    {
        $existing = $this->createPage();

        $this->pageRepo->shouldReceive('findById')
            ->with(42)
            ->once()
            ->andReturn($existing);

        $this->pageRepo->shouldReceive('save')
            ->once()
            ->with(Mockery::on(function (PageEntity $page) {
                return $page->title === 'New Title'
                    && (string)$page->slug === 'new-slug'
                    && $page->contentType->isWidgetBased()
                    && $page->content === 'New content'
                    && $page->isPublished()
                    && $page->meta->getTitle() === 'New SEO'
                    && (string)$page->template === 'landing'
                    && $page->getAuthorId() === 2;
            }))
            ->andReturn($existing);

        $dto = new PageUpdateData(
            title: 'New Title',
            slug: 'new-slug',
            contentType: 'widget_based',
            content: 'New content',
            status: 'published',
            meta: ['title' => 'New SEO'],
            template: 'landing',
            authorId: 2,
        );

        $result = $this->useCase->execute(42, $dto, $this->mockUserPermission(edit: true));

        $this->assertSame('New Title', $result->title);
        $this->assertSame('new-slug', (string)$result->slug);
        $this->assertTrue($result->isPublished());
    }

    #[Test]
    public function updates_partial_fields_without_affecting_others(): void
    {
        $existing = $this->createPage();

        $this->pageRepo->shouldReceive('findById')
            ->with(42)
            ->once()
            ->andReturn($existing);

        $this->pageRepo->shouldReceive('save')
            ->once()
            ->with(Mockery::on(function (PageEntity $page) {
                // Изменился только title, остальное прежнее
                return $page->title === 'Only Title'
                    && (string)$page->slug === 'old-slug'
                    && $page->contentType->isSimple();
            }))
            ->andReturn($existing);

        $dto = new PageUpdateData(title: 'Only Title');

        $result = $this->useCase->execute(42, $dto, $this->mockUserPermission(edit: true));
        $this->assertSame('Only Title', $result->title);
    }

    #[Test]
    public function throws_access_denied_when_missing_edit_permission(): void
    {
        $dto = new PageUpdateData(title: 'New');

        $this->pageRepo->shouldNotReceive('findById');
        $this->pageRepo->shouldNotReceive('save');

        $this->expectException(AccessDeniedException::class);
        $this->useCase->execute(42, $dto, $this->mockUserPermission()); // edit: false
    }

    #[Test]
    public function throws_not_found_when_page_does_not_exist(): void
    {
        $this->pageRepo->shouldReceive('findById')
            ->with(999)
            ->once()
            ->andReturn(null);

        $dto = new PageUpdateData(title: 'New');

        $this->expectException(PageNotFoundException::class);
        $this->useCase->execute(999, $dto, $this->mockUserPermission(edit: true));
    }
}
