<?php

namespace App\Modules\Content\Application\Actions\SettingSite\Footer;

use App\Modules\Content\Application\DTOs\SettingSite\Footer\FooterSettingsSaveData;
use App\Modules\Shared\Application\Interfaces\SettingRepositoryInterface;
use App\Modules\Shared\Domain\Entities\UserPermission;
use App\Modules\Shared\Infrastructure\Exceptions\AccessDeniedException;

final readonly class SaveFooterSettingsUseCase
{
    public function __construct(private SettingRepositoryInterface $settingRepository) {}

    public function execute(FooterSettingsSaveData $dto, UserPermission $permissions): void
    {
        if (!$permissions->can('content.data.edit')) {
            throw new AccessDeniedException();
        }
        $this->settingRepository->set('content', 'footer', $dto->toArray());
    }
}
