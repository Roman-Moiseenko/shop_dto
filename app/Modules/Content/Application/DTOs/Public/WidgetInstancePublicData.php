<?php

namespace App\Modules\Content\Application\DTOs\Public;
use App\Modules\Content\Domain\Entities\WidgetInstanceEntity;
use Spatie\LaravelData\Data;
class WidgetInstancePublicData extends Data
{
    public function __construct(
        public readonly string $uuid,
        public readonly string $widgetSlug,      // для выбора компонента на фронте
        public readonly array $params,           // готовые параметры
    ) {}

    public static function fromEntity(WidgetInstanceEntity $entity): self
    {
        return new self(
            uuid: $entity->uuid,
            widgetSlug: $entity->widgetSlug,     // должен быть заполнен репозиторием
            params: $entity->params,
        );
    }
}
