<?php

namespace App\Modules\Storage\Presentation\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Modules\Storage\Infrastructure\Models\Media;
use Illuminate\Http\Request;

class MediaController extends Controller
{
    public function __construct(private ImageProcessor $imageProcessor) {}

    // Загрузка одного или нескольких файлов
    public function upload(Request $request)
    {
        $request->validate([
            'files' => 'required|array',
            'files.*' => 'file|max:10240',
            'model_type' => 'required|string|max:255',
            'model_id' => 'required|integer',
            'type' => 'required|string|max:255',        // image, icon, gallery
            'titles' => 'nullable|array',
            'descriptions' => 'nullable|array',
        ]);

        $uploaded = [];
        $disk = config('storage.local.disk', 'public');

        foreach ($request->file('files') as $index => $file) {
            $fileName = \Illuminate\Support\Str::uuid() . '.' . $file->getClientOriginalExtension();
            $modelType = $request->model_type;
            $modelId = $request->model_id;
            $type = $request->type;

            $basePath = config('storage.local.upload_path', 'uploads') . '/' . $modelType . '/' . $modelId . '/';
            $file->storeAs($basePath, $fileName, $disk);

            $media = Media::create([
                'model_type' => $modelType,
                'model_id' => $modelId,
                'type' => $type,
                'title' => $request->input("titles.{$index}"),
                'description' => $request->input("descriptions.{$index}"),
                'file_name' => $fileName,
                'mime_type' => $file->getMimeType(),
                'disk' => $disk,
                'size' => $file->getSize(),
            ]);

            // Генерация нарезок согласно конфигу
            $this->imageProcessor->process($media);

            $uploaded[] = [
                'id' => $media->id,
                'uuid' => $media->uuid,
                'url' => $media->getUrl(),
            ];
        }

        return response()->json(['data' => $uploaded], 201);
    }

    // Получение изображений
    public function index(Request $request)
    {
        $request->validate([
            'model_type' => 'required|string',
            'model_id' => 'required|integer',
            'type' => 'nullable|string',
        ]);

        $query = Media::where('model_type', $request->model_type)
            ->where('model_id', $request->model_id);

        if ($request->type) {
            $query->where('type', $request->type);
        }

        $media = $query->orderBy('sort')->get();

        return response()->json($media->map(fn($m) => [
            'id' => $m->id,
            'uuid' => $m->uuid,
            'type' => $m->type,
            'title' => $m->title,
            'description' => $m->description,
            'url' => $m->getUrl(),
            'thumbnails' => $this->getThumbnailUrls($m),
        ]));
    }

    public function show(string $uuid, Request $request)
    {
        $media = Media::where('uuid', $uuid)->firstOrFail();
        $thumb = $request->get('thumb');
        return response()->json([
            'id' => $media->id,
            'uuid' => $media->uuid,
            'title' => $media->title,
            'url' => $thumb ? $media->getUrl($thumb) : $media->getUrl(),
        ]);
    }

    public function destroy(string $uuid)
    {
        $media = Media::where('uuid', $uuid)->firstOrFail();
        // Удаляем файлы и нарезки
        Storage::disk($media->disk)->deleteDirectory(dirname($media->getPath()));
        $media->delete();

        return response()->json(null, 204);
    }

    private function getThumbnailUrls(Media $media): array
    {
        $config = config("storage.thumbs.{$media->model_type}.{$media->model_type}", []);
        $thumbnails = [];
        foreach ($config as $slug => $settings) {
            $thumbnails[$slug] = $media->getUrl($slug);
        }
        return $thumbnails;
    }
}
