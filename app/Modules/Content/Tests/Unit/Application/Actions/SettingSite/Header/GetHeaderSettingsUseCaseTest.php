<?php

namespace App\Modules\Content\Tests\Unit\Application\Actions\SettingSite\Header;

use App\Modules\Content\Application\Actions\SettingSite\Header\GetHeaderSettingsUseCase;
use App\Modules\Content\Application\DTOs\SettingSite\Header\HeaderSettingsData;
use App\Modules\Content\Application\Interfaces\MenuRepositoryInterface;
use App\Modules\Content\Domain\Entities\MenuEntity;
use App\Modules\Shared\Application\Interfaces\SettingRepositoryInterface;
use App\Modules\Shared\Domain\ValueObjects\Slug;
use App\Modules\Shared\Infrastructure\Exceptions\AccessDeniedException;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Mockery;
use Tests\Trait\MockPermission;

class GetHeaderSettingsUseCaseTest extends TestCase
{
    use MockPermission;

    private SettingRepositoryInterface $settingRepo;
    private MenuRepositoryInterface $menuRepo;
    private GetHeaderSettingsUseCase $useCase;

    public function getModuleName(): string { return 'content'; }
    public function getEntityName(): string { return 'data'; }

    protected function setUp(): void
    {
        parent::setUp();
        $this->settingRepo = Mockery::mock(SettingRepositoryInterface::class);
        $this->menuRepo    = Mockery::mock(MenuRepositoryInterface::class);
        $this->useCase = new GetHeaderSettingsUseCase(
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
    public function returns_header_settings_with_menu_names(): void
    {
        $raw = [
            'siteName'         => 'My Shop',
            'slogan'           => 'Best Shop Ever',
            'logoUuid'         => 'uuid-logo-123',
            'searchEnabled'    => true,
            'searchPlaceholder'=> 'Find something...',
            'searchActionUrl'  => '/search',
            'menuPositions'    => [
                ['position' => 'main', 'menuId' => 1],
                ['position' => 'secondary', 'menuId' => 2],
            ],
        ];

        $this->settingRepo->shouldReceive('get')
            ->with('content', 'header', [])
            ->once()
            ->andReturn($raw);

        $menu1 = new MenuEntity('Main Menu', new Slug('main'));
        $menu1->id = 1;
        $menu2 = new MenuEntity('Secondary Menu', new Slug('secondary'));
        $menu2->id = 2;

        $this->menuRepo->shouldReceive('findById')->with(1)->andReturn($menu1);
        $this->menuRepo->shouldReceive('findById')->with(2)->andReturn($menu2);

        $result = $this->useCase->execute($this->mockUserPermission(view: true));

        $this->assertInstanceOf(HeaderSettingsData::class, $result);
        $this->assertSame('My Shop', $result->siteName);
        $this->assertSame('Best Shop Ever', $result->slogan);
        $this->assertSame('uuid-logo-123', $result->logoUuid);
        $this->assertTrue($result->searchEnabled);
        $this->assertSame('Find something...', $result->searchPlaceholder);
        $this->assertSame('/search', $result->searchActionUrl);

        $this->assertCount(2, $result->menuPositions);
        $this->assertSame('main', $result->menuPositions[0]['position']);
        $this->assertSame(1, $result->menuPositions[0]['menuId']);
        $this->assertSame('Main Menu', $result->menuPositions[0]['menuName']);
        $this->assertSame('secondary', $result->menuPositions[1]['position']);
        $this->assertSame(2, $result->menuPositions[1]['menuId']);
        $this->assertSame('Secondary Menu', $result->menuPositions[1]['menuName']);
    }

    #[Test]
    public function returns_defaults_when_no_settings(): void
    {
        $this->settingRepo->shouldReceive('get')
            ->once()
            ->andReturn([]);

        $result = $this->useCase->execute($this->mockUserPermission(view: true));

        $this->assertSame('', $result->siteName);
        $this->assertNull($result->slogan);
        $this->assertNull($result->logoUuid);
        $this->assertFalse($result->searchEnabled);
        $this->assertNull($result->searchPlaceholder);
        $this->assertNull($result->searchActionUrl);
        $this->assertEmpty($result->menuPositions);
    }

    #[Test]
    public function handles_missing_menu_gracefully(): void
    {
        $raw = [
            'siteName' => 'Test',
            'menuPositions' => [
                ['position' => 'broken', 'menuId' => 99],
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
