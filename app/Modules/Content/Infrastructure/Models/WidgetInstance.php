<?php

namespace App\Modules\Content\Infrastructure\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

/**
 * @property string $uuid
 * @property int $widget_id
 * @property array $params
 * @property string $title
 *
 */
class WidgetInstance extends Model
{
    protected $fillable = ['uuid', 'widget_id', 'params', 'title'];
    protected $casts = [
        'params' => 'array',
    ];

    protected static function booted(): void
    {
        static::creating(function (WidgetInstance $instance) {
            $instance->uuid = (string)Str::uuid();
        });
    }

    public function widget(): BelongsTo
    {
        return $this->belongsTo(Widget::class);
    }

    public function contentBlocks()
    {
        return $this->hasMany(ContentBlock::class);
    }
}

