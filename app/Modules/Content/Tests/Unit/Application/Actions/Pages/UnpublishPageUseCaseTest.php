<?php

namespace App\Modules\Content\Tests\Unit\Application\Actions\Pages;
use App\Modules\Content\Application\Actions\Pages\UnpublishPageUseCase;
use App\Modules\Content\Application\Interfaces\PageRepositoryInterface;
use App\Modules\Content\Domain\Entities\PageEntity;
use App\Modules\Content\Domain\Exceptions\PageNotFoundException;
use App\Modules\Shared\Domain\Exceptions\AccessDeniedException;
use App\Modules\Shared\Domain\ValueObjects\Slug;
use Mockery;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Tests\Trait\MockPermission;

class UnpublishPageUseCaseTest extends TestCase
{
    use MockPermission;

    private PageRepositoryInterface $pageRepo;
    private UnpublishPageUseCase $useCase;

    public function getModuleName(): string { return 'content'; }
    public function getEntityName(): string { return 'data'; }

    protected function setUp(): void
    {
        parent::setUp();
        $this->pageRepo = Mockery::mock(PageRepositoryInterface::class);
        $this->useCase = new UnpublishPageUseCase($this->pageRepo);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    private function createPublishedPage(): PageEntity
    {
        $page = new PageEntity('Published Page', new Slug('published-page'));
        $page->id = 10;
        $page->publish(); // устанавливает статус published
        return $page;
    }

    #[Test]
    public function unpublishes_page_successfully(): void
    {
        $page = $this->createPublishedPage();

        $this->pageRepo->shouldReceive('findById')
            ->with(10)
            ->once()
            ->andReturn($page);

        $this->pageRepo->shouldReceive('save')
            ->once()
            ->with(Mockery::on(function (PageEntity $p) {
                return !$p->isPublished(); // статус draft
            }))
            ->andReturn($page);

        $this->useCase->execute(10, $this->mockUserPermission(edit: true));
        // Исключений нет
        $this->assertTrue(true);
    }

    #[Test]
    public function throws_access_denied_when_missing_edit_permission(): void
    {
        $this->pageRepo->shouldNotReceive('findById');
        $this->pageRepo->shouldNotReceive('save');

        $this->expectException(AccessDeniedException::class);
        $this->useCase->execute(10, $this->mockUserPermission()); // edit: false
    }

    #[Test]
    public function throws_not_found_when_page_does_not_exist(): void
    {
        $this->pageRepo->shouldReceive('findById')
            ->with(999)
            ->once()
            ->andReturn(null);

        $this->expectException(PageNotFoundException::class);
        $this->useCase->execute(999, $this->mockUserPermission(edit: true));
    }
}
