<?php

namespace App\Modules\Storage\Application\Actions\Media;

use App\Modules\Shared\Domain\Entities\UserPermission;
use App\Modules\Shared\Domain\Exceptions\AccessDeniedException;
use App\Modules\Storage\Application\DTOs\Media\UpdateMediaData;
use App\Modules\Storage\Application\Interfaces\MediaRepositoryInterface;
use App\Modules\Storage\Domain\Entities\MediaEntity;

readonly class UpdateMediaUseCase
{
    public function __construct(
        private MediaRepositoryInterface $mediaRepository,
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
