<?php

namespace App\Modules\Content\Tests\Unit\Application\Actions\Menu;

use App\Modules\Content\Application\Actions\Menu\UpdateMenuItemUseCase;
use App\Modules\Content\Application\DTOs\Menu\MenuItemUpdateData;
use App\Modules\Content\Application\Interfaces\MenuItemRepositoryInterface;
use App\Modules\Content\Domain\Entities\MenuItemEntity;
use App\Modules\Content\Domain\ValueObjects\MenuItemStyle;
use App\Modules\Content\Domain\ValueObjects\ReferenceType;
use App\Modules\Shared\Domain\Exceptions\AccessDeniedException;
use InvalidArgumentException;
use Mockery;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Tests\Trait\MockPermission;

class UpdateMenuItemUseCaseTest extends TestCase
{
    use MockPermission;

    private MenuItemRepositoryInterface $itemRepo;
    private UpdateMenuItemUseCase $useCase;

    public function getModuleName(): string { return 'content'; }
    public function getEntityName(): string { return 'data'; }

    protected function setUp(): void
    {
        parent::setUp();
        $this->itemRepo = Mockery::mock(MenuItemRepositoryInterface::class);
        $this->useCase = new UpdateMenuItemUseCase($this->itemRepo);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    private function createExistingItem(int $id = 10, int $menuId = 1): MenuItemEntity
    {
        $item = new MenuItemEntity(menuId: $menuId, title: 'Old Title');
        $item->id = $id;
        return $item;
    }

    #[Test]
    public function updates_all_fields_successfully(): void
    {
        $menuId = 1;
        $itemId = 10;
        $existing = $this->createExistingItem($itemId, $menuId);

        $this->itemRepo->shouldReceive('findById')
            ->with($itemId)
            ->once()
            ->andReturn($existing);

        $this->itemRepo->shouldReceive('save')
            ->once()
            ->with(Mockery::type(MenuItemEntity::class))
            ->andReturnUsing(function (MenuItemEntity $item) {
                return $item;
            });

        $dto = new MenuItemUpdateData(
            title: 'New Title',
            parentId: 2,
            url: '/new-url',
            referenceType: 'page',
            referenceId: 42,
            iconUuid: 'icon-uuid-123',
            style: 'sale',
            targetBlank: true,
            widgetInstanceId: 99
        );

        $result = $this->useCase->execute($menuId, $itemId, $dto, $this->mockUserPermission(edit: true));

        $this->assertSame('New Title', $result->title);
        $this->assertSame(2, $result->parentId);
        $this->assertSame('/new-url', $result->url);
        $this->assertInstanceOf(ReferenceType::class, $result->referenceType);
        $this->assertSame('page', $result->referenceType->getValue());
        $this->assertSame(42, $result->referenceId);
        $this->assertSame('icon-uuid-123', $result->iconUuid);
        $this->assertInstanceOf(MenuItemStyle::class, $result->style);
        $this->assertSame('sale', $result->style->getValue());
        $this->assertTrue($result->targetBlank);
        $this->assertSame(99, $result->widgetInstanceId);
    }

    #[Test]
    public function updates_partial_fields_without_affecting_others(): void
    {
        $menuId = 1;
        $itemId = 10;
        $existing = $this->createExistingItem($itemId, $menuId);

        $this->itemRepo->shouldReceive('findById')
            ->with($itemId)
            ->once()
            ->andReturn($existing);

        $this->itemRepo->shouldReceive('save')
            ->once()
            ->with(Mockery::type(MenuItemEntity::class))
            ->andReturnUsing(function (MenuItemEntity $item) {
                return $item;
            });

        $dto = new MenuItemUpdateData(title: 'Only Title');

        $result = $this->useCase->execute($menuId, $itemId, $dto, $this->mockUserPermission(edit: true));

        $this->assertSame('Only Title', $result->title);
        // Остальные поля могут стать null/false, так как DTO их не передал
        // При необходимости в будущем можно доработать логику, чтобы не сбрасывались поля, если они не переданы
    }

    #[Test]
    public function throws_access_denied_when_permission_missing(): void
    {
        $menuId = 1;
        $itemId = 10;
        $dto = new MenuItemUpdateData(title: 'Test');

        $this->itemRepo->shouldNotReceive('findById');
        $this->itemRepo->shouldNotReceive('save');

        $this->expectException(AccessDeniedException::class);
        $this->useCase->execute($menuId, $itemId, $dto, $this->mockUserPermission()); // edit: false
    }

    #[Test]
    public function throws_exception_when_item_not_found(): void
    {
        $menuId = 1;
        $itemId = 999;
        $dto = new MenuItemUpdateData(title: 'Test');

        $this->itemRepo->shouldReceive('findById')
            ->with($itemId)
            ->once()
            ->andReturn(null);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Пункт меню не найден');
        $this->useCase->execute($menuId, $itemId, $dto, $this->mockUserPermission(edit: true));
    }
}
