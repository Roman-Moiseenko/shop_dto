<?php

namespace App\Modules\Content\Tests\Unit\Domain\ValueObjects;
use App\Modules\Content\Domain\ValueObjects\ContentType;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
class ContentTypeTest extends TestCase
{
    #[Test] public function creates_simple(): void
    {
        $type = new ContentType('simple');
        $this->assertTrue($type->isSimple());
        $this->assertFalse($type->isWidgetBased());
    }

    #[Test] public function creates_widget_based(): void
    {
        $type = new ContentType('widget_based');
        $this->assertTrue($type->isWidgetBased());
    }

    #[Test] public function throws_on_invalid(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new ContentType('mixed');
    }

    #[Test] public function static_factories(): void
    {
        $this->assertSame('simple', ContentType::simple()->getValue());
        $this->assertSame('widget_based', ContentType::widgetBased()->getValue());
    }
}
