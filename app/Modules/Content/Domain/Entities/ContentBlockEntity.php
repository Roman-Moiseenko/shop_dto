<?php

namespace App\Modules\Content\Domain\Entities;
use App\Modules\Content\Domain\ValueObjects\ContainerType;
use DateTimeImmutable;

/**
 * ContentBlock — связующая сущность. Связывает экземпляр виджета с контейнером (страница, пост блога) и задаёт порядок.
 */
final class ContentBlockEntity
{
    public ?int $id = null {
        get => $this->id;
        set => $this->id = $value;
    }
    public ?string $caption = null {
        get => $this->caption;
        set => $this->caption = $value;
    }
    public ContainerType $containerType {
        get => $this->containerType;
    }

    public int $containerId {
        get => $this->containerId;
    }

    public int $widgetInstanceId {
        get => $this->widgetInstanceId;
    }

    public ?int $sort = null {
        get => $this->sort;
        set => $this->sort = $value;
    }

    public ?string $section = null {
        get => $this->section;
        set => $this->section = $value;
    }

    public ?DateTimeImmutable $createdAt = null {
        get => $this->createdAt;
        set => $this->createdAt = $value;
    }

    public ?DateTimeImmutable $updatedAt = null {
        get => $this->updatedAt;
        set => $this->updatedAt = $value;
    }
    public ?WidgetInstanceEntity $widgetInstance = null {
        get => $this->widgetInstance;
        set => $this->widgetInstance = $value;
    }
    public function __construct(
        ContainerType $containerType,
        int $containerId,
        int $widgetInstanceId,
        ?int $sort = null,
        ?string $section = null,
        ?string $caption = null
    ) {
        $this->containerType = $containerType;
        $this->containerId = $containerId;
        $this->widgetInstanceId = $widgetInstanceId;
        $this->sort = $sort;
        $this->section = $section;
        $this->caption = $caption;
    }
}
