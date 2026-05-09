<?php

namespace App\Modules\Content\Application\Interfaces;

use App\Modules\Content\Domain\Entities\ContentBlockEntity;
use App\Modules\Content\Domain\ValueObjects\ContainerType;

interface ContentBlockRepositoryInterface
{
    public function save(ContentBlockEntity $block): ContentBlockEntity;
    public function findById(int $id): ?ContentBlockEntity;
    public function delete(int $id): void;
    public function listByContainer(ContainerType $containerType, int $containerId): array;
    public function reorder(ContainerType $containerType, int $containerId, array $orderedIds): void;

    public function updateSortOrder(int $blockId, int $newSortOrder, ContainerType $containerType, int $containerId): void;
}
