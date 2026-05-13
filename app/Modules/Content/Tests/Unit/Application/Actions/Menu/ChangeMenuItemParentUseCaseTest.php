<?php

namespace App\Modules\Content\Tests\Unit\Application\Actions\Menu;
use App\Modules\Content\Application\Actions\Menu\ChangeMenuItemParentUseCase;
use App\Modules\Content\Application\DTOs\Menu\ChangeMenuItemParentData;
use App\Modules\Content\Application\Interfaces\MenuItemRepositoryInterface;
use App\Modules\Content\Domain\Entities\MenuItemEntity;
use App\Modules\Content\Infrastructure\Exceptions\MenuNotFoundException;
use App\Modules\Shared\Infrastructure\Exceptions\AccessDeniedException;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Mockery;
use Tests\Trait\MockPermission;

class ChangeMenuItemParentUseCaseTest extends TestCase
{
    use MockPermission;

    private MenuItemRepositoryInterface $itemRepo;
    private ChangeMenuItemParentUseCase $useCase;

    public function getModuleName(): string { return 'content'; }
    public function getEntityName(): string { return 'data'; }

    protected function setUp(): void
    {
        parent::setUp();
        $this->itemRepo = Mockery::mock(MenuItemRepositoryInterface::class);
        $this->useCase = new ChangeMenuItemParentUseCase($this->itemRepo);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    private function createMenuItem(int $id = 10, int $menuId = 1, ?int $parentId = null): MenuItemEntity
    {
        $item = new MenuItemEntity(menuId: $menuId, title: 'Item', parentId: $parentId);
        $item->id = $id;
        return $item;
    }

    #[Test]
    public function changes_parent_successfully(): void
    {
        $itemId = 10;
        $newParentId = 5;
        $existingItem = $this->createMenuItem($itemId, 1, null);
        $dto = new ChangeMenuItemParentData(newParentId: $newParentId);

        $this->itemRepo->shouldReceive('findById')
            ->with($itemId)
            ->once()
            ->andReturn($existingItem);

        $this->itemRepo->shouldReceive('changeParent')
            ->once()
            ->with($itemId, $newParentId)
            ->andReturnNull();

        $this->useCase->execute($itemId, $dto, $this->mockUserPermission(edit: true));
        // Если исключений нет — успех
        $this->assertTrue(true);
    }

    #[Test]
    public function changes_parent_to_null(): void
    {
        $itemId = 10;
        $existingItem = $this->createMenuItem($itemId, 1, 5);
        $dto = new ChangeMenuItemParentData(newParentId: null);

        $this->itemRepo->shouldReceive('findById')
            ->with($itemId)
            ->once()
            ->andReturn($existingItem);

        $this->itemRepo->shouldReceive('changeParent')
            ->once()
            ->with($itemId, null)
            ->andReturnNull();

        $this->useCase->execute($itemId, $dto, $this->mockUserPermission(edit: true));
        $this->assertTrue(true);
    }

    #[Test]
    public function throws_access_denied_when_permission_missing(): void
    {
        $dto = new ChangeMenuItemParentData(newParentId: 5);
        $this->itemRepo->shouldNotReceive('findById');
        $this->itemRepo->shouldNotReceive('changeParent');

        $this->expectException(AccessDeniedException::class);
        $this->useCase->execute(10, $dto, $this->mockUserPermission()); // edit: false
    }

    #[Test]
    public function throws_exception_when_item_not_found(): void
    {
        $itemId = 999;
        $dto = new ChangeMenuItemParentData(newParentId: 5);

        $this->itemRepo->shouldReceive('findById')
            ->with($itemId)
            ->once()
            ->andReturn(null);

        $this->expectException(MenuNotFoundException::class);
        $this->useCase->execute($itemId, $dto, $this->mockUserPermission(edit: true));
    }
}
