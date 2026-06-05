<?php

namespace App\Modules\Content\Tests\Unit\Application\Actions\Menu;

use App\Modules\Content\Application\Actions\Menu\DeleteMenuUseCase;
use App\Modules\Content\Application\Interfaces\MenuRepositoryInterface;
use App\Modules\Content\Domain\Entities\MenuEntity;
use App\Modules\Content\Domain\Exceptions\MenuNotFoundException;
use App\Modules\Shared\Domain\Exceptions\AccessDeniedException;
use App\Modules\Shared\Domain\ValueObjects\Slug;
use Mockery;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Tests\Trait\MockPermission;

class DeleteMenuUseCaseTest extends TestCase
{
    use MockPermission;

    private MenuRepositoryInterface $menuRepo;
    private DeleteMenuUseCase $useCase;

    public function getModuleName(): string { return 'content'; }
    public function getEntityName(): string { return 'data'; }

    protected function setUp(): void
    {
        parent::setUp();
        $this->menuRepo = Mockery::mock(MenuRepositoryInterface::class);
        $this->useCase = new DeleteMenuUseCase($this->menuRepo);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    #[Test]
    public function deletes_menu_successfully(): void
    {
        $menuId = 1;
        $existingMenu = new MenuEntity('Main Menu', new Slug('main'));
        $existingMenu->id = $menuId;

        $this->menuRepo->shouldReceive('findById')
            ->with($menuId)
            ->once()
            ->andReturn($existingMenu);

        $this->menuRepo->shouldReceive('delete')
            ->once()
            ->with($menuId)
            ->andReturnNull();

        $this->useCase->execute($menuId, $this->mockUserPermission(delete: true));
        // Если исключений нет — успех
        $this->assertTrue(true);
    }

    #[Test]
    public function throws_access_denied_when_permission_missing(): void
    {
        $this->menuRepo->shouldNotReceive('findById');
        $this->menuRepo->shouldNotReceive('delete');

        $this->expectException(AccessDeniedException::class);
        $this->useCase->execute(1, $this->mockUserPermission()); // delete: false
    }

    #[Test]
    public function throws_exception_when_menu_not_found(): void
    {
        $menuId = 999;

        $this->menuRepo->shouldReceive('findById')
            ->with($menuId)
            ->once()
            ->andReturn(null);

        $this->expectException(MenuNotFoundException::class);
        $this->useCase->execute($menuId, $this->mockUserPermission(delete: true));
    }
}
