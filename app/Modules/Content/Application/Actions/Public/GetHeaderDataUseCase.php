<?php

namespace App\Modules\Content\Application\Actions\Public;

use App\Modules\Content\Application\DTOs\Contact\ContactData;
use App\Modules\Content\Application\DTOs\Contact\ContactViewData;
use App\Modules\Content\Application\DTOs\Menu\MenuItemTreeData;
use App\Modules\Content\Application\DTOs\Public\ContactPublicData;
use App\Modules\Content\Application\DTOs\Public\HeaderData;
use App\Modules\Content\Application\DTOs\Public\MenuFullData;
use App\Modules\Content\Application\DTOs\Public\SearchData;
use App\Modules\Content\Application\Interfaces\ContactRepositoryInterface;
use App\Modules\Content\Application\Interfaces\MenuItemRepositoryInterface;
use App\Modules\Content\Application\Interfaces\MenuRepositoryInterface;
use App\Modules\Content\Domain\Entities\ContactEntity;
use App\Modules\Shared\Application\Interfaces\SettingRepositoryInterface;

final readonly class GetHeaderDataUseCase
{
    public function __construct(
        private SettingRepositoryInterface     $settingRepository,
        private MenuRepositoryInterface        $menuRepository,
        private MenuItemRepositoryInterface    $menuItemRepository,
        private ContactRepositoryInterface     $contactRepository,
    ) {}

    public function execute(): HeaderData
    {
        // Получаем настройки хедера из общего хранилища (модуль 'content', ключ 'header')
        $raw = $this->settingRepository->get('content', 'header', []);

        // Собираем меню с полными деревьями пунктов
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



        // Поиск
        $search = new SearchData(
            enabled:     $raw['searchEnabled'] ?? false,
            placeholder: $raw['searchPlaceholder'] ?? '',
            actionUrl:   $raw['searchActionUrl'] ?? '',
        );
        // Активные контакты
        $contacts = $this->contactRepository->findAllActive();
        $contactData = array_map(fn(ContactEntity $c) => ContactPublicData::fromEntity($c), $contacts);

        return new HeaderData(
            siteName: $raw['siteName'] ?? '',
            slogan:   $raw['slogan'] ?? null,
            logoUuid: $raw['logoUuid'] ?? null,
            menus:    $menus,
            contacts: $contactData,
            search:   $search,
        );
    }
}
