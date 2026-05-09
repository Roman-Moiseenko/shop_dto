<?php

namespace App\Modules\Content\Application\Interfaces;

use App\Modules\Content\Domain\Entities\PageEntity;
use App\Modules\Shared\Domain\ValueObjects\Slug;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface PageRepositoryInterface
{
    public function save(PageEntity $page): PageEntity;
    public function findById(int $id, bool $withTrashed = true): ?PageEntity;
    public function findBySlug(Slug $slug, bool $withTrashed = true): ?PageEntity;
    public function slugExists(Slug $slug, ?int $excludeId = null, bool $withTrashed = true): bool;
    public function delete(int $id): void;
    public function paginate(int $perPage = 15, array $filters = [], bool $withTrashed = true): LengthAwarePaginator;
    public function forceDelete(int $id): void;
    public function restore(int $id): void;
}
