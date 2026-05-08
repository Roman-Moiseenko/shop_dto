<?php

namespace App\Modules\Content\Tests\Unit\Domain\Entities;

use App\Modules\Content\Domain\Entities\WidgetEntity;
use App\Modules\Content\Domain\ValueObjects\WidgetCategory;
use App\Modules\Content\Domain\ValueObjects\WidgetSchema;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use DateTimeImmutable;

class WidgetEntityTest extends TestCase
{
    private WidgetCategory $category;
    private WidgetSchema $schema;

    protected function setUp(): void
    {
        parent::setUp();
        $this->category = WidgetCategory::content();
        $this->schema = new WidgetSchema([
            'type' => 'object',
            'properties' => ['text' => ['type' => 'string']],
        ]);
    }

    #[Test] public function creates_widget_with_required_fields(): void
    {
        $widget = new WidgetEntity('Text Block', 'text-block', $this->category, $this->schema);

        $this->assertNull($widget->id);
        $this->assertSame('Text Block', $widget->name);
        $this->assertSame('text-block', $widget->slug);
        $this->assertNull($widget->description);
        $this->assertSame('content', $widget->category->getValue());
        $this->assertSame($this->schema, $widget->schema);
        $this->assertNull($widget->createdAt);
        $this->assertNull($widget->updatedAt);
    }

    #[Test] public function creates_widget_with_description(): void
    {
        $widget = new WidgetEntity('Banner', 'banner', $this->category, $this->schema, 'A promotional banner');
        $this->assertSame('A promotional banner', $widget->description);
    }

    #[Test] public function can_set_dates(): void
    {
        $widget = new WidgetEntity('Test', 'test', $this->category, $this->schema);
        $now = new DateTimeImmutable();
        $widget->createdAt = $now;
        $widget->updatedAt = $now;
        $this->assertSame($now, $widget->createdAt);
        $this->assertSame($now, $widget->updatedAt);
    }
}
