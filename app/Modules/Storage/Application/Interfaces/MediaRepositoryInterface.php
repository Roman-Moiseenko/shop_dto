<?php

namespace App\Modules\Storage\Application\Interfaces;

use App\Modules\Storage\Domain\Entities\MediaEntity;

interface MediaRepositoryInterface
{
    public function save(MediaEntity $media): MediaEntity;
    public function findById(int $id): ?MediaEntity;
    public function findByUuid(string $uuid): ?MediaEntity;
    public function delete(int $id): void;
    public function listByEntity(string $modelType, int $modelId, ?string $type = null): array;
    public function findByEntityType(string $modelType, int $modelId, string $type): ?MediaEntity;
    public function listAll(?string $modelType = null, ?int $modelId = null): array;
}
