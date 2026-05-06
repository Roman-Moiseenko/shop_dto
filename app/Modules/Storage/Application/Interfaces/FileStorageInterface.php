<?php

namespace App\Modules\Storage\Application\Interfaces;

use Illuminate\Http\UploadedFile;

interface FileStorageInterface
{
    /**
     * Сохраняет загруженный файл и возвращает путь относительно корня диска.
     */
    public function storeUploadedFile(UploadedFile $file, string $path, string $filename, string $disk): string;

    /**
     * Удаляет директорию и всё её содержимое на указанном диске.
     */
    public function deleteDirectory(string $path, string $disk): void;
    public function put(string $path, string $content, ?string $disk = null): void;
    public function exists(string $path, string $disk): bool;
    public function fullPath(string $path, string $disk): string;
}
