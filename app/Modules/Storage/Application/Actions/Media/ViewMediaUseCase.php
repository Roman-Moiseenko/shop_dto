<?php

namespace App\Modules\Storage\Application\Actions\Media;

use App\Modules\Storage\Application\Interfaces\MediaRepositoryInterface;
use App\Modules\Storage\Domain\Entities\MediaEntity;
use App\Modules\Storage\Domain\Exceptions\MediaFileNotFoundException;

readonly class ViewMediaUseCase
{
    public function __construct(
        private MediaRepositoryInterface $mediaRepository,
    ) {}
    public function execute(string $uuid): MediaEntity
    {
        $media = $this->mediaRepository->findByUuid($uuid);
        if (!$media) throw new MediaFileNotFoundException('Медиа не найдено');

        return $media;
    }
}
