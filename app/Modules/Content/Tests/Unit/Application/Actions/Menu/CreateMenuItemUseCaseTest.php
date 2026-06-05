<?php

namespace App\Modules\Content\Tests\Unit\Application\Actions\Menu;

use App\Modules\Content\Application\Actions\Menu\CreateMenuItemUseCase;
use App\Modules\Content\Application\DTOs\Menu\MenuItemCreateData;
use App\Modules\Content\Application\Interfaces\MenuItemRepositoryInterface;
use App\Modules\Content\Domain\Entities\MenuItemEntity;
use App\Modules\Content\Domain\ValueObjects\ReferenceType;
use App\Modules\Shared\Domain\Exceptions\AccessDeniedException;
use Mockery;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Tests\Trait\MockPermission;

class CreateMenuItemUseCaseTest extends TestCase
{
    use MockPermission;

    private MenuItemRepositoryInterface $itemRepo;
    private CreateMenuItemUseCase $useCase;

    public function getModuleName(): string { return 'content'; }
    public function getEntityName(): string { return 'data'; }

    protected function setUp(): void
    {
        parent::setUp();
        $this->itemRepo = Mockery::mock(MenuItemRepositoryInterface::class);
        $this->useCase = new CreateMenuItemUseCase($this->itemRepo);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    #[Test]
    public function creates_menu_item_with_minimal_data_and_defaults(): void
    {
        $menuId = 1;
        $dto = new MenuItemCreateData(title: 'Home', parentId: null);

        $this->itemRepo->shouldReceive('save')
            ->once()
            ->with(Mockery::on(function (MenuItemEntity $item) use ($menuId) {
                return $item->menuId === $menuId
                    && $item->title === 'Home'
                    && $item->parentId === null
                    && $item->url === null
                    && $item->referenceType === null
                    && $item->referenceId === null
                    && $item->isActive === false; // теперь по умолчанию неактивен
            }))
            ->andReturnUsing(function (MenuItemEntity $item) {
                $item->id = 42;
                return $item;
            });

        $result = $this->useCase->execute($menuId, $dto, $this->mockUserPermission(create: true));

        $this->assertEquals(42, $result->id);
        $this->assertSame('Home', $result->title);
        $this->assertFalse($result->isActive);
    }

    #[Test]
    public function creates_menu_item_with_optional_fields(): void
    {
        $menuId = 2;
        $dto = new MenuItemCreateData(
            title: 'Catalog',
            parentId: 5,
            url: '/catalog',
            referenceType: 'page',
            referenceId: 10
        );

        $this->itemRepo->shouldReceive('save')
            ->once()
            ->with(Mockery::on(function (MenuItemEntity $item) use ($menuId) {
                return $item->menuId === $menuId
                    && $item->title === 'Catalog'
                    && $item->parentId === 5
                    && $item->url === '/catalog'
                    && $item->referenceType instanceof ReferenceType
                    && $item->referenceType->getValue() === 'page'
                    && $item->referenceId === 10
                    && $item->isActive === false;
            }))
            ->andReturnUsing(function (MenuItemEntity $item) {
                $item->id = 99;
                return $item;
            });

        $result = $this->useCase->execute($menuId, $dto, $this->mockUserPermission(create: true));

        $this->assertEquals(99, $result->id);
        $this->assertSame('Catalog', $result->title);
        $this->assertSame(5, $result->parentId);
        $this->assertSame('/catalog', $result->url);
        $this->assertSame('page', $result->referenceType->getValue());
        $this->assertSame(10, $result->referenceId);
    }

    #[Test]
    public function throws_access_denied_when_permission_missing(): void
    {
        $menuId = 1;
        $dto = new MenuItemCreateData(title: 'About');

        $this->itemRepo->shouldNotReceive('save');

        $this->expectException(AccessDeniedException::class);
        $this->useCase->execute($menuId, $dto, $this->mockUserPermission()); // create: false
    }
}
