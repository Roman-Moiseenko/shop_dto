<?php

namespace App\Modules\Storage\Application\Actions\Tag;

use App\Modules\Shared\Domain\Entities\UserPermission;
use App\Modules\Shared\Domain\Exceptions\AccessDeniedException;
use App\Modules\Shared\Domain\ValueObjects\Slug;
use App\Modules\Storage\Application\DTOs\Tag\MediaTagData;
use App\Modules\Storage\Application\Interfaces\MediaTagRepositoryInterface;
use App\Modules\Storage\Domain\Entities\MediaTagEntity;
use App\Modules\Storage\Domain\ValueObjects\TagName;
use InvalidArgumentException;

final readonly class UpdateMediaTagUseCase
{
    public function __construct(
        private MediaTagRepositoryInterface $tagRepository
    ) {}

    public function execute(int $id, MediaTagData $dto, UserPermission $permissions): MediaTagEntity
    {
        if (!$permissions->can('storage.media.edit')) {
            throw new AccessDeniedException();
        }

        $tag = $this->tagRepository->findById($id);
        if (!$tag) {
            throw new InvalidArgumentException('Тег не найден');
        }

        $tag->name = new TagName($dto->name);
        $tag->slug = new Slug($dto->slug);

        return $this->tagRepository->save($tag);
    }
}
