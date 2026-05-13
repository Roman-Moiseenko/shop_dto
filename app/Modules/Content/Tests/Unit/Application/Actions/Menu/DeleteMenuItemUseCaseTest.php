<?php

namespace App\Modules\Content\Tests\Unit\Application\Actions\Menu;

use App\Modules\Content\Application\Actions\Menu\DeleteMenuItemUseCase;
use App\Modules\Content\Application\Interfaces\MenuItemRepositoryInterface;
use App\Modules\Content\Domain\Entities\MenuItemEntity;
use App\Modules\Content\Infrastructure\Exceptions\MenuNotFoundException;
use App\Modules\Shared\Infrastructure\Exceptions\AccessDeniedException;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Mockery;
use Tests\Trait\MockPermission;

class DeleteMenuItemUseCaseTest extends TestCase
{
    use MockPermission;

    private MenuItemRepositoryInterface $itemRepo;
    private DeleteMenuItemUseCase $useCase;

    public function getModuleName(): string { return 'content'; }
    public function getEntityName(): string { return 'data'; }

    protected function setUp(): void
    {
        parent::setUp();
        $this->itemRepo = Mockery::mock(MenuItemRepositoryInterface::class);
        $this->useCase = new DeleteMenuItemUseCase($this->itemRepo);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    private function createMenuItem(int $id = 10, int $menuId = 1): MenuItemEntity
    {
        $item = new MenuItemEntity(menuId: $menuId, title: 'Item');
        $item->id = $id;
        return $item;
    }

    #[Test]
    public function deletes_item_successfully(): void
    {
        $itemId = 10;
        $existingItem = $this->createMenuItem($itemId);

        $this->itemRepo->shouldReceive('findById')
            ->with($itemId)
            ->once()
            ->andReturn($existingItem);

        $this->itemRepo->shouldReceive('delete')
            ->once()
            ->with($itemId)
            ->andReturnNull();

        $this->useCase->execute($itemId, $this->mockUserPermission(delete: true));
        // Если исключений нет – успех
        $this->assertTrue(true);
    }

    #[Test]
    public function throws_access_denied_when_permission_missing(): void
    {
        $this->itemRepo->shouldNotReceive('findById');
        $this->itemRepo->shouldNotReceive('delete');

        $this->expectException(AccessDeniedException::class);
        $this->useCase->execute(10, $this->mockUserPermission()); // delete: false
    }

    #[Test]
    public function throws_exception_when_item_not_found(): void
    {
        $itemId = 999;

        $this->itemRepo->shouldReceive('findById')
            ->with($itemId)
            ->once()
            ->andReturn(null);

        $this->expectException(MenuNotFoundException::class);
        $this->useCase->execute($itemId, $this->mockUserPermission(delete: true));
    }
}
