<?php

namespace App\Modules\Content\Infrastructure\Models;

use DateTime;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property int $id
 * @property string $title
 * @property string $slug
 * @property string $content
 * @property string $status
 * @property string $content_type
 * @property string $template
 * @property array meta
 * @property int $author_id
 *
 * @property ?DateTime $published_at
 * @property ?DateTime $created_at
 * @property ?DateTime $updated_at
 * @property ?DateTime $deleted_at
 */
class Page extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'title', 'slug', 'content', 'status', 'content_type', 'template', 'meta', 'author_id', 'published_at'
    ];
    protected $casts = [
        'meta' => 'array',
        'published_at' => 'datetime',
    ];
    /*
    public function author()
    {
        return $this->belongsTo(\Modules\Auth\Infrastructure\Models\Staff::class, 'author_id');
    }
    */
    public function blocks(): MorphMany
    {
        return $this->morphMany(ContentBlock::class, 'container', 'container_type', 'container_id');
    }
}
