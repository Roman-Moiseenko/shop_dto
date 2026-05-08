<?php

namespace App\Modules\Content\Domain\Entities;

use App\Modules\Content\Domain\ValueObjects\ContentType;
use App\Modules\Content\Domain\ValueObjects\Meta;
use App\Modules\Content\Domain\ValueObjects\PageStatus;
use App\Modules\Content\Domain\ValueObjects\PageTemplate;
use App\Modules\Shared\Domain\ValueObjects\Slug;
use DateTimeImmutable;

class PageEntity
{
    public ?int $id = null {
        get => $this->id;
        set => $this->id = $value;
    }

    public Slug $slug {
        get => $this->slug;
    }

    public string $title {
        get => $this->title;
        set => $this->title = $value;
    }

    public ?string $content = null {
        get => $this->content;
        set => $this->content = $value;
    }

    public PageStatus $status {
        get => $this->status;
        set => $this->status = $value;
    }

    public ?DateTimeImmutable $publishedAt = null {
        get => $this->publishedAt;
        set => $this->publishedAt = $value;
    }

    public ?Meta $meta = null {
        get => $this->meta;
        set => $this->meta = $value;
    }

    public ContentType $contentType {
        get => $this->contentType;
    }

    public ?DateTimeImmutable $createdAt = null {
        get => $this->createdAt;
    }

    public ?DateTimeImmutable $updatedAt = null {
        get => $this->updatedAt;
    }

    public array $blocks = [] {
        get => $this->blocks;
        set => $this->blocks = $value;
    }
    public ?int $authorId = null {
        get => $this->authorId;
        set => $this->authorId = $value;
    }
    public ?PageTemplate $template = null {
        get => $this->template;
        set => $this->template = $value;
    }
    public function __construct(
        string $title,
        Slug $slug,
        ?ContentType $contentType = null,
        ?string $content = null,
        ?PageStatus $status = null,
        ?Meta $meta = null,
        ?int $authorId = null,
        ?PageTemplate $template = null,
    ) {
        $this->title = $title;
        $this->slug = $slug;
        $this->contentType = $contentType ?? ContentType::simple();
        $this->content = $content;

        // Устанавливаем статус и дату публикации
        if ($status !== null && $status->isPublished()) {
            $this->publish(); // установит статус published и текущую дату
        } else {
            $this->status = $status ?? PageStatus::draft();
            $this->publishedAt = null;
        }


        $this->status = $status ?? PageStatus::draft();
        $this->meta = $meta;
        $this->authorId = $authorId;
        $this->template = $template;
    }

    public function publish(?DateTimeImmutable $date = null): void
    {
        $this->status = PageStatus::published();
        $this->publishedAt= $date ?? new DateTimeImmutable();
    }

    public function unpublish(): void
    {
        $this->status = PageStatus::draft();
        $this->publishedAt = null;
    }

    public function isPublished(): bool
    {
        return $this->status->isPublished();
    }

    public function isWidgetBased(): bool
    {
        return $this->contentType->isWidgetBased();
    }

    public function getAuthorId(): ?int
    {
        return $this->authorId;
    }

    public function setAuthorId(int $id): void
    {
        $this->authorId = $id;
    }

    public function getBlocks(): array
    {
        return $this->blocks;
    }

    public function setBlocks(array $blocks): void
    {
        $this->blocks = $blocks;
    }

    public function setCreatedAt(DateTimeImmutable $date): void
    {
        $this->createdAt = $date;
    }

    public function setUpdatedAt(DateTimeImmutable $date): void
    {
        $this->updatedAt = $date;
    }
}
