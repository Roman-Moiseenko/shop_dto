<?php

namespace App\Modules\Content\Infrastructure\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * @property string $container_type
 * @property int $container_id
 * @property int $widget_instance_id
 * @property int $sort_order
 * @property string $section
 *
 */
class ContentBlock extends Model
{
    protected $fillable = ['container_type', 'container_id', 'widget_instance_id', 'sort_order', 'section'];
    public function widgetInstance(): BelongsTo
    {
        return $this->belongsTo(WidgetInstance::class);
    }
    public function container(): MorphTo
    {
        return $this->morphTo('container', 'container_type', 'container_id');
    }
}
