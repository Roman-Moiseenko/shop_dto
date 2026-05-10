<?php

namespace App\Modules\Content\Application\DTOs;
use App\Modules\Content\Domain\Entities\ContentBlockEntity;
use App\Modules\Content\Domain\Entities\PageEntity;
use Spatie\LaravelData\Attributes\Validation\Required;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Attributes\Validation\IntegerType;
class PageIndexData extends Data
{
    public function __construct(
        #[Required, IntegerType]
        public readonly int $id,
        public readonly string $title,
        public readonly string $slug,
        public readonly string $contentType,
        public readonly string $status,
        public readonly ?string $publishedAt = null,
        public readonly ?string $template = null,
        public readonly ?int $authorId = null,
        public readonly bool $isTrashed,
        public readonly ?string $createdAt = null,
        public readonly ?string $updatedAt = null,
    ) {}

    public static function fromEntity(PageEntity $page): self
    {
        return new self(
            id: $page->id,
            title: $page->title,
            slug: (string) $page->slug,
            contentType: $page->contentType->getValue(),
            status: $page->status->getValue(),
            publishedAt: $page->publishedAt?->format('c'),
            template: $page->template ? (string) $page->template : null,
            authorId: $page->authorId,
            isTrashed: $page->isTrashed,
            createdAt: $page->createdAt?->format('c'),
            updatedAt: $page->updatedAt?->format('c'),
        );
    }

    public static function from(mixed ...$payloads): static
    {
        if (count($payloads) === 1 && $payloads[0] instanceof PageEntity) {
            return static::fromEntity($payloads[0]);
        }

        return parent::from(...$payloads);
    }
}
