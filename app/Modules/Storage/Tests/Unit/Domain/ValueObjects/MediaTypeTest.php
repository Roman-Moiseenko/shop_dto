<?php

namespace App\Modules\Storage\Tests\Unit\Domain\ValueObjects;

use App\Modules\Storage\Domain\ValueObjects\MediaType;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class MediaTypeTest extends TestCase
{
    #[Test]
    public function creates_valid_types(): void
    {
        $icon = new MediaType('icon');
        $image = new MediaType('image');
        $gallery = new MediaType('gallery');

        $this->assertSame('icon', $icon->getValue());
        $this->assertSame('image', $image->getValue());
        $this->assertSame('gallery', $gallery->getValue());
    }

    #[Test]
    public function normalizes_case(): void
    {
        $type = new MediaType('ICON');
        $this->assertSame('icon', $type->getValue());
    }

    #[Test]
    public function trims_whitespace(): void
    {
        $type = new MediaType('  gallery  ');
        $this->assertSame('gallery', $type->getValue());
    }

    #[Test]
    public function throws_exception_on_invalid_type(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new MediaType('video');
    }

    #[Test]
    public function is_single_returns_true_for_icon_and_image(): void
    {
        $this->assertTrue(new MediaType('icon')->isSingle());
        $this->assertTrue(new MediaType('image')->isSingle());
    }

    #[Test]
    public function is_single_returns_false_for_gallery(): void
    {
        $this->assertFalse(new MediaType('gallery')->isSingle());
    }

    #[Test]
    public function equals_works(): void
    {
        $a = new MediaType('image');
        $b = new MediaType('image');
        $c = new MediaType('gallery');
        $d = new MediaType('IMAGE'); // после нормализации тот же

        $this->assertTrue($a->equals($b));
        $this->assertTrue($a->equals($d));
        $this->assertFalse($a->equals($c));
    }

    #[Test]
    public function to_string_returns_value(): void
    {
        $type = new MediaType('icon');
        $this->assertSame('icon', (string) $type);
    }
}
