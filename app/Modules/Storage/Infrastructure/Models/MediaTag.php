<?php

namespace App\Modules\Storage\Infrastructure\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * @property int $id
 * @property string $name
 * @property string $slug
 * @property Media[] $medias
 */
class MediaTag extends Model
{
    protected $table = 'media_tags';
    protected $fillable = ['name', 'slug'];

    public function medias(): BelongsToMany
    {
        return $this->belongsToMany(
            Media::class,
            'media_has_tags',
            'media_tag_id',
            'media_id'
        )->withTimestamps();
    }
}
