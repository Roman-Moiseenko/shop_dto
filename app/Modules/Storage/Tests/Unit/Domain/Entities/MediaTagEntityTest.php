<?php

namespace App\Modules\Storage\Tests\Unit\Domain\Entities;

use App\Modules\Shared\Domain\ValueObjects\Slug;
use App\Modules\Storage\Domain\Entities\MediaTagEntity;
use App\Modules\Storage\Domain\ValueObjects\TagName;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use DateTimeImmutable;

class MediaTagEntityTest extends TestCase
{
    #[Test]
    public function creates_entity_with_required_fields(): void
    {
        $name = new TagName('Summer 2026');
        $slug = new Slug('summer-2026');
        $tag = new MediaTagEntity($name, $slug);

        $this->assertNull($tag->id);
        $this->assertSame('Summer 2026', $tag->name->getValue());
        $this->assertSame('summer-2026', (string) $tag->slug);
        $this->assertNull($tag->createdAt);
        $this->assertNull($tag->updatedAt);
    }

    #[Test]
    public function can_set_dates(): void
    {
        $tag = new MediaTagEntity(new TagName('Test'), new Slug('test'));
        $now = new DateTimeImmutable();
        $tag->createdAt = $now;
        $tag->updatedAt = $now;
        $this->assertSame($now, $tag->createdAt);
        $this->assertSame($now, $tag->updatedAt);
    }

    #[Test]
    public function can_update_name_and_slug(): void
    {
        $tag = new MediaTagEntity(new TagName('Old'), new Slug('old'));
        $tag->name = new TagName('New');
        $tag->slug = new Slug('new');
        $this->assertSame('New', $tag->name->getValue());
        $this->assertSame('new', (string) $tag->slug);
    }
}
