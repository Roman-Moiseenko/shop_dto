<?php

namespace App\Modules\Storage\Presentation\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Modules\Shared\Domain\Entities\UserPermission;
use App\Modules\Storage\Application\Actions\DeleteMediaUseCase;
use App\Modules\Storage\Application\Actions\DownloadMediaUseCase;
use App\Modules\Storage\Application\Actions\IndexMediaUseCase;
use App\Modules\Storage\Application\Actions\UpdateMediaUseCase;
use App\Modules\Storage\Application\Actions\UploadMediaUseCase;
use App\Modules\Storage\Application\Actions\ViewMediaUseCase;
use App\Modules\Storage\Application\DTOs\DownloadMediaData;
use App\Modules\Storage\Application\DTOs\IndexMediaData;
use App\Modules\Storage\Application\DTOs\UpdateMediaData;
use App\Modules\Storage\Application\DTOs\UploadMediaData;
use App\Modules\Storage\Infrastructure\Models\Media;
use Illuminate\Http\Request;

class MediaController extends Controller
{
    public function __construct(
        private readonly UploadMediaUseCase   $uploadUseCase,
        private readonly DownloadMediaUseCase $downloadUseCase,
        private readonly IndexMediaUseCase    $indexUseCase,
        private readonly ViewMediaUseCase     $viewUseCase,
        private readonly UpdateMediaUseCase   $updateUseCase,
        private readonly DeleteMediaUseCase   $deleteUseCase,
    )
    {
    }

    // Загрузка одного или нескольких файлов
    public function show(string $uuid, Request $request)
    {
        $media = $this->viewUseCase->execute($uuid);
        $thumb = $request->input('thumb');
        return response()->json([
            'id' => $media->uuid,
            'url' => $thumb ? $media->getUrl($thumb) : $media->getUrl(),
        ]);
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

        $media = $this->uploadUseCase->execute($dto, $permissions);
        return response()->json($media->toArray(), 201);
    }

    public function download(DownloadMediaData $dto, UserPermission $permissions)
    {
        $media = $this->downloadUseCase->execute($dto, $permissions);
        return response()->json($media->toArray(), 201);
    }

    public function index(IndexMediaData $dto, UserPermission $permissions)
    {
        $mediaList = $this->indexUseCase->execute($dto, $permissions);
        return response()->json($mediaList);
    }

    public function update(int $id, UpdateMediaData $dto, UserPermission $permissions)
    {
        $media = $this->updateUseCase->execute($id, $dto, $permissions);
        return response()->json($media->toArray());
    }

    public function destroy(int $id, UserPermission $permissions)
    {
        $this->deleteUseCase->execute($id, $permissions);
        return response()->json(null, 204);
    }
}
