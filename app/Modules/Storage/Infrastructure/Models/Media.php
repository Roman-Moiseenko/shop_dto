<?php

namespace App\Modules\Storage\Infrastructure\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

/**
 * @property int $id
 * @property string $uuid
 * @property string $model_type
 * @property int $model_id
 * @property string $type
 * @property string $title
 * @property string $description
 * @property int $sort
 * @property string $file_name
 * @property string $mime_type
 * @property string $disk
 * @property int $size
 * @property array $custom_properties
 *
 */
class Media extends Model
{
    protected $table = 'media';

    protected $fillable = [
        'uuid', 'model_type', 'model_id', 'type', 'title',
        'description', 'sort', 'file_name', 'mime_type',
        'disk', 'size', 'custom_properties',
    ];

    protected $casts = [
        'custom_properties' => 'array',
    ];

    protected static function booted(): void
    {
        static::creating(function (Media $media) {
            $media->uuid = (string) Str::uuid();
        });
    }

    public function getUrl(string $conversion = ''): string
    {
        $filesystem = app('filesystem')->disk($this->disk);
        $path = $this->getPath($conversion);
        return $filesystem->url($path);
    }

    public function getTemporaryUrl(\DateTimeInterface $expiration, string $conversion = ''): string
    {
        $filesystem = app('filesystem')->disk($this->disk);
        return $filesystem->temporaryUrl($this->getPath($conversion), $expiration);
    }

    public function getPath(string $conversion = ''): string
    {
        $base = 'uploads/' . $this->model_type . '/' . $this->model_id . '/';
        if ($conversion) {
            $base .= 'cache/' . $conversion . '/';
        }
        // Для нарезок имя файла можно формировать как {media_id}_{slug}.{ext}
        $filename = $conversion ? $this->id . '_' . $conversion . '.' . pathinfo($this->file_name, PATHINFO_EXTENSION) : $this->file_name;
        return $base . $filename;
    }
}
