<?php

namespace App\Modules\Storage\Tests\Unit\Domain\ValueObjects;

use App\Modules\Storage\Domain\ValueObjects\GalleryName;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class GalleryNameTest extends TestCase
{
    #[Test]
    public function creates_valid_name(): void
    {
        $name = new GalleryName('Summer Sale 2026');
        $this->assertSame('Summer Sale 2026', $name->getValue());
    }

    #[Test]
    public function trims_spaces(): void
    {
        $name = new GalleryName('  Discount  ');
        $this->assertSame('Discount', $name->getValue());
    }

    #[Test]
    public function throws_exception_on_too_short_name(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new GalleryName('Ab');
    }

    #[Test]
    public function throws_exception_on_too_long_name(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new GalleryName(str_repeat('x', 101));
    }

    #[Test]
    public function toString_returns_value(): void
    {
        $name = new GalleryName('Test Gallery');
        $this->assertSame('Test Gallery', (string) $name);
    }

    #[Test]
    public function equals_works(): void
    {
        $a = new GalleryName('Promotions');
        $b = new GalleryName('Promotions');
        $c = new GalleryName('New Arrivals');
        $this->assertTrue($a->equals($b));
        $this->assertFalse($a->equals($c));
    }
}
