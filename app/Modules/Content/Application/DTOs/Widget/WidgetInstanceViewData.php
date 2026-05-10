<?php

namespace App\Modules\Content\Application\DTOs\Widget;

use App\Modules\Content\Domain\Entities\WidgetInstanceEntity;
use Spatie\LaravelData\Attributes\Validation\IntegerType;
use Spatie\LaravelData\Attributes\Validation\Required;
use Spatie\LaravelData\Data;

class WidgetInstanceViewData extends Data
{
    public function __construct(
        #[Required, IntegerType]
        public readonly int $id,
        public readonly int $widgetId,
        public readonly string $widgetName,
        public readonly string $widgetSlug,
        public readonly array $params = [],
        public readonly ?string $title = null,
        public readonly ?string $uuid = null,
        public readonly ?string $createdAt = null,
        public readonly ?string $updatedAt = null,
    ) {}


    public static function fromEntity(WidgetInstanceEntity $instance): self
    {
        return new self(
            $instance->id,
            $instance->widgetId,
            $instance->widgetName,
            $instance->widgetSlug,
            $instance->params,
            $instance->title,
            $instance->uuid,
            $instance->createdAt->format('c'),
            $instance->updatedAt->format('c'),
        );
    }
}
