<?php

namespace App\Modules\Content\Domain\Entities;

use App\Modules\Content\Domain\ValueObjects\ContactType;
use DateTimeImmutable;

final class ContactEntity
{
    public ?int $id = null {
        get => $this->id;
        set => $this->id = $value;
    }
    public ContactType $type {
        get => $this->type;
        set => $this->type = $value;
    }
    public string $value {
        get => $this->value;
        set => $this->value = $value;
    }
    public ?string $link = null {
        get => $this->link;
        set => $this->link = $value;
    }
    public ?string $iconUuid = null {
        get => $this->iconUuid;
        set => $this->iconUuid = $value;
    }
    public ?string $caption = null {
        get => $this->caption;
        set => $this->caption = $value;
    }
    public ?string $analyticsField = null {
        get => $this->analyticsField;
        set => $this->analyticsField = $value;
    }
    public int $sort = 0 {
        get => $this->sort;
        set => $this->sort = $value;
    }
    public bool $isActive = true {
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

    public function __construct(
        ContactType  $type,
        string  $value,
        ?string $link = null,
        ?string $iconUuid = null,
        ?string $caption = null,
        ?string $analyticsField = null,
        int     $sort = 0,
        bool    $isActive = false,
    )
    {
        $this->type = $type;
        $this->value = $value;
        $this->link = $link;
        $this->iconUuid = $iconUuid;
        $this->caption = $caption;
        $this->analyticsField = $analyticsField;
        $this->sort = $sort;
        $this->isActive = $isActive;
    }
}
