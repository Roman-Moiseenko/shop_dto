<?php

namespace App\Modules\Content\Tests\Unit\Application\Actions\Menu;

use App\Modules\Content\Application\Actions\Menu\IndexMenuItemsUseCase;
use App\Modules\Content\Application\Interfaces\MenuItemRepositoryInterface;
use App\Modules\Content\Domain\Entities\MenuItemEntity;
use App\Modules\Shared\Domain\Exceptions\AccessDeniedException;
use Mockery;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Tests\Trait\MockPermission;

class IndexMenuItemsUseCaseTest extends TestCase
{
    use MockPermission;

    private MenuItemRepositoryInterface $itemRepo;
    private IndexMenuItemsUseCase $useCase;

    public function getModuleName(): string { return 'content'; }
    public function getEntityName(): string { return 'data'; }

    protected function setUp(): void
    {
        parent::setUp();
        $this->itemRepo = Mockery::mock(MenuItemRepositoryInterface::class);
        $this->useCase = new IndexMenuItemsUseCase($this->itemRepo);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    #[Test]
    public function returns_tree_when_view_permission_granted(): void
    {
        $menuId = 1;
        $root1 = new MenuItemEntity(menuId: $menuId, title: 'Home');
        $root1->id = 1;
        $child1 = new MenuItemEntity(menuId: $menuId, title: 'About', parentId: 1);
        $child1->id = 2;
        $root2 = new MenuItemEntity(menuId: $menuId, title: 'Catalog');
        $root2->id = 3;

        $tree = [$root1, $root2];
        // Предполагаем, что репозиторий уже строит дерево и отдаёт корни с дочерними
        // Для теста можно не мокать внутреннее устройство, просто проверим возврат массива

        $this->itemRepo->shouldReceive('getTree')
            ->with($menuId)
            ->once()
            ->andReturn($tree);

        $result = $this->useCase->execute($menuId, $this->mockUserPermission(view: true));

        $this->assertSame($tree, $result);
        $this->assertCount(2, $result);
    }

    #[Test]
    public function throws_access_denied_when_view_permission_absent(): void
    {
        $this->itemRepo->shouldNotReceive('getTree');
        $this->expectException(AccessDeniedException::class);
        $this->useCase->execute(1, $this->mockUserPermission()); // view: false
    }
}
