<?php

namespace App\Modules\Content\Application\DTOs\Public;
use App\Modules\Content\Domain\Entities\ContentBlockEntity;
use Spatie\LaravelData\Data;
class ContentBlockPublicData extends Data
{
    public function __construct(
        public readonly int $id,
        public readonly int $sort,
        public readonly ?string $section,
        public readonly WidgetInstancePublicData $widgetInstance,
    ) {}

    public static function fromEntity(ContentBlockEntity $block): self
    {
        return new self(
            id: $block->id,
            sort: $block->sort,
            section: $block->section,
            widgetInstance: WidgetInstancePublicData::fromEntity($block->widgetInstance),
        );
    }
}
