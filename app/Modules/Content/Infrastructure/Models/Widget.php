<?php

namespace App\Modules\Content\Infrastructure\Models;
use Illuminate\Database\Eloquent\Model;

/**
 * @property string $name
 * @property string $slug
 * @property string $description
 * @property string $category
 * @property array $schema
 */
class Widget extends Model
{
    protected $fillable = ['name', 'slug', 'description', 'category', 'schema'];
    protected $casts = [
        'schema' => 'array',
    ];
    public function instances()
    {
        return $this->hasMany(WidgetInstance::class);
    }
}
