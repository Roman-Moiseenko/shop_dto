<?php

namespace App\Modules\Content\Tests\Unit\Application\Actions\Menu;

use App\Modules\Content\Application\Actions\Menu\UpdateMenuUseCase;
use App\Modules\Content\Application\DTOs\Menu\MenuData;
use App\Modules\Content\Application\Interfaces\MenuRepositoryInterface;
use App\Modules\Content\Domain\Entities\MenuEntity;
use App\Modules\Content\Domain\Exceptions\MenuNotFoundException;
use App\Modules\Shared\Domain\Exceptions\AccessDeniedException;
use App\Modules\Shared\Domain\ValueObjects\Slug;
use Mockery;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Tests\Trait\MockPermission;

class UpdateMenuUseCaseTest extends TestCase
{
    use MockPermission;

    private MenuRepositoryInterface $menuRepo;
    private UpdateMenuUseCase $useCase;

    public function getModuleName(): string { return 'content'; }
    public function getEntityName(): string { return 'data'; }

    protected function setUp(): void
    {
        parent::setUp();
        $this->menuRepo = Mockery::mock(MenuRepositoryInterface::class);
        $this->useCase = new UpdateMenuUseCase($this->menuRepo);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    private function createExistingMenu(): MenuEntity
    {
        $menu = new MenuEntity('Old Name', new Slug('old-slug'), 'Old description', true);
        $menu->id = 10;
        return $menu;
    }

    #[Test]
    public function updates_all_fields_successfully(): void
    {
        $existing = $this->createExistingMenu();

        $this->menuRepo->shouldReceive('findById')
            ->with(10)
            ->once()
            ->andReturn($existing);

        $this->menuRepo->shouldReceive('save')
            ->once()
            ->with(Mockery::type(MenuEntity::class))
            ->andReturn($existing);

        $dto = new MenuData(
            name: 'New Name',
            slug: 'new-slug',
            description: 'New description',
            isActive: false
        );

        $result = $this->useCase->execute(10, $dto, $this->mockUserPermission(edit: true));

        $this->assertSame('New Name', $result->name);
        $this->assertSame('new-slug', (string) $result->slug);
        $this->assertSame('New description', $result->description);
        $this->assertFalse($result->isActive);
    }

    #[Test]
    public function auto_generates_slug_when_not_provided(): void
    {
        $existing = $this->createExistingMenu();

        $this->menuRepo->shouldReceive('findById')
            ->with(10)
            ->once()
            ->andReturn($existing);

        $this->menuRepo->shouldReceive('save')
            ->once()
            ->with(Mockery::type(MenuEntity::class))
            ->andReturn($existing);

        $dto = new MenuData(name: 'Новое имя', slug: null);

        $result = $this->useCase->execute(10, $dto, $this->mockUserPermission(edit: true));
        $this->assertSame('novoe-imya', (string) $result->slug);
    }

    #[Test]
    public function does_not_update_optional_fields_when_null(): void
    {
        $existing = $this->createExistingMenu();

        $this->menuRepo->shouldReceive('findById')
            ->with(10)
            ->once()
            ->andReturn($existing);

        $this->menuRepo->shouldReceive('save')
            ->once()
            ->with(Mockery::type(MenuEntity::class))
            ->andReturnUsing(function (MenuEntity $menu) {
                // проверяем, что опциональные поля не изменились
                if ($menu->description !== 'Old description' || $menu->isActive !== true) {
                    throw new \PHPUnit\Framework\ExpectationFailedException('Optional fields were modified');
                }
                return $menu;
            });

        $dto = new MenuData(name: 'New Name', slug: 'new-slug', description: null, isActive: null);

        $result = $this->useCase->execute(10, $dto, $this->mockUserPermission(edit: true));
        $this->assertSame('Old description', $result->description);
        $this->assertTrue($result->isActive);
    }

    #[Test]
    public function throws_access_denied_when_permission_missing(): void
    {
        $dto = new MenuData(name: 'Test', slug: 'test');

        $this->menuRepo->shouldNotReceive('findById');
        $this->menuRepo->shouldNotReceive('save');

        $this->expectException(AccessDeniedException::class);
        $this->useCase->execute(10, $dto, $this->mockUserPermission()); // edit: false
    }

    #[Test]
    public function throws_exception_when_menu_not_found(): void
    {
        $this->menuRepo->shouldReceive('findById')
            ->with(999)
            ->once()
            ->andReturn(null);

        $dto = new MenuData(name: 'Test', slug: 'test');

        $this->expectException(MenuNotFoundException::class);
        $this->useCase->execute(999, $dto, $this->mockUserPermission(edit: true));
    }
}
