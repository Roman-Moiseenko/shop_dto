<?php

namespace App\Modules\Storage\Presentation\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Modules\Shared\Domain\Entities\UserPermission;
use App\Modules\Storage\Application\Actions\Media\ClearCacheUseCase;
use App\Modules\Storage\Application\Actions\Media\DeleteMediaUseCase;
use App\Modules\Storage\Application\Actions\Media\DownloadMediaUseCase;
use App\Modules\Storage\Application\Actions\Media\FileMediaUseCase;
use App\Modules\Storage\Application\Actions\Media\PublicListMediaUseCase;
use App\Modules\Storage\Application\Actions\Media\UpdateMediaUseCase;
use App\Modules\Storage\Application\Actions\Media\UploadMediaUseCase;
use App\Modules\Storage\Application\Actions\Media\ViewMediaUseCase;
use App\Modules\Storage\Application\Actions\SyncMediaTagsUseCase;
use App\Modules\Storage\Application\DTOs\Media\DownloadMediaData;
use App\Modules\Storage\Application\DTOs\Media\IndexMediaData;
use App\Modules\Storage\Application\DTOs\Media\MediaViewData;
use App\Modules\Storage\Application\DTOs\Media\UpdateMediaData;
use App\Modules\Storage\Application\DTOs\Media\UploadMediaData;
use App\Modules\Storage\Application\DTOs\SyncMediaTagsData;
use App\Modules\Storage\Application\Services\MediaFileService;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response;

class MediaController extends Controller
{
    public function __construct(
        private readonly UploadMediaUseCase     $uploadUseCase,
        private readonly DownloadMediaUseCase   $downloadUseCase,
        private readonly ViewMediaUseCase       $viewUseCase,
        private readonly UpdateMediaUseCase     $updateUseCase,
        private readonly DeleteMediaUseCase     $deleteUseCase,
        private readonly FileMediaUseCase       $fileMediaUseCase,
        private readonly MediaFileService       $mediaFileService,
        private readonly ClearCacheUseCase      $clearCacheUseCase,
        private readonly PublicListMediaUseCase $publicListMediaUseCase,
        private readonly SyncMediaTagsUseCase   $syncMediaTagsUseCase,
    )
    {
    }
    public function publicIndex(Request $request)
    {
        try {
            $dto = IndexMediaData::validateAndCreate($request);
        } catch (ValidationException $e) {
            return response()->json(['errors' => $e->errors()], Response::HTTP_UNPROCESSABLE_ENTITY);
        }
        $mediaList = $this->publicListMediaUseCase->execute($dto->model_type, $dto->model_id, $dto->type);

        return response()->json($mediaList);
    }

    // Загрузка одного или нескольких файлов
    public function show(string $uuid, Request $request)
    {
        $media = $this->viewUseCase->execute($uuid);
        $thumb = $request->input('thumb');

        $this->mediaFileService->ensureCacheExists($media);

        if ($thumb) {
            if (!$this->mediaFileService->thumbExists($media, $thumb)) {
                return response()->json(['message' => 'Thumbnail not found'], 404);
            }
            $url = $this->mediaFileService->getThumbUrl($media, $thumb);
        } else {
            $url = $this->mediaFileService->getCacheFullUrl($media);
        }

        return response()->json(['url' => $url]);
    }

    public function upload(Request $request, UserPermission $permissions)
    {
        // Создаём DTO из запроса, валидация произойдёт автоматически
        $dto = UploadMediaData::validateAndCreate($request->all());
        // Добавляем файл вручную, так как Spatie Data не парсит UploadedFile
        if ($request->hasFile('file')) {
            $dto->file = $request->file('file');
        } else {
            return response()->json(['message' => 'File is required'], 422);
        }

        try {
            $media = $this->uploadUseCase->execute($dto, $permissions);
            return response()->json(MediaViewData::fromEntity($media), Response::HTTP_CREATED);
        } catch (\Throwable $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }

    }

    public function download(Request $request, UserPermission $permissions)
    {
        try {
            $dto = DownloadMediaData::validateAndCreate($request);
        } catch (ValidationException $e) {
            return response()->json(['errors' => $e->errors()], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $media = $this->downloadUseCase->execute($dto, $permissions);
        return response()->json(MediaViewData::fromEntity($media), Response::HTTP_CREATED);
    }


    public function update(int $id, Request $request, UserPermission $permissions)
    {
        try {
            $dto = UpdateMediaData::validateAndCreate($request);
        } catch (ValidationException $e) {
            return response()->json(['errors' => $e->errors()], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $media = $this->updateUseCase->execute($id, $dto, $permissions);
        return response()->json(MediaViewData::fromEntity($media), Response::HTTP_CREATED);
    }

    public function destroy(int $id, UserPermission $permissions)
    {
        $this->deleteUseCase->execute($id, $permissions);
        return response()->json(null, 204);
    }

    /**
     * Оригинальный файл
     */
    public function file(string $uuid, UserPermission $permissions)
    {
        $file = $this->fileMediaUseCase->execute($uuid, $permissions);
        return response()->file($file);
    }

    public function clearCache(Request $request, UserPermission $permissions)
    {
        $modelType = $request->input('model_type');
        $modelId = $request->input('model_id');

        $count = $this->clearCacheUseCase->execute($permissions, $modelType, $modelId);

        return response()->json([
            'message' => "Кэш успешно очищен. Обработано записей: {$count}",
            'count' => $count,
        ]);
    }

    public function syncTags(Request $request,int $id,  UserPermission $permissions)
    {
        try {
            $dto = SyncMediaTagsData::validateAndCreate($request->all());
        } catch (ValidationException $e) {
            return response()->json(['errors' => $e->errors()], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $this->syncMediaTagsUseCase->execute($id, $dto, $permissions);

        return response()->json(['message' => 'Теги обновлены']);

    }
}
