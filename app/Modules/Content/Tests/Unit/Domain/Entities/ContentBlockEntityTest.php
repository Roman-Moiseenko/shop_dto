<?php

namespace App\Modules\Content\Tests\Unit\Domain\Entities;

use App\Modules\Content\Domain\Entities\ContentBlockEntity;
use App\Modules\Content\Domain\ValueObjects\ContainerType;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use DateTimeImmutable;

class ContentBlockEntityTest extends TestCase
{
    #[Test] public function creates_block_with_required_fields(): void
    {
        $block = new ContentBlockEntity(ContainerType::page(), 10, 5);

        $this->assertNull($block->id);
        $this->assertSame('page', $block->containerType->getValue());
        $this->assertSame(10, $block->containerId);
        $this->assertSame(5, $block->widgetInstanceId);
        $this->assertSame(0, $block->sort);
        $this->assertNull($block->section);
        $this->assertNull($block->createdAt);
        $this->assertNull($block->updatedAt);
    }

    #[Test] public function creates_block_with_optional_fields(): void
    {
        $block = new ContentBlockEntity(
            ContainerType::post(),
            20,
            8,
            3,
            'sidebar'
        );

        $this->assertSame(3, $block->sort);
        $this->assertSame('sidebar', $block->section);
    }

    #[Test] public function can_change_sort_order(): void
    {
        $block = new ContentBlockEntity(ContainerType::page(), 1, 2);
        $block->sort = 5;
        $this->assertSame(5, $block->sort);
    }

    #[Test] public function can_set_dates(): void
    {
        $block = new ContentBlockEntity(ContainerType::page(), 1, 2);
        $now = new DateTimeImmutable();
        $block->createdAt = $now;
        $block->updatedAt = $now;
        $this->assertSame($now, $block->createdAt);
        $this->assertSame($now, $block->updatedAt);
    }
}
