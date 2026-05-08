<?php

namespace App\Modules\Content\Tests\Unit\Domain\ValueObjects;
use App\Modules\Content\Domain\ValueObjects\PageTemplate;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
class PageTemplateTest extends TestCase
{
    #[Test] public function creates_default_template(): void
    {
        $tpl = new PageTemplate('default');
        $this->assertFalse($tpl->isCustom());
    }

    #[Test] public function creates_custom_template(): void
    {
        $tpl = new PageTemplate('landing');
        $this->assertTrue($tpl->isCustom());
    }

    #[Test] public function throws_on_invalid(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new PageTemplate('unknown');
    }

    #[Test] public function static_factories(): void
    {
        $this->assertSame('landing', PageTemplate::landing()->getValue());
        $this->assertSame('full-width', PageTemplate::fullWidth()->getValue());
    }
}
