<?php

namespace App\Modules\Storage\Application\Actions\Tag;

use App\Modules\Shared\Domain\Entities\UserPermission;
use App\Modules\Shared\Infrastructure\Exceptions\AccessDeniedException;
use App\Modules\Storage\Application\Interfaces\MediaTagRepositoryInterface;
use App\Modules\Storage\Domain\Entities\MediaTagEntity;
use InvalidArgumentException;

final readonly class ViewMediaTagUseCase
{
    public function __construct(
        private MediaTagRepositoryInterface $tagRepository
    ) {}

    public function execute(int $id, UserPermission $permissions): MediaTagEntity
    {
        if (!$permissions->can('storage.media.view')) {
            throw new AccessDeniedException();
        }

        $tag = $this->tagRepository->findById($id);
        if (!$tag) {
            throw new InvalidArgumentException('Тег не найден');
        }

        return $tag;
    }
}
