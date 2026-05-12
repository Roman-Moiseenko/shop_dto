<?php

namespace App\Modules\Storage\Tests\Unit\Domain\Entities;

use App\Modules\Shared\Domain\ValueObjects\Slug;
use App\Modules\Storage\Domain\Entities\GalleryEntity;
use App\Modules\Storage\Domain\ValueObjects\GalleryName;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use DateTimeImmutable;

class GalleryEntityTest extends TestCase
{
    private GalleryName $name;
    private Slug $slug;

    protected function setUp(): void
    {
        parent::setUp();
        $this->name = new GalleryName('Летние скидки');
        $this->slug = new Slug('letnie-skidki');
    }

    #[Test]
    public function creates_entity_with_required_fields(): void
    {
        $gallery = new GalleryEntity($this->name, $this->slug);

        $this->assertNull($gallery->id);
        $this->assertSame('Летние скидки', $gallery->name->getValue());
        $this->assertSame('letnie-skidki', (string) $gallery->slug);
        $this->assertNull($gallery->description);
        $this->assertTrue($gallery->isActive);
        $this->assertNull($gallery->createdAt);
        $this->assertNull($gallery->updatedAt);
    }

    #[Test]
    public function creates_entity_with_optional_fields(): void
    {
        $gallery = new GalleryEntity(
            $this->name,
            $this->slug,
            description: 'Акционные товары',
            isActive: false
        );

        $this->assertSame('Акционные товары', $gallery->description);
        $this->assertFalse($gallery->isActive);
    }

    #[Test]
    public function activate_and_deactivate_work(): void
    {
        $gallery = new GalleryEntity($this->name, $this->slug, isActive: false);

        $gallery->activate();
        $this->assertTrue($gallery->isActive);

        $gallery->deactivate();
        $this->assertFalse($gallery->isActive);
    }

    #[Test]
    public function can_set_dates(): void
    {
        $gallery = new GalleryEntity($this->name, $this->slug);
        $now = new DateTimeImmutable();
        $gallery->createdAt = $now;
        $gallery->updatedAt = $now;
        $this->assertSame($now, $gallery->createdAt);
        $this->assertSame($now, $gallery->updatedAt);
    }

    #[Test]
    public function can_set_id(): void
    {
        $gallery = new GalleryEntity($this->name, $this->slug);
        $gallery->id = 42;
        $this->assertSame(42, $gallery->id);
    }

    #[Test]
    public function can_update_name_and_slug(): void
    {
        $gallery = new GalleryEntity($this->name, $this->slug);
        $newName = new GalleryName('Новое название');
        $newSlug = new Slug('novoe-nazvanie');

        $gallery->name = $newName;
        $gallery->slug = $newSlug;

        $this->assertSame('Новое название', $gallery->name->getValue());
        $this->assertSame('novoe-nazvanie', (string) $gallery->slug);
    }
}
