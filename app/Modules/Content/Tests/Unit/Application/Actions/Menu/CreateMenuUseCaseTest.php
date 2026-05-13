<?php

namespace App\Modules\Content\Tests\Unit\Application\Actions\Menu;

use App\Modules\Content\Application\Actions\Menu\CreateMenuUseCase;
use App\Modules\Content\Application\DTOs\Menu\MenuData;
use App\Modules\Content\Application\Interfaces\MenuRepositoryInterface;
use App\Modules\Content\Domain\Entities\MenuEntity;
use App\Modules\Shared\Infrastructure\Exceptions\AccessDeniedException;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Mockery;
use Tests\Trait\MockPermission;

class CreateMenuUseCaseTest extends TestCase
{
    use MockPermission;

    private MenuRepositoryInterface $menuRepo;
    private CreateMenuUseCase $useCase;

    public function getModuleName(): string { return 'content'; }
    public function getEntityName(): string { return 'data'; }

    protected function setUp(): void
    {
        parent::setUp();
        $this->menuRepo = Mockery::mock(MenuRepositoryInterface::class);
        $this->useCase = new CreateMenuUseCase($this->menuRepo);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    #[Test]
    public function creates_menu_with_explicit_slug(): void
    {
        $dto = new MenuData(
            name: 'Главное меню',
            slug: 'main',
            description: 'Основное меню сайта',
            isActive: true
        );

        $this->menuRepo->shouldReceive('save')
            ->once()
            ->with(Mockery::on(function (MenuEntity $menu) {
                return $menu->name === 'Главное меню'
                    && (string) $menu->slug === 'main'
                    && $menu->description === 'Основное меню сайта'
                    && $menu->isActive === true;
            }))
            ->andReturnUsing(function (MenuEntity $menu) {
                $menu->id = 42;
                return $menu;
            });

        $result = $this->useCase->execute($dto, $this->mockUserPermission(create: true));

        $this->assertEquals(42, $result->id);
        $this->assertSame('Главное меню', $result->name);
        $this->assertSame('main', (string) $result->slug);
        $this->assertSame('Основное меню сайта', $result->description);
        $this->assertTrue($result->isActive);
    }

    #[Test]
    public function creates_menu_with_auto_slug_and_default_active(): void
    {
        $dto = new MenuData(
            name: 'Футер',
            slug: null,
            description: null,
            isActive: null
        );

        $this->menuRepo->shouldReceive('save')
            ->once()
            ->with(Mockery::on(function (MenuEntity $menu) {
                // slug должен быть сгенерирован из name (транслитерация "Футер" -> "futer")
                return (string) $menu->slug === 'futer'
                    && $menu->isActive === true;
            }))
            ->andReturnUsing(function (MenuEntity $menu) {
                $menu->id = 99;
                return $menu;
            });

        $result = $this->useCase->execute($dto, $this->mockUserPermission(create: true));

        $this->assertEquals(99, $result->id);
        $this->assertSame('futer', (string) $result->slug);
        $this->assertTrue($result->isActive);
    }

    #[Test]
    public function throws_access_denied_when_permission_missing(): void
    {
        $dto = new MenuData(name: 'Test', slug: 'test');

        $this->menuRepo->shouldNotReceive('save');

        $this->expectException(AccessDeniedException::class);
        $this->useCase->execute($dto, $this->mockUserPermission()); // create: false
    }
}
