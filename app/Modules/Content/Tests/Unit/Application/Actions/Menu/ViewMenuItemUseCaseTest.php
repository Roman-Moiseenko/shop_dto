<?php

namespace App\Modules\Content\Tests\Unit\Application\Actions\Menu;

use App\Modules\Content\Application\Actions\Menu\ViewMenuItemUseCase;
use App\Modules\Content\Application\Interfaces\MenuItemRepositoryInterface;
use App\Modules\Content\Domain\Entities\MenuItemEntity;
use App\Modules\Content\Infrastructure\Exceptions\MenuNotFoundException;
use App\Modules\Shared\Infrastructure\Exceptions\AccessDeniedException;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Mockery;
use Tests\Trait\MockPermission;

class ViewMenuItemUseCaseTest extends TestCase
{
    use MockPermission;

    private MenuItemRepositoryInterface $itemRepo;
    private ViewMenuItemUseCase $useCase;

    public function getModuleName(): string { return 'content'; }
    public function getEntityName(): string { return 'data'; }

    protected function setUp(): void
    {
        parent::setUp();
        $this->itemRepo = Mockery::mock(MenuItemRepositoryInterface::class);
        $this->useCase = new ViewMenuItemUseCase($this->itemRepo);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    private function createMenuItem(int $id = 10, int $menuId = 1): MenuItemEntity
    {
        $item = new MenuItemEntity(menuId: $menuId, title: 'Test Item');
        $item->id = $id;
        return $item;
    }

    #[Test]
    public function returns_menu_item_when_found_and_view_permission_granted(): void
    {
        $itemId = 10;
        $existingItem = $this->createMenuItem($itemId);

        $this->itemRepo->shouldReceive('findById')
            ->with($itemId)
            ->once()
            ->andReturn($existingItem);

        $result = $this->useCase->execute($itemId, $this->mockUserPermission(view: true));
        $this->assertSame($existingItem, $result);
    }

    #[Test]
    public function throws_access_denied_when_view_permission_absent(): void
    {
        $this->itemRepo->shouldNotReceive('findById');

        $this->expectException(AccessDeniedException::class);
        $this->useCase->execute(10, $this->mockUserPermission()); // view: false
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
        $this->useCase->execute($itemId, $this->mockUserPermission(view: true));
    }
}
