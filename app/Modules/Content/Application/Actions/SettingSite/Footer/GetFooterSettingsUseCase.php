<?php

namespace App\Modules\Content\Application\Actions\SettingSite\Footer;

use App\Modules\Content\Application\DTOs\SettingSite\Footer\FooterSettingsData;
use App\Modules\Content\Application\Interfaces\MenuRepositoryInterface;
use App\Modules\Shared\Application\Interfaces\SettingRepositoryInterface;
use App\Modules\Shared\Domain\Entities\UserPermission;
use App\Modules\Shared\Infrastructure\Exceptions\AccessDeniedException;

final readonly class GetFooterSettingsUseCase
{
    public function __construct(
        private SettingRepositoryInterface $settingRepository,
        private MenuRepositoryInterface    $menuRepository,
    ) {}

    public function execute(UserPermission $permissions): FooterSettingsData
    {
        if (!$permissions->can('content.data.view')) {
            throw new AccessDeniedException();
        }

        $raw = $this->settingRepository->get('content', 'footer', []);

        $menuPositions = [];
        foreach ($raw['menuPositions'] ?? [] as $pos) {
            $menu = $this->menuRepository->findById($pos['menuId']);
            $menuPositions[] = [
                'position' => $pos['position'],
                'menuId'   => $pos['menuId'],
                'menuName' => $menu?->name ?? 'Неизвестное меню',
            ];
        }

        return new FooterSettingsData(
            copyright:     $raw['copyright'] ?? '',
            description:   $raw['description'] ?? null,
            menuPositions: $menuPositions,
        );
    }
}
