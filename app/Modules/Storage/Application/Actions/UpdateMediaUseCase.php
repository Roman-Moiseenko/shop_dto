<?php

namespace App\Modules\Storage\Application\Actions;

use App\Modules\Shared\Domain\Entities\UserPermission;
use App\Modules\Shared\Infrastructure\Exceptions\AccessDeniedException;
use App\Modules\Storage\Application\DTOs\UpdateMediaData;
use App\Modules\Storage\Application\Interfaces\MediaRepositoryInterface;
use App\Modules\Storage\Application\Services\ImageProcessor;
use App\Modules\Storage\Domain\Entities\MediaEntity;

class UpdateMediaUseCase
{
    public function __construct(
        private readonly MediaRepositoryInterface $mediaRepository,
    ) {}
    public function execute(int $id, UpdateMediaData $dto, UserPermission $permissions): MediaEntity
    {
        if (!$permissions->can('storage.media.edit')) throw new AccessDeniedException();

        $media = $this->mediaRepository->findById($id);
        if (!$media) throw new \InvalidArgumentException('Медиа не найдено');

        if ($dto->title !== null) $media->title = $dto->title;
        if ($dto->description !== null) $media->description = $dto->description;
        if ($dto->sort !== null) $media->sort = $dto->sort;

        return $this->mediaRepository->save($media);
    }
}
