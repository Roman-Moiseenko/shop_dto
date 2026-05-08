<?php

namespace App\Modules\Storage\Application\Services;

class StorageConfig
{
    /**
     * @param string $originalDisk   диск для загруженных оригиналов
     * @param string $cacheDisk      диск для публичного кэша
     * @param string $uploadPath     путь к оригиналу относительно корня диска
     * @param string $cachePath      путь к кэшу относительно корня диска
     * @param array  $cacheSettings  настройки кэша (max_width, max_height, watermark)
     * @param array  $watermarkSettings глобальные настройки водяного знака (path, disk, ratio, offset, position)
     */
    public function __construct(
        public readonly string $originalDisk,
        public readonly string $cacheDisk,
        public readonly string $uploadPath,
        public readonly string $cachePath,
        public readonly array  $thumbs,
        public readonly array  $cacheSettings = [],
        public readonly array  $watermarkSettings = [],
    ) {}
}
