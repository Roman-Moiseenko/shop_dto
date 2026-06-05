<?php

namespace App\Modules\Content\Application\Actions\ContentBlocks;

use App\Modules\Content\Application\DTOs\ContentBlocks\UpdateBlockCaptionData;
use App\Modules\Content\Application\Interfaces\ContentBlockRepositoryInterface;
use App\Modules\Content\Domain\Entities\ContentBlockEntity;
use App\Modules\Content\Domain\Exceptions\ContentBlockNotFoundException;
use App\Modules\Shared\Domain\Entities\UserPermission;
use App\Modules\Shared\Domain\Exceptions\AccessDeniedException;

readonly class UpdateBlockCaptionUseCase
{
    public function __construct(
        private ContentBlockRepositoryInterface $blockRepository
    ) {}

    public function execute(int $pageId, UpdateBlockCaptionData $dto, UserPermission $permissions): ContentBlockEntity
    {
        if (!$permissions->can('content.data.edit')) {
            throw new AccessDeniedException();
        }

        $block = $this->blockRepository->findById($dto->id);
        if (!$block || $block->containerId !== $pageId || $block->containerType->getValue() !== 'page') {
            throw new ContentBlockNotFoundException('Блок не найден');
        }

        $block->caption = $dto->caption;
        return $this->blockRepository->save($block);
    }
}
