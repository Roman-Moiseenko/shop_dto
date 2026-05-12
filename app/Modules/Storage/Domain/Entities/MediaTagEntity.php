<?php

namespace App\Modules\Storage\Domain\Entities;

use App\Modules\Shared\Domain\ValueObjects\Slug;
use App\Modules\Storage\Domain\ValueObjects\TagName;
use DateTimeImmutable;

class MediaTagEntity
{
    public ?int $id = null {
        get => $this->id;
        set => $this->id = $value;
    }

    public TagName $name {
        get => $this->name;
        set => $this->name = $value;
    }

    public Slug $slug {
        get => $this->slug;
        set => $this->slug = $value;
    }

    public ?DateTimeImmutable $createdAt = null {
        get => $this->createdAt;
        set => $this->createdAt = $value;
    }

    public ?DateTimeImmutable $updatedAt = null {
        get => $this->updatedAt;
        set => $this->updatedAt = $value;
    }


    public function __construct(TagName $name, Slug $slug)
    {
        $this->name = $name;
        $this->slug = $slug;
    }
}
