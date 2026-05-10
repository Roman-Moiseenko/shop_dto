<?php

namespace App\Modules\Content\Application\DTOs\Widget;

use App\Modules\Content\Domain\Entities\WidgetEntity;
use Spatie\LaravelData\Attributes\Validation\IntegerType;
use Spatie\LaravelData\Attributes\Validation\Required;
use Spatie\LaravelData\Data;

/**
 * DTO для возврата данных на фронтенд
 */
class WidgetIndexData extends Data
{
    public function __construct(
        #[Required, IntegerType]
        public int              $id,
        public readonly string  $name,
        public readonly string  $slug,
        public readonly ?string $description = null,
        public readonly string  $category,

    )
    {
    }

    public static function fromEntity(WidgetEntity $widget): self
    {
        return new self(
            $widget->id,
            $widget->name,
            $widget->slug,
            $widget->description,
            $widget->category->getValue(),

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
