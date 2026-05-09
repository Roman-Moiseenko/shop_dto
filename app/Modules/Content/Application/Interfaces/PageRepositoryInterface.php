<?php

namespace App\Modules\Content\Application\Interfaces;

use App\Modules\Content\Domain\Entities\PageEntity;
use App\Modules\Shared\Domain\ValueObjects\Slug;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface PageRepositoryInterface
{
    public function save(PageEntity $page): PageEntity;
    public function findById(int $id): ?PageEntity;
    public function findBySlug(Slug $slug): ?PageEntity;
    public function slugExists(Slug $slug, ?int $excludeId = null): bool;
    public function delete(int $id): void;
    public function paginate(int $perPage = 15, array $filters = []): LengthAwarePaginator;
}
