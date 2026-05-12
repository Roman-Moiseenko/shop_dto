<?php

namespace App\Modules\Storage\Application\Actions\Tag;

use App\Modules\Shared\Domain\Entities\UserPermission;
use App\Modules\Shared\Infrastructure\Exceptions\AccessDeniedException;
use App\Modules\Storage\Application\Interfaces\MediaTagRepositoryInterface;

final readonly class IndexMediaTagsUseCase
{
    public function __construct(
        private MediaTagRepositoryInterface $tagRepository
    ) {}

    public function execute(UserPermission $permissions): array
    {
        if (!$permissions->can('storage.media.view')) {
            throw new AccessDeniedException();
        }

        return $this->tagRepository->all();
    }
}
