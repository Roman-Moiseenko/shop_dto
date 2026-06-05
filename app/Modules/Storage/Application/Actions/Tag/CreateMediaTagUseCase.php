<?php

namespace App\Modules\Storage\Application\Actions\Tag;

use App\Modules\Shared\Domain\Entities\UserPermission;
use App\Modules\Shared\Domain\Exceptions\AccessDeniedException;
use App\Modules\Shared\Domain\ValueObjects\Slug;
use App\Modules\Storage\Application\DTOs\Tag\MediaTagData;
use App\Modules\Storage\Application\Interfaces\MediaTagRepositoryInterface;
use App\Modules\Storage\Domain\Entities\MediaTagEntity;
use App\Modules\Storage\Domain\ValueObjects\TagName;

final readonly class CreateMediaTagUseCase
{
    public function __construct(
        private MediaTagRepositoryInterface $tagRepository
    ) {}

    public function execute(MediaTagData $dto, UserPermission $permissions): MediaTagEntity
    {
        if (!$permissions->can('storage.media.create')) {
            throw new AccessDeniedException();
        }

        $tag = new MediaTagEntity(
            new TagName($dto->name),
            new Slug($dto->slug)
        );

        return $this->tagRepository->save($tag);
    }
}
