<?php

namespace App\Modules\Content\Application\Actions\ContentBlocks;

use App\Modules\Content\Application\DTOs\ReorderSingleBlockData;
use App\Modules\Content\Application\Interfaces\ContentBlockRepositoryInterface;
use App\Modules\Content\Domain\ValueObjects\ContainerType;
use App\Modules\Content\Infrastructure\Exceptions\ContentBlockNotFoundException;
use App\Modules\Shared\Domain\Entities\UserPermission;
use App\Modules\Shared\Infrastructure\Exceptions\AccessDeniedException;

readonly class ReorderSingleBlockUseCase
{
    public function __construct(
        private ContentBlockRepositoryInterface $blockRepository
    ) {}

    public function execute(int $pageId, ReorderSingleBlockData $dto, UserPermission $permissions): void
    {
        if (!$permissions->can('content.data.edit')) {
            throw new AccessDeniedException();
        }

        $block = $this->blockRepository->findById($dto->id);
        if (!$block || $block->containerId !== $pageId || (string)$block->containerType !== 'page') {
            throw new ContentBlockNotFoundException('Блок не найден');
        }

        $this->blockRepository->updateSortOrder(
            $dto->id,
            $dto->sort,
            ContainerType::page(),
            $pageId
        );
    }
}
