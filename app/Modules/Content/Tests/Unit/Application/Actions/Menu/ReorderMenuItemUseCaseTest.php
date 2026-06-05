<?php

namespace App\Modules\Content\Tests\Unit\Application\Actions\Menu;

use App\Modules\Content\Application\Actions\Menu\ReorderMenuItemUseCase;
use App\Modules\Content\Application\DTOs\Menu\ReorderMenuItemData;
use App\Modules\Content\Application\Interfaces\MenuItemRepositoryInterface;
use App\Modules\Content\Domain\Entities\MenuItemEntity;
use App\Modules\Content\Domain\Exceptions\MenuNotFoundException;
use App\Modules\Shared\Domain\Exceptions\AccessDeniedException;
use Mockery;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Tests\Trait\MockPermission;

class ReorderMenuItemUseCaseTest extends TestCase
{
    use MockPermission;

    private MenuItemRepositoryInterface $itemRepo;
    private ReorderMenuItemUseCase $useCase;

    public function getModuleName(): string { return 'content'; }
    public function getEntityName(): string { return 'data'; }

    protected function setUp(): void
    {
        parent::setUp();
        $this->itemRepo = Mockery::mock(MenuItemRepositoryInterface::class);
        $this->useCase = new ReorderMenuItemUseCase($this->itemRepo);
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
    public function reorders_item_successfully(): void
    {
        $itemId = 10;
        $newSort = 3;
        $existingItem = $this->createMenuItem($itemId);
        $dto = new ReorderMenuItemData(id: $itemId, newSort: $newSort);

        $this->itemRepo->shouldReceive('findById')
            ->with($itemId)
            ->once()
            ->andReturn($existingItem);

        $this->itemRepo->shouldReceive('updateSortOrder')
            ->once()
            ->with($itemId, $newSort)
            ->andReturnNull();

        $this->useCase->execute($dto, $this->mockUserPermission(edit: true));
        // Если исключений нет – успех
        $this->assertTrue(true);
    }

    #[Test]
    public function throws_access_denied_when_permission_missing(): void
    {
        $dto = new ReorderMenuItemData(id: 10, newSort: 2);

        $this->itemRepo->shouldNotReceive('findById');
        $this->itemRepo->shouldNotReceive('updateSortOrder');

        $this->expectException(AccessDeniedException::class);
        $this->useCase->execute($dto, $this->mockUserPermission()); // edit: false
    }

    #[Test]
    public function throws_exception_when_item_not_found(): void
    {
        $itemId = 999;
        $dto = new ReorderMenuItemData(id: $itemId, newSort: 5);

        $this->itemRepo->shouldReceive('findById')
            ->with($itemId)
            ->once()
            ->andReturn(null);

        $this->expectException(MenuNotFoundException::class);
        $this->useCase->execute($dto, $this->mockUserPermission(edit: true));
    }
}
