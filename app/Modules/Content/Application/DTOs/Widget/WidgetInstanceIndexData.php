<?php

namespace App\Modules\Content\Application\DTOs\Widget;

use App\Modules\Content\Domain\Entities\WidgetInstanceEntity;
use Spatie\LaravelData\Attributes\Validation\IntegerType;
use Spatie\LaravelData\Attributes\Validation\Required;
use Spatie\LaravelData\Data;

class WidgetInstanceIndexData extends Data
{
    public function __construct(
        #[Required, IntegerType]
        public readonly int $id,
        public readonly string $widgetName,
        public readonly string $widgetSlug,
        public readonly ?string $title = null,
        public readonly ?string $uuid = null,
    ) {}


    public static function fromEntity(WidgetInstanceEntity $instance): self
    {
        return new self(
            $instance->id,
            $instance->widgetName,
            $instance->widgetSlug,
            $instance->title,
            $instance->uuid,

        );
    }

    public static function from(mixed ...$payloads): static
    {
        if (count($payloads) === 1 && $payloads[0] instanceof WidgetInstanceEntity) {
            return static::fromEntity($payloads[0]);
        }

        return parent::from(...$payloads);
    }
}
