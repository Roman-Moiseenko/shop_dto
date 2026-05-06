<?php

namespace App\Modules\Storage\Presentation\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Modules\Shared\Domain\Entities\UserPermission;
use App\Modules\Storage\Application\Actions\ClientDeleteMediaUseCase;
use App\Modules\Storage\Application\Actions\ClientUploadMediaUseCase;
use App\Modules\Storage\Application\DTOs\UploadMediaData;
use Illuminate\Http\Request;

class ClientMediaController extends Controller
{
    public function __construct(
        private readonly ClientUploadMediaUseCase $clientUploadUseCase,
        private readonly ClientDeleteMediaUseCase $clientDeleteUseCase,
    ) {}

    public function store(Request $request, UserPermission $permissions)
    {
        $dto = UploadMediaData::validateAndCreate($request->all());
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
}
