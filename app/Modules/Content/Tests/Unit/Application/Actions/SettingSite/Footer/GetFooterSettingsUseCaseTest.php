<?php

namespace App\Modules\Content\Tests\Unit\Application\Actions\SettingSite\Footer;

use App\Modules\Content\Application\Actions\SettingSite\Footer\GetFooterSettingsUseCase;
use App\Modules\Content\Application\DTOs\SettingSite\Footer\FooterSettingsData;
use App\Modules\Content\Application\Interfaces\MenuRepositoryInterface;
use App\Modules\Content\Domain\Entities\MenuEntity;
use App\Modules\Shared\Application\Interfaces\SettingRepositoryInterface;
use App\Modules\Shared\Domain\ValueObjects\Slug;
use App\Modules\Shared\Infrastructure\Exceptions\AccessDeniedException;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Mockery;
use Tests\Trait\MockPermission;

class GetFooterSettingsUseCaseTest extends TestCase
{
    use MockPermission;

    private SettingRepositoryInterface $settingRepo;
    private MenuRepositoryInterface $menuRepo;
    private GetFooterSettingsUseCase $useCase;

    public function getModuleName(): string { return 'content'; }
    public function getEntityName(): string { return 'data'; }

    protected function setUp(): void
    {
        parent::setUp();
        $this->settingRepo = Mockery::mock(SettingRepositoryInterface::class);
        $this->menuRepo    = Mockery::mock(MenuRepositoryInterface::class);
        $this->useCase = new GetFooterSettingsUseCase(
            $this->settingRepo,
            $this->menuRepo,
        );
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    #[Test]
    public function returns_footer_settings_with_menu_names(): void
    {
        $raw = [
            'copyright'   => '© 2026',
            'description' => 'Footer description',
            'menuPositions' => [
                ['position' => 'footer_main', 'menuId' => 1],
                ['position' => 'footer_secondary', 'menuId' => 2],
            ],
        ];

        $this->settingRepo->shouldReceive('get')
            ->with('content', 'footer', [])
            ->once()
            ->andReturn($raw);

        $menu1 = new MenuEntity('Main Footer', new Slug('main-footer'));
        $menu1->id = 1;
        $menu2 = new MenuEntity('Secondary Footer', new Slug('secondary-footer'));
        $menu2->id = 2;

        $this->menuRepo->shouldReceive('findById')->with(1)->andReturn($menu1);
        $this->menuRepo->shouldReceive('findById')->with(2)->andReturn($menu2);

        $result = $this->useCase->execute($this->mockUserPermission(view: true));

        $this->assertInstanceOf(FooterSettingsData::class, $result);
        $this->assertSame('© 2026', $result->copyright);
        $this->assertSame('Footer description', $result->description);
        $this->assertCount(2, $result->menuPositions);

        $this->assertSame('footer_main', $result->menuPositions[0]['position']);
        $this->assertSame(1, $result->menuPositions[0]['menuId']);
        $this->assertSame('Main Footer', $result->menuPositions[0]['menuName']);

        $this->assertSame('footer_secondary', $result->menuPositions[1]['position']);
        $this->assertSame(2, $result->menuPositions[1]['menuId']);
        $this->assertSame('Secondary Footer', $result->menuPositions[1]['menuName']);
    }

    #[Test]
    public function returns_defaults_when_no_settings(): void
    {
        $this->settingRepo->shouldReceive('get')
            ->once()
            ->andReturn([]);

        $result = $this->useCase->execute($this->mockUserPermission(view: true));

        $this->assertSame('', $result->copyright);
        $this->assertNull($result->description);
        $this->assertEmpty($result->menuPositions);
    }

    #[Test]
    public function handles_missing_menu_gracefully(): void
    {
        $raw = [
            'copyright' => '',
            'menuPositions' => [
                ['position' => 'x', 'menuId' => 99],
            ],
        ];
        $this->settingRepo->shouldReceive('get')->once()->andReturn($raw);
        $this->menuRepo->shouldReceive('findById')->with(99)->once()->andReturn(null);

        $result = $this->useCase->execute($this->mockUserPermission(view: true));

        $this->assertCount(1, $result->menuPositions);
        $this->assertSame('Неизвестное меню', $result->menuPositions[0]['menuName']);
    }

    #[Test]
    public function throws_access_denied_when_view_permission_absent(): void
    {
        $this->settingRepo->shouldNotReceive('get');

        $this->expectException(AccessDeniedException::class);
        $this->useCase->execute($this->mockUserPermission()); // view: false
    }
}
