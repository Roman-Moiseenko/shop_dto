<?php

namespace App\Modules\Content\Application\Interfaces;

use App\Modules\Content\Domain\Entities\MenuEntity;
use App\Modules\Shared\Domain\ValueObjects\Slug;

interface MenuRepositoryInterface
{
    public function save(MenuEntity $menu): MenuEntity;
    public function findById(int $id): ?MenuEntity;
    public function findBySlug(Slug $slug): ?MenuEntity;
    public function delete(int $id): void;
    public function all(): array;
}
