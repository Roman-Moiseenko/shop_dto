<?php

namespace App\Modules\Content\Application\DTOs\Widget;

use App\Modules\Content\Domain\Entities\WidgetEntity;
use Spatie\LaravelData\Data;

/**
 * DTO для возврата списка на фронтенд в <select/>
 */
class WidgetOptionData extends Data
{
    public function __construct(
        public int              $key,
        public readonly string  $label,
    )
    {
    }

    public static function fromEntity(WidgetEntity $widget): self
    {
        return new self(
            $widget->id,
            $widget->name,
        );
    }

    public static function from(mixed ...$payloads): static
    {
        if (count($payloads) === 1 && $payloads[0] instanceof WidgetEntity) {
            return static::fromEntity($payloads[0]);
        }

        return parent::from(...$payloads);
    }
}
