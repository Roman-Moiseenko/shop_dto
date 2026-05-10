<?php

namespace App\Modules\Content\Application\DTOs;

use App\Modules\Content\Domain\Entities\ContentBlockEntity;
use Spatie\LaravelData\Attributes\Validation\Required;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Attributes\Validation\IntegerType;

class ContentBlockViewData extends Data
{
    public function __construct(
        #[Required, IntegerType]
        public readonly int $id,
        public readonly int $sort,
        public readonly ?string $section,
        public readonly ?string $caption,
        /** Полные данные связанного экземпляра виджета */
        public readonly ?WidgetInstanceViewData $widgetInstance,
    ) {}

    public static function fromEntity(ContentBlockEntity $block): self
    {
        return new self(
            id: $block->id,
            sort: $block->sort,
            section: $block->section,
            caption: $block->caption,
            widgetInstance: $block->widgetInstance
                ? WidgetInstanceViewData::fromEntity($block->widgetInstance)
                : null,
        );
    }

    public static function from(mixed ...$payloads): static
    {
        if (count($payloads) === 1 && $payloads[0] instanceof ContentBlockEntity) {
            return static::fromEntity($payloads[0]);
        }
        return parent::from(...$payloads);
    }

}
