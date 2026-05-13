<?php

namespace App\Modules\Content\Application\Actions\SettingSite\Header;

use App\Modules\Content\Application\Actions\Header\HeaderSettingsData;
use App\Modules\Content\Application\Interfaces\MenuRepositoryInterface;
use App\Modules\Shared\Application\Interfaces\SettingRepositoryInterface;
use App\Modules\Shared\Domain\Entities\UserPermission;
use App\Modules\Shared\Infrastructure\Exceptions\AccessDeniedException;

final readonly class GetHeaderSettingsUseCase
{
    public function __construct(
        private SettingRepositoryInterface $settingRepository,
        private MenuRepositoryInterface    $menuRepository,
    ) {}

    public function execute(UserPermission $permissions): HeaderSettingsData
    {
        if (!$permissions->can('content.data.view')) {
            throw new AccessDeniedException();
        }

        // Получаем сохранённые настройки из общего хранилища
        $raw = $this->settingRepository->get('content', 'header', []);

        // Добавляем имена меню для каждой позиции
        $menuPositions = [];
        foreach ($raw['menuPositions'] ?? [] as $pos) {
            $menu = $this->menuRepository->findById($pos['menuId']);
            $menuPositions[] = [
                'position' => $pos['position'],
                'menuId'   => $pos['menuId'],
                'menuName' => $menu?->name ?? 'Неизвестное меню',
            ];
        }

        return new HeaderSettingsData (
            siteName:          $raw['siteName'] ?? '',
            slogan:            $raw['slogan'] ?? null,
            logoUuid:          $raw['logoUuid'] ?? null,
            menuPositions:     $menuPositions,
            searchEnabled:     $raw['searchEnabled'] ?? false,
            searchPlaceholder: $raw['searchPlaceholder'] ?? null,
            searchActionUrl:   $raw['searchActionUrl'] ?? null,
        );
    }
}
