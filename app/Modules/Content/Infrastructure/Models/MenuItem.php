<?php

namespace App\Modules\Content\Infrastructure\Models;

use DateTime;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property int $menu_id
 * @property int $parent_id
 * @property string $title
 * @property string $url
 * @property string $reference_type
 * @property int $reference_id
 * @property string $icon_uuid
 * @property string $style
 * @property bool $target_blank
 * @property int $sort
 * @property bool $is_active
 * @property int $widget_instance_id
 * @property MenuItem[] $children
 * @property MenuItem $parent
 * @property WidgetInstance $widgetInstance
 * @property Menu $menu
 * @property DateTime $created_at
 * @property DateTime $updated_at
 */
class MenuItem extends Model
{
    protected $table = 'menu_items';
    protected $fillable = [
        'menu_id', 'parent_id', 'title', 'url', 'reference_type',
        'reference_id', 'icon_uuid', 'style', 'target_blank',
        'sort', 'is_active', 'widget_instance_id',
    ];
    protected $casts = [
        'target_blank' => 'boolean',
        'is_active' => 'boolean',
        'sort' => 'integer',
    ];

    public function menu(): BelongsTo
    {
        return $this->belongsTo(Menu::class);
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id')->orderBy('sort');
    }

    public function widgetInstance(): BelongsTo
    {
        return $this->belongsTo(WidgetInstance::class, 'widget_instance_id');
    }
}
