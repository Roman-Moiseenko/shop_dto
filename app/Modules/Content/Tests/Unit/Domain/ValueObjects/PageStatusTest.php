<?php

namespace App\Modules\Content\Tests\Unit\Domain\ValueObjects;
use App\Modules\Content\Domain\ValueObjects\PageStatus;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
class PageStatusTest extends TestCase
{
    #[Test] public function creates_draft(): void
    {
        $status = new PageStatus('draft');
        $this->assertTrue($status->isDraft());
        $this->assertFalse($status->isPublished());
    }

    #[Test] public function creates_published(): void
    {
        $status = new PageStatus('published');
        $this->assertTrue($status->isPublished());
    }

    #[Test] public function throws_on_invalid(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new PageStatus('archived');
    }

    #[Test] public function static_factory_methods(): void
    {
        $this->assertSame('draft', PageStatus::draft()->getValue());
        $this->assertSame('published', PageStatus::published()->getValue());
    }
}
