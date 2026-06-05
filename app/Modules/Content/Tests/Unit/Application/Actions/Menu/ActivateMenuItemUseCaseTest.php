<?php

namespace App\Modules\Content\Tests\Unit\Application\Actions\Menu;

use App\Modules\Content\Application\Actions\Menu\ActivateMenuItemUseCase;
use App\Modules\Content\Application\Interfaces\MenuItemRepositoryInterface;
use App\Modules\Content\Domain\Entities\MenuItemEntity;
use App\Modules\Shared\Domain\Exceptions\AccessDeniedException;
use InvalidArgumentException;
use Mockery;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Tests\Trait\MockPermission;

class ActivateMenuItemUseCaseTest extends TestCase
{
    use MockPermission;

    private MenuItemRepositoryInterface $itemRepo;
    private ActivateMenuItemUseCase $useCase;

    public function getModuleName(): string { return 'content'; }
    public function getEntityName(): string { return 'data'; }

    protected function setUp(): void
    {
        parent::setUp();
        $this->itemRepo = Mockery::mock(MenuItemRepositoryInterface::class);
        $this->useCase = new ActivateMenuItemUseCase($this->itemRepo);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    #[Test]
    public function activates_item_successfully(): void
    {
        $itemId = 42;
        $item = new MenuItemEntity(menuId: 1, title: 'Test');
        $item->id = $itemId;
        $item->isActive = false; // изначально неактивен

        $this->itemRepo->shouldReceive('findById')
            ->with($itemId)
            ->once()
            ->andReturn($item);

        $this->itemRepo->shouldReceive('save')
            ->once()
            ->with(Mockery::on(function (MenuItemEntity $savedItem) {
                return $savedItem->isActive === true;
            }))
            ->andReturn($item);

        $this->useCase->execute($itemId, $this->mockUserPermission(edit: true));

        $this->assertTrue($item->isActive);
    }

    #[Test]
    public function throws_access_denied_when_permission_missing(): void
    {
        $this->itemRepo->shouldNotReceive('findById');
        $this->itemRepo->shouldNotReceive('save');

        $this->expectException(AccessDeniedException::class);
        $this->useCase->execute(42, $this->mockUserPermission()); // edit: false
    }

    #[Test]
    public function throws_exception_when_item_not_found(): void
    {
        $this->itemRepo->shouldReceive('findById')
            ->with(999)
            ->once()
            ->andReturn(null);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Пункт меню не найден');
        $this->useCase->execute(999, $this->mockUserPermission(edit: true));
    }
}
