<?php

namespace App\Modules\Storage\Application\Services;

use App\Modules\Storage\Infrastructure\Models\Media;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\ImageManager;
class ImageProcessor
{
    public function process(Media $media): void
    {
        $config = config("storage.thumbs.{$media->model_type}.{$media->type}", []);
        if (empty($config)) return;

        $image = ImageManager::imagick()->read($media->getPath());
        foreach ($config as $slug => $settings) {
            $thumb = $image->cover($settings['width'], $settings['height']);
            $thumbPath = $media->getPath($slug);
            Storage::disk($media->disk)->put($thumbPath, $thumb->toJpeg());
        }
    }

    //TODO Добавить нарезку по настройкам как Photo::class
}
