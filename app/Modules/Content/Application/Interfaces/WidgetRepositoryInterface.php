<?php

namespace App\Modules\Content\Application\Interfaces;

use App\Modules\Content\Domain\Entities\WidgetEntity;

interface WidgetRepositoryInterface
{
    public function save(WidgetEntity $widget): WidgetEntity;
    public function findById(int $id): ?WidgetEntity;
    public function delete(int $id): void;
    public function all(): array;
}
