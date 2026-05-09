<?php

namespace App\Modules\Storage\Presentation\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Modules\Shared\Domain\Entities\UserPermission;
use App\Modules\Storage\Application\Actions\ClientDeleteMediaUseCase;
use App\Modules\Storage\Application\Actions\ClientListMediaUseCase;
use App\Modules\Storage\Application\Actions\ClientUploadMediaUseCase;
use App\Modules\Storage\Application\DTOs\IndexMediaData;
use App\Modules\Storage\Application\DTOs\UploadMediaData;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response;

class ClientMediaController extends Controller
{
    public function __construct(
        private readonly ClientUploadMediaUseCase $clientUploadUseCase,
        private readonly ClientDeleteMediaUseCase $clientDeleteUseCase,
        private readonly ClientListMediaUseCase   $clientListMediaUseCase,
    )
    {
    }

    public function store(Request $request, UserPermission $permissions)
    {
        try {
            $dto = UploadMediaData::validateAndCreate($request->all());
        } catch (ValidationException $e) {
            return response()->json(['errors' => $e->errors()], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        if ($request->hasFile('file')) {
            $dto->file = $request->file('file');
        } else {
            return response()->json(['message' => 'File is required'], 422);
        }

        $media = $this->clientUploadUseCase->execute($dto, $permissions);
        return response()->json($media->toArray(), 201);
    }

    public function destroy(string $uuid, UserPermission $permissions)
    {
        $this->clientDeleteUseCase->execute($uuid, $permissions);
        return response()->json(null, 204);
    }

    public function index(Request $request, UserPermission $permissions)
    {
        try {
            $dto = IndexMediaData::validateAndCreate($request->all());
        } catch (ValidationException $e) {
            return response()->json(['errors' => $e->errors()], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $mediaList = $this->clientListMediaUseCase->execute($dto->model_type, $dto->model_id, $permissions);

        return response()->json($mediaList);
    }
}
