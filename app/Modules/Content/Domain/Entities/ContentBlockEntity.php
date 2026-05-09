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

    public int $sortOrder = 0 {
        get => $this->sortOrder;
        set => $this->sortOrder = $value;
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

    public function __construct(
        ContainerType $containerType,
        int $containerId,
        int $widgetInstanceId,
        int $sortOrder = 0,
        ?string $section = null,
        ?string $caption = null
    ) {
        $this->containerType = $containerType;
        $this->containerId = $containerId;
        $this->widgetInstanceId = $widgetInstanceId;
        $this->sortOrder = $sortOrder;
        $this->section = $section;
        $this->caption = $caption;
    }
}
