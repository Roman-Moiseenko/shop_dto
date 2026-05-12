<?php

namespace App\Modules\Storage\Infrastructure\Models;

use DateTime;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;

/**
 * @property int $id
 * @property string $name
 * @property string $slug
 * @property string $description
 * @property bool $is_active
 * @property DateTime $created_at
 * @property DateTime $updated_at
 */
class Gallery extends Model
{
    protected $table = 'galleries';
    protected $fillable = ['name', 'slug', 'description', 'is_active'];
    protected $casts = ['is_active' => 'boolean'];

    public function media(): MorphMany
    {
        return $this->morphMany(Media::class, 'model', 'model_type', 'model_id');
        // Полиморфная связь через столбцы model_type и model_id в таблице media
    }
}
