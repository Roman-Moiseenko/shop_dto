<?php

namespace App\Modules\Storage\Application\Interfaces;

use App\Modules\Shared\Domain\ValueObjects\Slug;
use App\Modules\Storage\Domain\Entities\GalleryEntity;

interface GalleryRepositoryInterface
{
    public function save(GalleryEntity $gallery): GalleryEntity;
    public function findById(int $id): ?GalleryEntity;
    public function findBySlug(Slug $slug): ?GalleryEntity;
    public function delete(int $id): void;
    public function all(): array;
}
