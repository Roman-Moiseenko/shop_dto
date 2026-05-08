<?php

namespace App\Modules\Storage\Application\Services;

use App\Modules\Storage\Application\Interfaces\FileStorageInterface;
use App\Modules\Storage\Domain\Entities\MediaEntity;
use Illuminate\Http\UploadedFile;
use Intervention\Image\Exceptions\AnalyzerException;
use Intervention\Image\Exceptions\EncoderException;

readonly class MediaFileService
{
    public function __construct(
        private FileStorageInterface $fileStorage,
        private readonly StorageConfig $config,
        private readonly ImageProcessor $imageProcessor,
    ) {}

    /**
     * Сохранить загруженный файл на диск оригиналов.
     */
    public function storeOriginal(UploadedFile $file, string $modelType, int $modelId, string $filename): void
    {
        $dirname = $this->config->uploadPath . '/' . $modelType . '/' . $modelId;
        $this->fileStorage->storeUploadedFile(
            $file,
            $dirname . '/',
            $filename,
            $this->config->originalDisk
        );
    }
    public function getCacheFullUrl(MediaEntity $media): string
    {
        return $this->fileStorage->getUrl($this->getCacheFullPath($media), $this->config->cacheDisk);
    }

    public function getThumbUrl(MediaEntity $media, string $slug): string
    {
        return $this->fileStorage->getUrl($this->getThumbPath($media, $slug), $this->config->cacheDisk);
    }
    /**
     * Полный путь к файлу оригинала на диске.
     */
    public function getOriginalPath(MediaEntity $media): string
    {
        return $this->config->uploadPath . '/' . $media->modelType . '/' . $media->modelId . '/' . $media->fileName;
    }

    /**
     * Получить содержимое оригинального файла.
     */
    public function getOriginalContent(MediaEntity $media): string
    {
        return $this->fileStorage->get($this->getOriginalPath($media), $this->config->originalDisk);
    }

    /**
     * Базовый путь к кэшу для конкретной сущности.
     */
    private function cacheBasePath(MediaEntity $media): string
    {
        return $this->config->cachePath . '/' . $media->modelType . '/' . $media->modelId;
    }

    /**
     * Путь к основному (полноразмерному) кэш-файлу.
     */
    public function getCacheFullPath(MediaEntity $media): string
    {
        return $this->cacheBasePath($media) . '/' . $media->fileName;
    }

    /**
     * Путь к конкретной нарезке (thumbnail).
     */
    public function getThumbPath(MediaEntity $media, string $slug): string
    {
        $ext = pathinfo($media->fileName, PATHINFO_EXTENSION);
        $thumbFilename = $media->id . '_' . $slug . '.' . $ext;

        return $this->cacheBasePath($media) . '/' . $slug . '/' . $thumbFilename;
    }

    /**
     * Проверить существование основного кэш-файла.
     */
    public function cacheFullExists(MediaEntity $media): bool
    {
        return $this->fileStorage->exists($this->getCacheFullPath($media), $this->config->cacheDisk);
    }
    public function getThumbsConfig(MediaEntity $media): array
    {
        return $this->config->thumbs[$media->modelType][$media->type->getValue()] ?? [];
    }
    /**
     * Проверить существование нарезки.
     */
    public function thumbExists(MediaEntity $media, string $slug): bool
    {
        return $this->fileStorage->exists($this->getThumbPath($media, $slug), $this->config->cacheDisk);
    }

    /**
     * Получить содержимое основного кэш-файла (оригинала).
     */
    public function getCacheFullContent(MediaEntity $media): string
    {
        return $this->fileStorage->get($this->getCacheFullPath($media), $this->config->cacheDisk);
    }

    /**
     * Получить содержимое нарезки.
     */
    public function getThumbContent(MediaEntity $media, string $slug): string
    {
        return $this->fileStorage->get($this->getThumbPath($media, $slug), $this->config->cacheDisk);
    }

    /**
     * Сгенерировать кэш (основной файл + нарезки) на основе оригинала.
     * @throws AnalyzerException
     * @throws EncoderException
     */
    public function generateCache(MediaEntity $media): void
    {
        $originalContent = $this->getOriginalContent($media);
        if (empty($originalContent)) {
            throw new \RuntimeException('Оригинал файла не найден');
        }

        // Основной кэш
        $image = $this->imageProcessor->makeImage($originalContent);

        $maxWidth = $this->config->cacheSettings['max_width'] ?? null;
        $maxHeight = $this->config->cacheSettings['max_height'] ?? null;
        $applyWatermark = $this->config->cacheSettings['watermark'] ?? false;

        if ($maxWidth || $maxHeight) {
            $image = $image->cover(
                $maxWidth ?? $image->width(),
                $maxHeight ?? $image->height()
            );
        }

        if ($applyWatermark) {
            $image = $this->imageProcessor->applyWatermark(
                $image,
                $this->config->watermarkSettings,
            );
        }

        $this->fileStorage->put(
            $this->getCacheFullPath($media),
            (string) $image->encode(),
            $this->config->cacheDisk
        );

        // Нарезки
        $entityThumbs = $this->config->thumbs[$media->modelType][$media->type->getValue()] ?? [];
        foreach ($entityThumbs as $slug => $settings) {
            $thumbImage = $this->imageProcessor->makeImage($originalContent); //Создаем image нов.экземпляр

            $thumb = $this->imageProcessor->processThumbnail($thumbImage, $settings); //Обрезаем
            if (isset($settings['watermark']) && $settings['watermark'])  //Водяной знак
                $thumb = $this->imageProcessor->applyWatermark($thumb, $this->config->watermarkSettings);

            $this->fileStorage->put(
                $this->getThumbPath($media, $slug),
                (string) $thumb->encode(),
                $this->config->cacheDisk
            );
        }
    }

    /**
     * Запустить generateCache только если кэш отсутствует.
     * @throws AnalyzerException
     */
    public function ensureCacheExists(MediaEntity $media): void
    {
        if (!$this->cacheFullExists($media)) {
            $this->generateCache($media);
        }
    }

    /**
     * Удалить оригинал и все кэшированные файлы (основной + нарезки).
     */
    public function deleteAllFiles(MediaEntity $media): void
    {
        \Log::warning(json_encode($media));
        // Оригинал
        $originalPath = $this->getOriginalPath($media);
        if ($this->fileStorage->exists($originalPath, $this->config->originalDisk)) {
            $this->fileStorage->delete($originalPath, $this->config->originalDisk);
        }

        // Основной кэш
        $cacheFullPath = $this->getCacheFullPath($media);
        if ($this->fileStorage->exists($cacheFullPath, $this->config->cacheDisk)) {
            $this->fileStorage->delete($cacheFullPath, $this->config->cacheDisk);
        }

        // Нарезки
        $this->deleteAllThumbnails($media);
    }

    public function getOriginalDisk(): string
    {
        return $this->config->originalDisk;
    }

    private function deleteAllThumbnails(MediaEntity $media): void
    {
        $entityThumbs = $this->config->thumbs[$media->modelType][$media->type->getValue()] ?? [];
        foreach ($entityThumbs as $slug => $settings) {
            $thumbPath = $this->getThumbPath($media, $slug);
            if ($this->fileStorage->exists($thumbPath, $this->config->cacheDisk)) {
                $this->fileStorage->delete($thumbPath, $this->config->cacheDisk);
            }
        }
    }
    /**
     * Сохранить бинарное содержимое как оригинал на защищённом диске.
     */
    public function storeOriginalFromContent(string $content, string $modelType, int $modelId, string $filename): void
    {
        $dirname = $this->config->uploadPath . '/' . $modelType . '/' . $modelId;
        $path = $dirname . '/' . $filename;
        $this->fileStorage->put($path, $content, $this->config->originalDisk);
    }

    /**
     * Удалить только кэш (основной + нарезки), оригинал остаётся.
     */
    public function deleteCache(MediaEntity $media): void
    {
        // Основной кэш
        $cacheFullPath = $this->getCacheFullPath($media);
        if ($this->fileStorage->exists($cacheFullPath, $this->config->cacheDisk)) {
            $this->fileStorage->delete($cacheFullPath, $this->config->cacheDisk);
        }

        // Нарезки
        $this->deleteAllThumbnails($media);
    }
}
