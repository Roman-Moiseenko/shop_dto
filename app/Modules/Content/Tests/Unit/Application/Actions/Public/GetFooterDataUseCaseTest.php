<?php

namespace App\Modules\Content\Tests\Unit\Application\Actions\Public;

use App\Modules\Content\Application\Actions\Public\GetFooterDataUseCase;
use App\Modules\Content\Application\DTOs\Contact\ContactData;
use App\Modules\Content\Application\DTOs\Contact\ContactViewData;
use App\Modules\Content\Application\DTOs\Public\ContactPublicData;
use App\Modules\Content\Application\DTOs\Public\FooterData;
use App\Modules\Content\Application\DTOs\Public\MenuFullData;
use App\Modules\Content\Application\Interfaces\ContactRepositoryInterface;
use App\Modules\Content\Application\Interfaces\MenuItemRepositoryInterface;
use App\Modules\Content\Application\Interfaces\MenuRepositoryInterface;
use App\Modules\Content\Domain\Entities\ContactEntity;
use App\Modules\Content\Domain\Entities\MenuEntity;
use App\Modules\Content\Domain\ValueObjects\ContactType;
use App\Modules\Shared\Application\Interfaces\SettingRepositoryInterface;
use App\Modules\Shared\Domain\ValueObjects\Slug;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Mockery;

class GetFooterDataUseCaseTest extends TestCase
{
    private SettingRepositoryInterface $settingRepo;
    private MenuRepositoryInterface $menuRepo;
    private MenuItemRepositoryInterface $menuItemRepo;
    private ContactRepositoryInterface $contactRepo;
    private GetFooterDataUseCase $useCase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->settingRepo   = Mockery::mock(SettingRepositoryInterface::class);
        $this->menuRepo      = Mockery::mock(MenuRepositoryInterface::class);
        $this->menuItemRepo  = Mockery::mock(MenuItemRepositoryInterface::class);
        $this->contactRepo   = Mockery::mock(ContactRepositoryInterface::class);
        $this->useCase = new GetFooterDataUseCase(
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
    public function returns_footer_data_with_menus_and_contacts(): void
    {
        // Настройки
        $rawSettings = [
            'copyright'     => '© 2026',
            'description'   => 'Footer description',
            'menuPositions' => [
                ['position' => 'footer_main', 'menuId' => 1],
                ['position' => 'footer_secondary', 'menuId' => 2],
            ],
        ];
        $this->settingRepo->shouldReceive('get')
            ->with('content', 'footer', [])
            ->once()
            ->andReturn($rawSettings);

        // Меню
        $menu1 = new MenuEntity('Main Footer', new Slug('main-footer'));
        $menu1->id = 1;
        $menu2 = new MenuEntity('Secondary Footer', new Slug('secondary-footer'));
        $menu2->id = 2;
        $this->menuRepo->shouldReceive('findById')->with(1)->andReturn($menu1);
        $this->menuRepo->shouldReceive('findById')->with(2)->andReturn($menu2);

        // Дерево пунктов меню (пустое для простоты)
        $this->menuItemRepo->shouldReceive('getTree')->with(1)->andReturn([]);
        $this->menuItemRepo->shouldReceive('getTree')->with(2)->andReturn([]);

        // Контакты
        $contact1 = new ContactEntity(new ContactType('phone'), '+123456789', sort: 0, isActive: true);
        $contact1->id = 10;
        $contact2 = new ContactEntity(new ContactType('email'), 'test@test.com', sort: 1, isActive: true);
        $contact2->id = 11;
        $this->contactRepo->shouldReceive('findAllActive')->once()->andReturn([$contact1, $contact2]);

        $result = $this->useCase->execute();

        $this->assertInstanceOf(FooterData::class, $result);
        $this->assertSame('© 2026', $result->copyright);
        $this->assertSame('Footer description', $result->description);
        $this->assertCount(2, $result->menus);
        $this->assertInstanceOf(MenuFullData::class, $result->menus[0]);
        $this->assertSame(1, $result->menus[0]->id);
        $this->assertSame('Main Footer', $result->menus[0]->name);
        $this->assertCount(2, $result->contacts);
        $this->assertInstanceOf(ContactPublicData::class, $result->contacts[0]);
        $this->assertSame('+123456789', $result->contacts[0]->value);
    }

    #[Test]
    public function returns_defaults_when_no_settings(): void
    {
        $this->settingRepo->shouldReceive('get')
            ->once()
            ->andReturn([]);

        $this->contactRepo->shouldReceive('findAllActive')->once()->andReturn([]);

        $result = $this->useCase->execute();

        $this->assertSame('', $result->copyright);
        $this->assertNull($result->description);
        $this->assertEmpty($result->menus);
        $this->assertEmpty($result->contacts);
    }

    #[Test]
    public function skips_menus_that_are_not_found(): void
    {
        $rawSettings = [
            'copyright' => '',
            'menuPositions' => [
                ['position' => 'x', 'menuId' => 99], // несуществующее меню
            ],
        ];
        $this->settingRepo->shouldReceive('get')->once()->andReturn($rawSettings);
        $this->menuRepo->shouldReceive('findById')->with(99)->once()->andReturn(null);
        $this->contactRepo->shouldReceive('findAllActive')->once()->andReturn([]);

        $result = $this->useCase->execute();

        $this->assertEmpty($result->menus);
    }
}
