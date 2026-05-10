<?php

namespace App\Modules\Content\Application\DTOs\Public;

use App\Modules\Content\Application\DTOs\MetaData;
use App\Modules\Content\Domain\Entities\ContentBlockEntity;
use App\Modules\Content\Domain\Entities\PageEntity;
use Spatie\LaravelData\Data;

class PagePublicData extends Data
{
    public function __construct(
        public readonly int $id,
        public readonly string $title,
        public readonly string $slug,
        public readonly ?string $content,
        public readonly string $contentType,
        public readonly ?string $publishedAt,
        public readonly ?MetaData $meta,
        public readonly ?string $template,
        /** @var ContentBlockPublicData[] */
        public readonly array $blocks,
    ) {}

    /**
     * @param ContentBlockEntity[] $blocks
     */
    public static function fromEntity(PageEntity $page, array $blocks = []): self
    {
        $blockData = array_map(
            fn(ContentBlockEntity $b) => ContentBlockPublicData::fromEntity($b),
            $blocks
        );

        return new self(
            id: $page->id,
            title: $page->title,
            slug: (string) $page->slug,
            content: $page->content,
            contentType: $page->contentType->getValue(),
            publishedAt: $page->publishedAt?->format('c'),
            meta: $page->meta ? MetaData::fromEntity($page->meta) : null,
            template: $page->template ? (string) $page->template : null,
            blocks: $blockData,
        );
    }
}
