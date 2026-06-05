<?php

namespace App\Modules\Content\Tests\Unit\Application\Actions\Menu;

use App\Modules\Content\Application\Actions\Menu\IndexMenusUseCase;
use App\Modules\Content\Application\Interfaces\MenuRepositoryInterface;
use App\Modules\Content\Domain\Entities\MenuEntity;
use App\Modules\Shared\Domain\Exceptions\AccessDeniedException;
use App\Modules\Shared\Domain\ValueObjects\Slug;
use Mockery;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Tests\Trait\MockPermission;

class IndexMenusUseCaseTest extends TestCase
{
    use MockPermission;

    private MenuRepositoryInterface $menuRepo;
    private IndexMenusUseCase $useCase;

    public function getModuleName(): string { return 'content'; }
    public function getEntityName(): string { return 'data'; }

    protected function setUp(): void
    {
        parent::setUp();
        $this->menuRepo = Mockery::mock(MenuRepositoryInterface::class);
        $this->useCase = new IndexMenusUseCase($this->menuRepo);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    #[Test]
    public function returns_all_menus_when_view_permission_granted(): void
    {
        $menus = [
            new MenuEntity('Main Menu', new Slug('main')),
            new MenuEntity('Footer Menu', new Slug('footer')),
        ];

        $this->menuRepo->shouldReceive('all')
            ->once()
            ->andReturn($menus);

        $result = $this->useCase->execute($this->mockUserPermission(view: true));
        $this->assertSame($menus, $result);
    }

    #[Test]
    public function throws_access_denied_when_view_permission_absent(): void
    {
        $this->menuRepo->shouldNotReceive('all');
        $this->expectException(AccessDeniedException::class);
        $this->useCase->execute($this->mockUserPermission()); // view: false
    }
}
