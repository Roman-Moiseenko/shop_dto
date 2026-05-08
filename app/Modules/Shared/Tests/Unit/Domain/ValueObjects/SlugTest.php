<?php

namespace App\Modules\Shared\Tests\Unit\Domain\ValueObjects;
use App\Modules\Shared\Domain\ValueObjects\Slug;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
class SlugTest extends TestCase
{
    #[Test] public function creates_slug_from_string(): void
    {
        $slug = new Slug('Hello World');
        $this->assertSame('hello-world', (string)$slug);
    }

    #[Test] public function creates_from_array_of_segments(): void
    {
        $slug = new Slug(['parent', 'child']);
        $this->assertSame('parent/child', (string)$slug);
    }

    #[Test] public function throws_exception_on_empty(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new Slug('   ');
    }

    #[Test] public function with_parent_creates_composite_slug(): void
    {
        $parent = new Slug('electronics');
        $child = new Slug('phones');
        $composite = $child->withParent($parent);
        $this->assertSame('electronics/phones', (string)$composite);
    }

    #[Test] public function last_segment_returns_final_part(): void
    {
        $slug = new Slug(['parent', 'child']); // создаём через массив
        $this->assertSame('child', $slug->lastSegment());
    }
}
