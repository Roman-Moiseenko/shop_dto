<?php

namespace App\Modules\Content\Tests\Unit\Domain\ValueObjects;

use App\Modules\Content\Domain\ValueObjects\ContainerType;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class ContainerTypeTest extends TestCase
{
    #[Test] public function creates_valid_types(): void
    {
        $page = new ContainerType('page');
        $post = new ContainerType('post');
        $this->assertSame('page', $page->getValue());
        $this->assertSame('post', $post->getValue());
    }

    #[Test] public function throws_on_invalid(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new ContainerType('product');
    }

    #[Test] public function static_factories(): void
    {
        $this->assertSame('page', ContainerType::page()->getValue());
        $this->assertSame('post', ContainerType::post()->getValue());
    }

    #[Test] public function equals_works(): void
    {
        $a = ContainerType::page();
        $b = ContainerType::page();
        $c = ContainerType::post();
        $this->assertTrue($a->equals($b));
        $this->assertFalse($a->equals($c));
    }
}
