<?php

namespace App\Modules\Content\Tests\Unit\Domain\ValueObjects;

use App\Modules\Content\Domain\ValueObjects\MenuItemStyle;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class MenuItemStyleTest extends TestCase
{
    #[Test]
    public function creates_valid_styles(): void
    {
        foreach (['sale', 'highlight', 'new'] as $style) {
            $s = new MenuItemStyle($style);
            $this->assertSame($style, $s->getValue());
        }
    }

    #[Test]
    public function normalizes_case(): void
    {
        $s = new MenuItemStyle('SALE');
        $this->assertSame('sale', $s->getValue());
    }

    #[Test]
    public function throws_on_invalid(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new MenuItemStyle('urgent');
    }

    #[Test]
    public function equals_works(): void
    {
        $a = new MenuItemStyle('new');
        $b = new MenuItemStyle('new');
        $c = new MenuItemStyle('sale');
        $this->assertTrue($a->equals($b));
        $this->assertFalse($a->equals($c));
    }
}
