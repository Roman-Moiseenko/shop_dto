<?php

namespace App\Modules\Storage\Application\Interfaces;

use App\Modules\Shared\Domain\ValueObjects\Slug;
use App\Modules\Storage\Domain\Entities\MediaTagEntity;

interface MediaTagRepositoryInterface
{
    public function save(MediaTagEntity $tag): MediaTagEntity;
    public function findById(int $id): ?MediaTagEntity;
    public function findBySlug(Slug $slug): ?MediaTagEntity;
    public function delete(int $id): void;
    public function all(): array;
}
