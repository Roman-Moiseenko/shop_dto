<?php

namespace App\Modules\Storage\Presentation\Http\Controllers\Api;


use App\Http\Controllers\Controller;
use App\Modules\Shared\Domain\Entities\UserPermission;
use App\Modules\Storage\Application\Actions\Tag\CreateMediaTagUseCase;
use App\Modules\Storage\Application\Actions\Tag\DeleteMediaTagUseCase;
use App\Modules\Storage\Application\Actions\Tag\UpdateMediaTagUseCase;
use App\Modules\Storage\Application\Actions\Tag\ViewMediaTagUseCase;
use App\Modules\Storage\Application\DTOs\Tag\MediaTagData;
use App\Modules\Storage\Application\DTOs\Tag\MediaTagViewData;
use App\Modules\Storage\Application\Actions\Tag\IndexMediaTagsUseCase;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response;

class MediaTagController extends Controller
{
    public function __construct(
        private readonly IndexMediaTagsUseCase  $indexUseCase,
        private readonly ViewMediaTagUseCase    $viewUseCase,
        private readonly CreateMediaTagUseCase  $createUseCase,
        private readonly UpdateMediaTagUseCase  $updateUseCase,
        private readonly DeleteMediaTagUseCase  $deleteUseCase,
    ) {}

    public function index(UserPermission $permissions): JsonResponse
    {
        $tags = $this->indexUseCase->execute($permissions);
        return response()->json(MediaTagViewData::collect($tags));
    }

    public function store(Request $request, UserPermission $permissions): JsonResponse
    {
        try {
            $dto = MediaTagData::validateAndCreate($request);
        } catch (ValidationException $e) {
            return response()->json(['errors' => $e->errors()], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $tag = $this->createUseCase->execute($dto, $permissions);
        return response()->json(MediaTagViewData::fromEntity($tag), Response::HTTP_CREATED);
    }

    public function show(int $id, UserPermission $permissions): JsonResponse
    {
        $tag = $this->viewUseCase->execute($id, $permissions);
        return response()->json(MediaTagViewData::fromEntity($tag));
    }

    public function update(Request $request, int $id, UserPermission $permissions): JsonResponse
    {
        try {
            $dto = MediaTagData::validateAndCreate($request);
        } catch (ValidationException $e) {
            return response()->json(['errors' => $e->errors()], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $tag = $this->updateUseCase->execute($id, $dto, $permissions);
        return response()->json(MediaTagViewData::fromEntity($tag));
    }

    public function destroy(int $id, UserPermission $permissions): JsonResponse
    {
        $this->deleteUseCase->execute($id, $permissions);
        return response()->json(null, Response::HTTP_NO_CONTENT);
    }
}
