<?php

namespace App\Modules\Storage\Infrastructure\Services;

use App\Modules\Storage\Application\Interfaces\FileStorageInterface;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class LaravelFileStorage implements FileStorageInterface
{
    public function storeUploadedFile(UploadedFile $file, string $path, string $filename, string $disk): string
    {
        // storeAs возвращает путь относительно корня диска: 'путь/имя.ext'
        return $file->storeAs($path, $filename, ['disk' => $disk]);
    }

    public function deleteDirectory(string $path, string $disk): void
    {
        Storage::disk($disk)->deleteDirectory($path);
    }

    public function put(string $path, string $content, ?string $disk = null): void
    {
        Storage::disk($disk)->put($path, $content);
    }
    public function get(string $path, string $disk): string
    {
        return Storage::disk($disk)->get($path);
    }
    public function exists(string $path, string $disk): bool
    {
        return Storage::disk($disk)->exists($path);
    }

    public function fullPath(string $path, string $disk): string
    {
        return Storage::disk($disk)->path($path);
    }
    public function delete(string $path, string $disk): void
    {
        Storage::disk($disk)->delete($path);
    }

    public function getUrl(string $path, string $disk): string
    {
        $fullUrl = Storage::disk($disk)->url($path);
        return parse_url($fullUrl, PHP_URL_PATH);
    }
}
