<?php

namespace App\Modules\Content\Tests\Unit\Domain\ValueObjects;

use App\Modules\Content\Domain\ValueObjects\ReferenceType;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class ReferenceTypeTest extends TestCase
{
    #[Test]
    public function creates_valid_types(): void
    {
        foreach (['page', 'blog.category', 'blog.post', 'catalog.product', 'catalog.category', 'custom'] as $type) {
            $ref = new ReferenceType($type);
            $this->assertSame($type, $ref->getValue());
        }
    }

    #[Test]
    public function normalizes_case(): void
    {
        $ref = new ReferenceType('PAGE');
        $this->assertSame('page', $ref->getValue());
    }

    #[Test]
    public function throws_on_invalid(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new ReferenceType('product');
    }

    #[Test]
    public function static_factories(): void
    {
        $this->assertSame('page', ReferenceType::page()->getValue());
        $this->assertSame('blog.category', ReferenceType::blogCategory()->getValue());
        $this->assertSame('custom', ReferenceType::custom()->getValue());
    }

    #[Test]
    public function equals_works(): void
    {
        $a = new ReferenceType('page');
        $b = new ReferenceType('page');
        $c = new ReferenceType('custom');
        $this->assertTrue($a->equals($b));
        $this->assertFalse($a->equals($c));
    }
}
