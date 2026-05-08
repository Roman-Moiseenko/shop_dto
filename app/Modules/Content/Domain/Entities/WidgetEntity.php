<?php

namespace App\Modules\Content\Domain\Entities;
use App\Modules\Content\Domain\ValueObjects\WidgetCategory;
use App\Modules\Content\Domain\ValueObjects\WidgetSchema;
use DateTimeImmutable;
final class WidgetEntity
{
    public ?int $id = null {
        get => $this->id;
        set => $this->id = $value;
    }

    public string $name {
        get => $this->name;
        set => $this->name = $value;
    }

    public string $slug {
        get => $this->slug;
        set => $this->slug = $value;
    }

    public ?string $description = null {
        get => $this->description;
        set => $this->description = $value;
    }

    public WidgetCategory $category {
        get => $this->category;
        set => $this->category = $value;
    }

    public WidgetSchema $schema {
        get => $this->schema;
    }

    public ?DateTimeImmutable $createdAt = null {
        get => $this->createdAt;
        set => $this->createdAt = $value;
    }

    public ?DateTimeImmutable $updatedAt = null {
        get => $this->updatedAt;
        set => $this->updatedAt = $value;
    }

    public function __construct(
        string $name,
        string $slug,
        WidgetCategory $category,
        WidgetSchema $schema,
        ?string $description = null,
    ) {
        $this->name = $name;
        $this->slug = $slug;
        $this->category = $category;
        $this->schema = $schema;
        $this->description= $description;
    }
}
