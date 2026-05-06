<?php

namespace App\Modules\Storage\Application\Actions;

use App\Modules\Shared\Domain\Entities\UserPermission;
use App\Modules\Shared\Infrastructure\Exceptions\AccessDeniedException;
use App\Modules\Storage\Application\DTOs\IndexMediaData;
use App\Modules\Storage\Application\Interfaces\MediaRepositoryInterface;

readonly class IndexMediaUseCase
{
    public function __construct(
        private MediaRepositoryInterface $mediaRepository,
    ) {}
    public function execute(IndexMediaData $dto, UserPermission $permissions): array
    {
        if (!$permissions->can('storage.media.view')) throw new AccessDeniedException();

        return $this->mediaRepository->listByEntity($dto->model_type, $dto->model_id, $dto->type);
    }
}
