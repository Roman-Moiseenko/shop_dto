<?php

namespace App\Modules\Content\Infrastructure\Models;

use DateTime;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string $type
 * @property string $value
 * @property string $link
 * @property string $icon_uuid
 * @property string $caption
 * @property string $analytics_field
 * @property int $sort
 * @property bool $is_active
 * @property DateTime $created_at
 * @property DateTime $updated_at
 */
class Contact extends Model
{
    protected $table = 'contacts';

    protected $fillable = [
        'type',
        'value',
        'link',
        'icon_uuid',
        'caption',
        'analytics_field',
        'sort',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];
}
