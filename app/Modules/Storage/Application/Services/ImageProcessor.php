<?php

namespace App\Modules\Storage\Application\Services;


use App\Modules\Storage\Application\Interfaces\FileStorageInterface;
use App\Modules\Storage\Domain\Entities\MediaEntity;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Alignment;
use Intervention\Image\Drivers\Gd\Driver as GdDriver;
use Intervention\Image\Drivers\Imagick\Driver as ImagickDriver;

//use Intervention\Image\Drivers\Imagick\Drive;
use Intervention\Image\Exceptions\AnalyzerException;
use Intervention\Image\Exceptions\DriverException;
use Intervention\Image\Exceptions\ImageDecoderException;
use Intervention\Image\Exceptions\InvalidArgumentException;
use Intervention\Image\Exceptions\ModifierException;
use Intervention\Image\Image;
use Intervention\Image\ImageManager;

class ImageProcessor
{
    public function __construct(
        private readonly FileStorageInterface $fileStorage,
    )
    {
    }


    /**
     * @throws InvalidArgumentException
     * @throws ImageDecoderException
     * @throws DriverException
     */
    public function makeImage(string $content): Image
    {
        $driver = class_exists(\Imagick::class) ? new ImagickDriver() : new GdDriver();
        $manager = new ImageManager($driver);
        return $manager->decode($content);
    }

    /**
     * @throws ImageDecoderException
     * @throws InvalidArgumentException
     * @throws DriverException
     * @throws ModifierException
     */
    public function applyWatermark($image, $settings): Image
    {
        // реализация как была в applyWatermark, но без чтения файла (файл водяного знака должен быть доступен)
        $wmGlobal = config('storage.watermark');
        $params = is_array($settings) ? array_merge($wmGlobal, $settings) : $wmGlobal;
        if (empty($params['path'])) return $image;

        $disk = $params['disk'] ?? 'public';
        $wmContent = $this->fileStorage->get($params['path'], $disk);
        if (!$wmContent) return $image;


        $watermark = $this->makeImage($wmContent);
        $watermark->resize(
            (int)($image->width() * $params['ratio']),
            (int)($image->width() * $params['ratio'])
        );
        $image->insert(
            $watermark,
            $params['offset'],
            $params['offset'],
            $params['position'],
        );
        return $image;
    }

    /**
     * @throws DriverException
     * @throws AnalyzerException
     * @throws ImageDecoderException
     * @throws InvalidArgumentException
     * @throws ModifierException
     */
    public function processThumbnail($image, array $settings): Image
    {
        //$image = $this->makeImage($originalContent);
        $fit = $settings['fit'] ?? true;
        if ($fit) {
            return $image->cover($settings['width'], $settings['height']);
        } else {
            $scale_w = $image->width() / $settings['width'];
            $scale_h = $image->height() / $settings['height'];
            $scale = max($scale_w, $scale_h);
            $thumb = $image->cover((int)($image->width() / $scale), (int)($image->height() / $scale));
            return $thumb->resize($settings['width'], $settings['height']);
        }
        if ($watermark) {

        }
    }

}
