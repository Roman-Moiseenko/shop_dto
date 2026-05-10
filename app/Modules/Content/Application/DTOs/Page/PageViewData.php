<?php

namespace App\Modules\Content\Application\DTOs\Page;
use App\Modules\Content\Application\DTOs\ContentBlocks\ContentBlockViewData;
use App\Modules\Content\Application\DTOs\MetaData;
use App\Modules\Content\Domain\Entities\ContentBlockEntity;
use App\Modules\Content\Domain\Entities\PageEntity;
use Spatie\LaravelData\Attributes\Validation\IntegerType;
use Spatie\LaravelData\Attributes\Validation\Required;
use Spatie\LaravelData\Data;

class PageViewData extends Data
{
    public function __construct(
        #[Required, IntegerType]
        public readonly int $id,
        public readonly string $title,
        public readonly string $slug,
        public readonly ?string $content = null,
        public readonly string $contentType,
        public readonly string $status,
        public readonly ?string $publishedAt = null,
        public readonly ?MetaData $meta = null,
        public readonly ?string $template = null,
        public readonly ?int $authorId = null,
        public readonly bool $isTrashed,
        public readonly ?string $createdAt = null,
        public readonly ?string $updatedAt = null,
        /** @var ContentBlockViewData[] */
        public readonly array $blocks,
    ) {}

    /**
     * @param ContentBlockEntity[] $blocks
     */
    public static function fromEntity(PageEntity $page, array $blocks = []): self
    {
        return new self(
            id: $page->id,
            title: $page->title,
            slug: (string) $page->slug,
            content: $page->content,
            contentType: $page->contentType->getValue(),
            status: $page->status->getValue(),
            publishedAt: $page->publishedAt?->format('c'),
            meta: $page->meta ? MetaData::fromEntity($page->meta) : null,
            template: $page->template ? (string) $page->template : null,
            authorId: $page->authorId,
            isTrashed: $page->isTrashed,
            createdAt: $page->createdAt?->format('c'),
            updatedAt: $page->updatedAt?->format('c'),
            blocks: empty($blocks) ? [] : ContentBlockViewData::collect($blocks),
        );
    }
}
