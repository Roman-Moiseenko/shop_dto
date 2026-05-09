<?php

namespace App\Modules\Content\Application\Actions\ContentBlocks;

use App\Modules\Content\Application\Interfaces\ContentBlockRepositoryInterface;
use App\Modules\Content\Infrastructure\Exceptions\ContentBlockNotFoundException;
use App\Modules\Shared\Domain\Entities\UserPermission;
use App\Modules\Shared\Infrastructure\Exceptions\AccessDeniedException;

readonly class RemoveBlockFromPageUseCase
{
    public function __construct(
        private ContentBlockRepositoryInterface $blockRepository
    ) {}

    public function execute(int $pageId, int $blockId, UserPermission $permissions): void
    {
        if (!$permissions->can('content.data.delete')) {
            throw new AccessDeniedException();
        }

        // Проверяем, что блок существует и принадлежит странице (опционально, можно проверить через контейнер в будущем)
        $block = $this->blockRepository->findById($blockId);
        if (!$block || $block->containerId !== $pageId || $block->containerType->getValue() !== 'page') {
            throw new ContentBlockNotFoundException('Блок не найден');
        }

        $this->blockRepository->delete($blockId);
    }
}
