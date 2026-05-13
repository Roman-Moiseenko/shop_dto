<?php

namespace App\Modules\Content\Tests\Unit\Domain\Entities;

use App\Modules\Content\Domain\Entities\MenuEntity;
use App\Modules\Shared\Domain\ValueObjects\Slug;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use DateTimeImmutable;

class MenuEntityTest extends TestCase
{
    private Slug $slug;

    protected function setUp(): void
    {
        parent::setUp();
        $this->slug = new Slug('test-menu');
    }

    #[Test]
    public function creates_with_required_fields(): void
    {
        $menu = new MenuEntity('Test Menu', $this->slug);

        $this->assertNull($menu->id);
        $this->assertSame('Test Menu', $menu->name);
        $this->assertSame('test-menu', (string)$menu->slug);
        $this->assertNull($menu->description);
        $this->assertTrue($menu->isActive);
        $this->assertNull($menu->createdAt);
        $this->assertNull($menu->updatedAt);
    }

    #[Test]
    public function creates_with_optional_fields(): void
    {
        $menu = new MenuEntity('Optional', $this->slug, 'Description', false);
        $this->assertSame('Description', $menu->description);
        $this->assertFalse($menu->isActive);
    }

    #[Test]
    public function activate_and_deactivate_work(): void
    {
        $menu = new MenuEntity('Act', $this->slug, isActive: false);
        $menu->activate();
        $this->assertTrue($menu->isActive);
        $menu->deactivate();
        $this->assertFalse($menu->isActive);
    }

    #[Test]
    public function can_set_dates(): void
    {
        $menu = new MenuEntity('Date', $this->slug);
        $now = new DateTimeImmutable();
        $menu->createdAt = $now;
        $menu->updatedAt = $now;
        $this->assertSame($now, $menu->createdAt);
        $this->assertSame($now, $menu->updatedAt);
    }

    #[Test]
    public function can_set_id(): void
    {
        $menu = new MenuEntity('ID', $this->slug);
        $menu->id = 42;
        $this->assertSame(42, $menu->id);
    }
}
