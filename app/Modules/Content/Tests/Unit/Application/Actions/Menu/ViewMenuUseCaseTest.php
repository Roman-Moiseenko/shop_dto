<?php

namespace App\Modules\Content\Tests\Unit\Application\Actions\Menu;

use App\Modules\Content\Application\Actions\Menu\ViewMenuUseCase;
use App\Modules\Content\Application\Interfaces\MenuRepositoryInterface;
use App\Modules\Content\Domain\Entities\MenuEntity;
use App\Modules\Content\Infrastructure\Exceptions\MenuNotFoundException;
use App\Modules\Shared\Domain\ValueObjects\Slug;
use App\Modules\Shared\Infrastructure\Exceptions\AccessDeniedException;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Mockery;
use Tests\Trait\MockPermission;

class ViewMenuUseCaseTest extends TestCase
{
    use MockPermission;

    private MenuRepositoryInterface $menuRepo;
    private ViewMenuUseCase $useCase;

    public function getModuleName(): string { return 'content'; }
    public function getEntityName(): string { return 'data'; }

    protected function setUp(): void
    {
        parent::setUp();
        $this->menuRepo = Mockery::mock(MenuRepositoryInterface::class);
        $this->useCase = new ViewMenuUseCase($this->menuRepo);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    private function createMenu(): MenuEntity
    {
        $menu = new MenuEntity('Main Menu', new Slug('main'));
        $menu->id = 10;
        return $menu;
    }

    #[Test]
    public function returns_menu_when_found_and_view_permission_granted(): void
    {
        $menu = $this->createMenu();

        $this->menuRepo->shouldReceive('findById')
            ->with(10)
            ->once()
            ->andReturn($menu);

        $result = $this->useCase->execute(10, $this->mockUserPermission(view: true));
        $this->assertSame($menu, $result);
    }

    #[Test]
    public function throws_access_denied_when_view_permission_absent(): void
    {
        $this->menuRepo->shouldNotReceive('findById');

        $this->expectException(AccessDeniedException::class);
        $this->useCase->execute(10, $this->mockUserPermission()); // view: false
    }

    #[Test]
    public function throws_exception_when_menu_not_found(): void
    {
        $this->menuRepo->shouldReceive('findById')
            ->with(999)
            ->once()
            ->andReturn(null);

        $this->expectException(MenuNotFoundException::class);
        $this->useCase->execute(999, $this->mockUserPermission(view: true));
    }
}
