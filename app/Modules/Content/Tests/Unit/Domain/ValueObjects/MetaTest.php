<?php

namespace App\Modules\Content\Tests\Unit\Domain\ValueObjects;
use App\Modules\Content\Domain\ValueObjects\Meta;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
class MetaTest extends TestCase
{
    #[Test] public function creates_default_meta(): void
    {
        $meta = Meta::default();
        $this->assertSame('', $meta->getTitle());
        $this->assertSame('', $meta->getDescription());
    }

    #[Test] public function sets_values_from_constructor(): void
    {
        $meta = new Meta(['title' => 'Page Title', 'description' => 'Desc']);
        $this->assertSame('Page Title', $meta->getTitle());
        $this->assertSame('Desc', $meta->getDescription());
    }

    #[Test] public function equals_works(): void
    {
        $a = new Meta(['title' => 'A']);
        $b = new Meta(['title' => 'A']);
        $c = new Meta(['title' => 'B']);
        $this->assertTrue($a->equals($b));
        $this->assertFalse($a->equals($c));
    }
}
