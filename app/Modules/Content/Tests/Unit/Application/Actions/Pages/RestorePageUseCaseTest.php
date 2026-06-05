<?php

namespace App\Modules\Content\Tests\Unit\Application\Actions\Pages;

use App\Modules\Content\Application\Actions\Pages\RestorePageUseCase;
use App\Modules\Content\Application\Interfaces\PageRepositoryInterface;
use App\Modules\Content\Domain\Entities\PageEntity;
use App\Modules\Content\Domain\Exceptions\PageNotFoundException;
use App\Modules\Shared\Domain\Exceptions\AccessDeniedException;
use App\Modules\Shared\Domain\ValueObjects\Slug;
use Mockery;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Tests\Trait\MockPermission;

class RestorePageUseCaseTest extends TestCase
{
    use MockPermission;

    private PageRepositoryInterface $pageRepo;
    private RestorePageUseCase $useCase;

    public function getModuleName(): string { return 'content'; }
    public function getEntityName(): string { return 'data'; }

    protected function setUp(): void
    {
        parent::setUp();
        $this->pageRepo = Mockery::mock(PageRepositoryInterface::class);
        $this->useCase = new RestorePageUseCase($this->pageRepo);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    private function createTrashedPage(): PageEntity
    {
        $page = new PageEntity('Test Page', new Slug('test-page'));
        $page->id = 42;
        // имитируем удаление: устанавливаем deletedAt
        $page->deletedAt = new \DateTimeImmutable();
        return $page;
    }

    #[Test]
    public function restores_trashed_page_successfully(): void
    {
        $page = $this->createTrashedPage();

        $this->pageRepo->shouldReceive('findById')
            ->with(42, true)
            ->once()
            ->andReturn($page);

        $this->pageRepo->shouldReceive('restore')
            ->with(42)
            ->once();

        $this->useCase->execute(42, $this->mockUserPermission(delete: true));
        $this->assertTrue(true); // не было исключений
    }

    #[Test]
    public function throws_access_denied_when_missing_permission(): void
    {
        $this->pageRepo->shouldNotReceive('findById');
        $this->pageRepo->shouldNotReceive('restore');

        $this->expectException(AccessDeniedException::class);
        $this->useCase->execute(42, $this->mockUserPermission()); // delete: false
    }

    #[Test]
    public function throws_not_found_when_page_does_not_exist(): void
    {
        $this->pageRepo->shouldReceive('findById')
            ->with(999, true)
            ->once()
            ->andReturn(null);

        $this->expectException(PageNotFoundException::class);
        $this->useCase->execute(999, $this->mockUserPermission(delete: true));
    }

    #[Test]
    public function throws_not_found_when_page_not_trashed(): void
    {
        $page = new PageEntity('Active Page', new Slug('active'));
        $page->id = 10; // не удалена

        $this->pageRepo->shouldReceive('findById')
            ->with(10, true)
            ->once()
            ->andReturn($page);

        $this->expectException(PageNotFoundException::class);
        $this->useCase->execute(10, $this->mockUserPermission(delete: true));
    }
}
