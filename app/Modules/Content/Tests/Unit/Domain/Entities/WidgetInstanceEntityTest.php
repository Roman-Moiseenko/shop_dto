<?php

namespace App\Modules\Content\Tests\Unit\Domain\Entities;
use App\Modules\Content\Domain\Entities\WidgetInstanceEntity;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use DateTimeImmutable;

class WidgetInstanceEntityTest extends TestCase
{
    #[Test] public function creates_instance_with_minimum_data(): void
    {
        $instance = new WidgetInstanceEntity(42, ['key' => 'value']);

        $this->assertNull($instance->id);
        $this->assertSame(42, $instance->widgetId);
        $this->assertSame(['key' => 'value'], $instance->params);
        $this->assertNull($instance->title);
        $this->assertNotEmpty($instance->uuid); // UUID генерируется автоматически
        $this->assertSame(36, strlen($instance->uuid));
        $this->assertNull($instance->createdAt);
        $this->assertNull($instance->updatedAt);
    }

    #[Test] public function creates_instance_with_title(): void
    {
        $instance = new WidgetInstanceEntity(1, [], 'Hero Banner');
        $this->assertSame('Hero Banner', $instance->title);
    }

    #[Test] public function can_set_dates(): void
    {
        $instance = new WidgetInstanceEntity(1, []);
        $now = new DateTimeImmutable();
        $instance->createdAt = $now;
        $instance->updatedAt = $now;
        $this->assertSame($now, $instance->createdAt);
        $this->assertSame($now, $instance->updatedAt);
    }
}
