<?php

namespace App\Modules\Content\Application\Actions\ContentBlocks;

use App\Modules\Content\Application\DTOs\ContentBlocks\AddContentBlockData;
use App\Modules\Content\Application\Interfaces\ContentBlockRepositoryInterface;
use App\Modules\Content\Domain\Entities\ContentBlockEntity;
use App\Modules\Content\Domain\ValueObjects\ContainerType;
use App\Modules\Shared\Domain\Entities\UserPermission;
use App\Modules\Shared\Domain\Exceptions\AccessDeniedException;

class AddBlockToPageUseCase
{
    public function __construct(private ContentBlockRepositoryInterface $blockRepository) {}

    public function execute(int $pageId, AddContentBlockData $dto, UserPermission $permissions): ContentBlockEntity
    {
        if (!$permissions->can('content.data.create')) {
            throw new AccessDeniedException();
        }

        $block = new ContentBlockEntity(
            ContainerType::page(),
            $pageId,
            $dto->instanceId,
            $dto->sort,
            $dto->section,
            $dto->caption
        );

        return $this->blockRepository->save($block);
    }
}
