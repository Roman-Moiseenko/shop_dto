<?php

namespace App\Modules\Storage\Application\Actions\Tag;
use App\Modules\Shared\Domain\Entities\UserPermission;
use App\Modules\Shared\Infrastructure\Exceptions\AccessDeniedException;
use App\Modules\Storage\Application\Interfaces\MediaTagRepositoryInterface;
use InvalidArgumentException;

final readonly class DeleteMediaTagUseCase
{
    public function __construct(
        private MediaTagRepositoryInterface $tagRepository
    ) {}

    public function execute(int $id, UserPermission $permissions): void
    {
        if (!$permissions->can('storage.media.delete')) {
            throw new AccessDeniedException();
        }

        $tag = $this->tagRepository->findById($id);
        if (!$tag) {
            throw new InvalidArgumentException('Тег не найден');
        }

        $this->tagRepository->delete($id);
    }
}
