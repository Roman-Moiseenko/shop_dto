<?php

namespace App\Modules\Content\Domain\Entities;

use App\Modules\Content\Domain\ValueObjects\MenuItemStyle;
use App\Modules\Content\Domain\ValueObjects\ReferenceType;
use DateTimeImmutable;

final class MenuItemEntity
{
    public ?int $id = null {
        get => $this->id;
        set => $this->id = $value;
    }

    public int $menuId {
        get => $this->menuId;
        set => $this->menuId = $value;
    }

    public ?int $parentId = null {
        get => $this->parentId;
        set => $this->parentId = $value;
    }

    public string $title {
        get => $this->title;
        set => $this->title = $value;
    }

    public ?string $url = null {
        get => $this->url;
        set => $this->url = $value;
    }

    public ?ReferenceType $referenceType = null {
        get => $this->referenceType;
        set => $this->referenceType = $value;
    }

    public ?int $referenceId = null {
        get => $this->referenceId;
        set => $this->referenceId = $value;
    }

    public ?string $iconUuid = null {
        get => $this->iconUuid;
        set => $this->iconUuid = $value;
    }

    public ?MenuItemStyle $style = null {
        get => $this->style;
        set => $this->style = $value;
    }

    public bool $targetBlank = false {
        get => $this->targetBlank;
        set => $this->targetBlank = $value;
    }

    public int $sort = 0 {
        get => $this->sort;
        set => $this->sort = $value;
    }

    public bool $isActive = true {
        get => $this->isActive;
        set => $this->isActive = $value;
    }

    public ?int $widgetInstanceId = null {
        get => $this->widgetInstanceId;
        set => $this->widgetInstanceId = $value;
    }

    // Связанные сущности (заполняются репозиторием)
    public ?WidgetInstanceEntity $widgetInstance = null {
        get => $this->widgetInstance;
        set => $this->widgetInstance = $value;
    }

    /** @var MenuItemEntity[] */
    public array $children = [] {
        get => $this->children;
        set => $this->children = $value;
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
        int $menuId,
        string $title,
        ?int $parentId = null,
        ?string $url = null,
        ?ReferenceType $referenceType = null,
        ?int $referenceId = null,
        ?string $iconUuid = null,
        ?MenuItemStyle $style = null,
        bool $targetBlank = false,
        int $sort = 0,
        bool $isActive = false,
        ?int $widgetInstanceId = null,
    ) {
        $this->menuId = $menuId;
        $this->title = $title;
        $this->parentId = $parentId;
        $this->url = $url;
        $this->referenceType = $referenceType;
        $this->referenceId = $referenceId;
        $this->iconUuid = $iconUuid;
        $this->style = $style;
        $this->targetBlank = $targetBlank;
        $this->sort = $sort;
        $this->isActive = $isActive;
        $this->widgetInstanceId = $widgetInstanceId;
    }
    public function addChild(MenuItemEntity $child): void
    {
        $children = $this->children;
        $children[] = $child;
        $this->children = $children;
    }

    // Методы для установки связанных сущностей (используются репозиторием)
}
