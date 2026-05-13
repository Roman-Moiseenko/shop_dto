<?php

namespace App\Modules\Content\Application\Interfaces;

use App\Modules\Content\Domain\Entities\MenuItemEntity;

interface MenuItemRepositoryInterface
{
    public function save(MenuItemEntity $item): MenuItemEntity;
    public function findById(int $id): ?MenuItemEntity;
    public function delete(int $id): void;
    public function listByMenu(int $menuId): array;
    public function changeParent(int $itemId, ?int $newParentId): void;
    public function updateSortOrder(int $itemId, int $newSort): void;
    public function getTree(int $menuId): array;
}
