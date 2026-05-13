<?php

namespace App\Modules\Content\Application\Actions\SettingSite\Header;

use App\Modules\Content\Application\DTOs\SettingSite\Header\HeaderSettingsSaveData;
use App\Modules\Shared\Application\Interfaces\SettingRepositoryInterface;
use App\Modules\Shared\Domain\Entities\UserPermission;
use App\Modules\Shared\Infrastructure\Exceptions\AccessDeniedException;

final readonly class SaveHeaderSettingsUseCase
{
    public function __construct(private SettingRepositoryInterface $settingRepository) {}
    public function execute(HeaderSettingsSaveData $dto, UserPermission $permissions): void
    {
        if (!$permissions->can('content.data.edit')) {
            throw new AccessDeniedException();
        }
        $this->settingRepository->set('content', 'header', $dto->toArray());
    }
}
