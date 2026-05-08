<?php

namespace App\Modules\Content\Tests\Unit\Domain\ValueObjects;
use App\Modules\Content\Domain\ValueObjects\WidgetCategory;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class WidgetCategoryTest extends TestCase
{
    #[Test] public function creates_valid_categories(): void
    {
        foreach (['content', 'media', 'commerce', 'custom'] as $cat) {
            $category = new WidgetCategory($cat);
            $this->assertSame($cat, $category->getValue());
        }
    }

    #[Test] public function normalizes_case(): void
    {
        $category = new WidgetCategory('CONTENT');
        $this->assertSame('content', $category->getValue());
    }

    #[Test] public function throws_on_invalid(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new WidgetCategory('invalid');
    }

    #[Test] public function static_factories(): void
    {
        $this->assertSame('content', WidgetCategory::content()->getValue());
        $this->assertSame('media', WidgetCategory::media()->getValue());
        $this->assertSame('commerce', WidgetCategory::commerce()->getValue());
        $this->assertSame('custom', WidgetCategory::custom()->getValue());
    }

    #[Test] public function equals_works(): void
    {
        $a = new WidgetCategory('media');
        $b = new WidgetCategory('media');
        $c = new WidgetCategory('content');
        $this->assertTrue($a->equals($b));
        $this->assertFalse($a->equals($c));
    }
}
