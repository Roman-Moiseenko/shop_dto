<?php

namespace App\Modules\Content\Application\Interfaces;

use App\Modules\Content\Domain\Entities\WidgetInstanceEntity;

interface WidgetInstanceRepositoryInterface
{
    public function save(WidgetInstanceEntity $instance): WidgetInstanceEntity;
    public function findById(int $id): ?WidgetInstanceEntity;
    public function findByUuid(string $uuid): ?WidgetInstanceEntity;
    public function delete(int $id): void;

    public function all(?int $widgetId);
}
