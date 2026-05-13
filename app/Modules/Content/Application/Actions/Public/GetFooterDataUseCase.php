<?php

namespace App\Modules\Content\Application\Actions\Public;

use App\Modules\Content\Application\DTOs\Contact\ContactData;
use App\Modules\Content\Application\DTOs\Contact\ContactViewData;
use App\Modules\Content\Application\DTOs\Menu\MenuItemTreeData;
use App\Modules\Content\Application\DTOs\Public\ContactPublicData;
use App\Modules\Content\Application\DTOs\Public\FooterData;
use App\Modules\Content\Application\DTOs\Public\MenuFullData;
use App\Modules\Content\Application\Interfaces\ContactRepositoryInterface;
use App\Modules\Content\Application\Interfaces\MenuItemRepositoryInterface;
use App\Modules\Content\Application\Interfaces\MenuRepositoryInterface;
use App\Modules\Content\Domain\Entities\ContactEntity;
use App\Modules\Shared\Application\Interfaces\SettingRepositoryInterface;

final readonly class GetFooterDataUseCase
{
    public function __construct(
        private SettingRepositoryInterface     $settingRepository,
        private MenuRepositoryInterface        $menuRepository,
        private MenuItemRepositoryInterface    $menuItemRepository,
        private ContactRepositoryInterface     $contactRepository,
    ) {}

    public function execute(): FooterData
    {
        $raw = $this->settingRepository->get('content', 'footer', []);

        $menus = [];
        foreach ($raw['menuPositions'] ?? [] as $pos) {
            $menu = $this->menuRepository->findById($pos['menuId']);
            if (!$menu) continue;

            $tree = $this->menuItemRepository->getTree($menu->id);
            $items = array_map(fn($item) => MenuItemTreeData::fromEntity($item), $tree);

            $menus[] = new MenuFullData(
                id:    $menu->id,
                name:  $menu->name,
                slug:  (string) $menu->slug,
                items: $items,
            );
        }

        $contacts = $this->contactRepository->findAllActive();
        $contactData = array_map(fn(ContactEntity $c) => ContactPublicData::fromEntity($c), $contacts);

        return new FooterData(
            copyright:   $raw['copyright'] ?? '',
            description: $raw['description'] ?? null,
            menus:       $menus,
            contacts:    $contactData,
        );
    }
}
