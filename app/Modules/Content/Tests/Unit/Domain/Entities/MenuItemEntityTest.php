<?php

namespace App\Modules\Content\Tests\Unit\Domain\Entities;

use App\Modules\Content\Domain\Entities\MenuItemEntity;
use App\Modules\Content\Domain\Entities\WidgetInstanceEntity;
use App\Modules\Content\Domain\ValueObjects\MenuItemStyle;
use App\Modules\Content\Domain\ValueObjects\ReferenceType;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class MenuItemEntityTest extends TestCase
{
    #[Test]
    public function creates_with_required_fields(): void
    {
        $item = new MenuItemEntity(menuId: 1, title: 'Home');

        $this->assertNull($item->id);
        $this->assertSame(1, $item->menuId);
        $this->assertSame('Home', $item->title);
        $this->assertNull($item->parentId);
        $this->assertNull($item->url);
        $this->assertNull($item->referenceType);
        $this->assertNull($item->referenceId);
        $this->assertNull($item->iconUuid);
        $this->assertNull($item->style);
        $this->assertFalse($item->targetBlank);
        $this->assertSame(0, $item->sort);
        $this->assertFalse($item->isActive);
        $this->assertNull($item->widgetInstanceId);
        $this->assertNull($item->widgetInstance);
        $this->assertEmpty($item->children);
    }

    #[Test]
    public function creates_with_all_fields(): void
    {
        $refType = ReferenceType::page();
        $style = new MenuItemStyle('sale');
        $item = new MenuItemEntity(
            menuId: 2,
            title: 'Catalog',
            parentId: 1,
            url: '/catalog',
            referenceType: $refType,
            referenceId: 10,
            iconUuid: 'uuid-icon',
            style: $style,
            targetBlank: true,
            sort: 5,
            isActive: false,
            widgetInstanceId: 99
        );

        $this->assertSame(2, $item->menuId);
        $this->assertSame(1, $item->parentId);
        $this->assertSame('/catalog', $item->url);
        $this->assertSame($refType, $item->referenceType);
        $this->assertSame(10, $item->referenceId);
        $this->assertSame('uuid-icon', $item->iconUuid);
        $this->assertSame($style, $item->style);
        $this->assertTrue($item->targetBlank);
        $this->assertSame(5, $item->sort);
        $this->assertFalse($item->isActive);
        $this->assertSame(99, $item->widgetInstanceId);
    }

    #[Test]
    public function can_set_widget_instance(): void
    {
        $item = new MenuItemEntity(menuId: 1, title: 'With widget');
        $widgetInstance = new WidgetInstanceEntity(widgetId: 7, params: []);
        $item->widgetInstance = $widgetInstance;
        $this->assertSame($widgetInstance, $item->widgetInstance);
    }

    #[Test]
    public function can_set_children(): void
    {
        $parent = new MenuItemEntity(menuId: 1, title: 'Parent');
        $child1 = new MenuItemEntity(menuId: 1, title: 'Child 1', parentId: 0);
        $child2 = new MenuItemEntity(menuId: 1, title: 'Child 2', parentId: 0);
        $parent->children = [$child1, $child2];
        $this->assertSame([$child1, $child2], $parent->children);
    }
}
