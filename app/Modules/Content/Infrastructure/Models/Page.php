<?php

namespace App\Modules\Content\Infrastructure\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;

/**
 * @property string $title
 * @property string $slug
 * @property string $content
 * @property string $status
 * @property string $content_type
 * @property string $template
 * @property array meta
 * @property int $author_id
 * @property ?\DateTimeImmutable $published_at
 */
class Page extends Model
{
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
