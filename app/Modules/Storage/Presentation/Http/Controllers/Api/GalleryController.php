<?php

namespace App\Modules\Storage\Presentation\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Modules\Shared\Domain\Entities\UserPermission;
use App\Modules\Storage\Application\Actions\Gallery\CreateGalleryUseCase;
use App\Modules\Storage\Application\Actions\Gallery\DeleteGalleryUseCase;
use App\Modules\Storage\Application\Actions\Gallery\IndexGalleriesUseCase;
use App\Modules\Storage\Application\Actions\Gallery\ListGalleryImagesUseCase;
use App\Modules\Storage\Application\Actions\Gallery\ListGalleryTagsUseCase;
use App\Modules\Storage\Application\Actions\Gallery\UpdateGalleryUseCase;
use App\Modules\Storage\Application\Actions\Gallery\ViewGalleryUseCase;
use App\Modules\Storage\Application\DTOs\Gallery\GalleryData;
use App\Modules\Storage\Application\DTOs\Gallery\GalleryImageData;
use App\Modules\Storage\Application\DTOs\Gallery\GalleryViewData;
use App\Modules\Storage\Application\DTOs\Media\MediaViewData;
use App\Modules\Storage\Application\DTOs\Tag\MediaTagIndexData;
use App\Modules\Storage\Application\DTOs\Tag\MediaTagViewData;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response;

class GalleryController extends Controller
{
    public function __construct(
        private readonly IndexGalleriesUseCase    $indexUseCase,
        private readonly ViewGalleryUseCase       $viewUseCase,
        private readonly CreateGalleryUseCase     $createUseCase,
        private readonly UpdateGalleryUseCase     $updateUseCase,
        private readonly DeleteGalleryUseCase     $deleteUseCase,
        private readonly ListGalleryImagesUseCase $listGalleryImagesUseCase,
        private readonly ListGalleryTagsUseCase   $listGalleryTagsUseCase,
    ) {}

    public function index(UserPermission $permissions): JsonResponse
    {
        $galleries = $this->indexUseCase->execute($permissions);
        return response()->json(GalleryViewData::collect($galleries));
    }

    public function store(Request $request, UserPermission $permissions): JsonResponse
    {
        try {
            $dto = GalleryData::validateAndCreate($request);
        } catch (ValidationException $e) {
            return response()->json(['errors' => $e->errors()], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $gallery = $this->createUseCase->execute($dto, $permissions);
        return response()->json(GalleryViewData::fromEntity($gallery), Response::HTTP_CREATED);
    }

    public function show(int $id, UserPermission $permissions): JsonResponse
    {
        $gallery = $this->viewUseCase->execute($id, $permissions);
        return response()->json(GalleryViewData::fromEntity($gallery));
    }

    public function update(Request $request, int $id, UserPermission $permissions): JsonResponse
    {
        try {
            $dto = GalleryData::validateAndCreate($request);
        } catch (ValidationException $e) {
            return response()->json(['errors' => $e->errors()], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $gallery = $this->updateUseCase->execute($id, $dto, $permissions);
        return response()->json(GalleryViewData::fromEntity($gallery));
    }

    public function destroy(int $id, UserPermission $permissions): JsonResponse
    {
        $this->deleteUseCase->execute($id, $permissions);
        return response()->json(null, Response::HTTP_NO_CONTENT);
    }

    public function media(int $id, Request $request, UserPermission $permissions): JsonResponse
    {
        $filters = [
            'type' => $request->input('type'),
            'tag'  => $request->input('tag'),
        ];

        $images = $this->listGalleryImagesUseCase->execute($id, $filters, $permissions);
        return response()->json(GalleryImageData::collect($images));
    }

    public function tags(int $id, UserPermission $permissions): JsonResponse
    {
        $tags = $this->listGalleryTagsUseCase->execute($id, $permissions);
        return response()->json(MediaTagIndexData::collect($tags));
    }
}
