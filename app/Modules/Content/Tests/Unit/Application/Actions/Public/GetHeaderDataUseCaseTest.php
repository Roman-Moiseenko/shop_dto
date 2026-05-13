<?php

namespace App\Modules\Content\Tests\Unit\Application\Actions\Public;

use App\Modules\Content\Application\Actions\Public\GetHeaderDataUseCase;
use App\Modules\Content\Application\DTOs\Contact\ContactViewData;
use App\Modules\Content\Application\DTOs\Public\HeaderData;
use App\Modules\Content\Application\DTOs\Public\MenuFullData;
use App\Modules\Content\Application\DTOs\Public\SearchData;
use App\Modules\Content\Application\Interfaces\ContactRepositoryInterface;
use App\Modules\Content\Application\Interfaces\MenuItemRepositoryInterface;
use App\Modules\Content\Application\Interfaces\MenuRepositoryInterface;
use App\Modules\Content\Domain\Entities\ContactEntity;
use App\Modules\Content\Domain\Entities\MenuEntity;
use App\Modules\Shared\Application\Interfaces\SettingRepositoryInterface;
use App\Modules\Shared\Domain\ValueObjects\Slug;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Mockery;
class GetHeaderDataUseCaseTest extends TestCase
{
    private SettingRepositoryInterface $settingRepo;
    private MenuRepositoryInterface $menuRepo;
    private MenuItemRepositoryInterface $menuItemRepo;
    private ContactRepositoryInterface $contactRepo;
    private GetHeaderDataUseCase $useCase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->settingRepo  = Mockery::mock(SettingRepositoryInterface::class);
        $this->menuRepo     = Mockery::mock(MenuRepositoryInterface::class);
        $this->menuItemRepo = Mockery::mock(MenuItemRepositoryInterface::class);
        $this->contactRepo  = Mockery::mock(ContactRepositoryInterface::class);
        $this->useCase = new GetHeaderDataUseCase(
            $this->settingRepo,
            $this->menuRepo,
            $this->menuItemRepo,
            $this->contactRepo,
        );
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    #[Test]
    public function returns_header_data_with_all_fields(): void
    {
        // Настройки
        $rawSettings = [
            'siteName'         => 'Мой магазин',
            'slogan'           => 'Лучшие товары',
            'logoUuid'         => 'uuid-logo',
            'searchEnabled'    => true,
            'searchPlaceholder' => 'Поиск...',
            'searchActionUrl'  => '/search',
            'menuPositions'    => [
                ['position' => 'main', 'menuId' => 1],
            ],
        ];
        $this->settingRepo->shouldReceive('get')
            ->with('content', 'header', [])
            ->once()
            ->andReturn($rawSettings);

        // Меню
        $menu = new MenuEntity('Главное меню', new Slug('main-menu'));
        $menu->id = 1;
        $this->menuRepo->shouldReceive('findById')->with(1)->andReturn($menu);
        $this->menuItemRepo->shouldReceive('getTree')->with(1)->andReturn([]);

        // Контакты
        $contact = new ContactEntity('phone', '+79991234567', sort: 0, isActive: true);
        $contact->id = 10;
        $this->contactRepo->shouldReceive('findAllActive')->once()->andReturn([$contact]);

        $result = $this->useCase->execute();

        $this->assertInstanceOf(HeaderData::class, $result);
        $this->assertSame('Мой магазин', $result->siteName);
        $this->assertSame('Лучшие товары', $result->slogan);
        $this->assertSame('uuid-logo', $result->logoUuid);
        $this->assertCount(1, $result->menus);
        $this->assertInstanceOf(MenuFullData::class, $result->menus[0]);
        $this->assertSame(1, $result->menus[0]->id);
        $this->assertSame('Главное меню', $result->menus[0]->name);
        $this->assertCount(1, $result->contacts);
        $this->assertInstanceOf(ContactViewData::class, $result->contacts[0]);
        $this->assertSame('+79991234567', $result->contacts[0]->value);
        $this->assertInstanceOf(SearchData::class, $result->search);
        $this->assertTrue($result->search->enabled);
        $this->assertSame('/search', $result->search->actionUrl);
    }

    #[Test]
    public function returns_defaults_when_no_settings(): void
    {
        $this->settingRepo->shouldReceive('get')
            ->once()
            ->andReturn([]);
        $this->contactRepo->shouldReceive('findAllActive')->once()->andReturn([]);

        $result = $this->useCase->execute();

        $this->assertSame('', $result->siteName);
        $this->assertNull($result->slogan);
        $this->assertNull($result->logoUuid);
        $this->assertEmpty($result->menus);
        $this->assertEmpty($result->contacts);
        $this->assertFalse($result->search->enabled);
        $this->assertSame('', $result->search->placeholder);
        $this->assertSame('', $result->search->actionUrl);
    }

    #[Test]
    public function skips_menus_that_are_not_found(): void
    {
        $rawSettings = [
            'siteName' => 'Test',
            'menuPositions' => [
                ['position' => 'x', 'menuId' => 99],
            ],
        ];
        $this->settingRepo->shouldReceive('get')->once()->andReturn($rawSettings);
        $this->menuRepo->shouldReceive('findById')->with(99)->once()->andReturn(null);
        $this->contactRepo->shouldReceive('findAllActive')->once()->andReturn([]);

        $result = $this->useCase->execute();

        $this->assertEmpty($result->menus);
    }
}
