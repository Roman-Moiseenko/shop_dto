<?php

namespace App\Modules\Content\Domain\Entities;

use App\Modules\Shared\Domain\ValueObjects\Slug;
use DateTimeImmutable;

final class MenuEntity
{
    public ?int $id = null {
        get => $this->id;
        set => $this->id = $value;
    }

    public string $name {
        get => $this->name;
        set => $this->name = $value;
    }

    public Slug $slug {
        get => $this->slug;
        set => $this->slug = $value;
    }

    public ?string $description = null {
        get => $this->description;
        set => $this->description = $value;
    }

    public bool $isActive {
        get => $this->isActive;
        set => $this->isActive = $value;
    }

    public ?DateTimeImmutable $createdAt = null {
        get => $this->createdAt;
        set => $this->createdAt = $value;
    }

    public ?DateTimeImmutable $updatedAt = null {
        get => $this->updatedAt;
        set => $this->updatedAt = $value;
    }


    public function __construct(string $name, Slug $slug, ?string $description = null, bool $isActive = true)
    {
        $this->name = $name;
        $this->slug = $slug;
        $this->description = $description;
        $this->isActive = $isActive;
    }

    public function activate(): void { $this->isActive = true; }
    public function deactivate(): void { $this->isActive = false; }
}
