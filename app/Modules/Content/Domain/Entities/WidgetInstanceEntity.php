<?php

namespace App\Modules\Content\Domain\Entities;

use DateTimeImmutable;
use Ramsey\Uuid\Uuid;

/**
 * WidgetInstanceEntity — настроенный экземпляр виджета с конкретными параметрами.
 */
final class WidgetInstanceEntity
{
    public ?int $id = null {
        get => $this->id;
        set => $this->id = $value;
    }

    public string $uuid {
        get => $this->uuid;
    }

    public int $widgetId {
        get => $this->widgetId;
    }

    public array $params {
        get => $this->params;
        set => $this->params = $value;
    }

    public ?string $title = null {
        get => $this->title;
        set => $this->title = $value;
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
        int     $widgetId,
        array   $params = [],
        ?string $title = null,
    )
    {
        $this->uuid = Uuid::uuid4()->toString();
        $this->widgetId = $widgetId;
        $this->params= $params;
        $this->title = $title;
    }
}
