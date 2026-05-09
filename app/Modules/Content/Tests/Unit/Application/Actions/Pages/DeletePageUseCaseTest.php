<?php

namespace App\Modules\Content\Tests\Unit\Application\Actions\Pages;

use App\Modules\Content\Application\Actions\Pages\DeletePageUseCase;
use App\Modules\Content\Application\Interfaces\PageRepositoryInterface;
use App\Modules\Content\Domain\Entities\PageEntity;
use App\Modules\Content\Infrastructure\Exceptions\PageNotFoundException;
use App\Modules\Shared\Domain\ValueObjects\Slug;
use App\Modules\Shared\Infrastructure\Exceptions\AccessDeniedException;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Mockery;
use Tests\Trait\MockPermission;

class DeletePageUseCaseTest extends TestCase
{
    use MockPermission;

    private PageRepositoryInterface $pageRepo;
    private DeletePageUseCase $useCase;

    public function getModuleName(): string { return 'content'; }
    public function getEntityName(): string { return 'data'; }

    protected function setUp(): void
    {
        parent::setUp();
        $this->pageRepo = Mockery::mock(PageRepositoryInterface::class);
        $this->useCase = new DeletePageUseCase($this->pageRepo);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    private function createPage(): PageEntity
    {
        $page = new PageEntity('Test Page', new Slug('test-page'));
        $page->id = 42;
        return $page;
    }

    #[Test]
    public function deletes_page_successfully(): void
    {
        $page = $this->createPage();

        $this->pageRepo->shouldReceive('findById')
            ->with(42)
            ->once()
            ->andReturn($page);

        $this->pageRepo->shouldReceive('delete')
            ->with(42)
            ->once();

        $this->useCase->execute(42, $this->mockUserPermission(delete: true));
        // Если исключений нет — успех
        $this->assertTrue(true);
    }

    #[Test]
    public function throws_access_denied_when_missing_permission(): void
    {
        $this->pageRepo->shouldNotReceive('findById');
        $this->pageRepo->shouldNotReceive('delete');

        $this->expectException(AccessDeniedException::class);
        $this->useCase->execute(42, $this->mockUserPermission());
    }

    #[Test]
    public function throws_not_found_when_page_does_not_exist(): void
    {
        $this->pageRepo->shouldReceive('findById')
            ->with(999)
            ->once()
            ->andReturn(null);

        $this->expectException(PageNotFoundException::class);
        $this->useCase->execute(999, $this->mockUserPermission(delete: true));
    }
}
