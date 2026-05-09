<?php

namespace App\Modules\Content\Presentation\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Modules\Content\Application\Actions\Pages\CreatePageUseCase;
use App\Modules\Content\Application\Actions\Pages\DeletePageUseCase;
use App\Modules\Content\Application\Actions\Pages\ForceDeletePageUseCase;
use App\Modules\Content\Application\Actions\Pages\IndexPageUseCase;
use App\Modules\Content\Application\Actions\Pages\RestorePageUseCase;
use App\Modules\Content\Application\Actions\Pages\UpdatePageUseCase;
use App\Modules\Content\Application\Actions\Pages\ViewPageUseCase;
use App\Modules\Content\Application\DTOs\PageCreateData;
use App\Modules\Content\Application\DTOs\PageUpdateData;
use App\Modules\Shared\Domain\Entities\UserPermission;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response;

class PageController extends Controller
{
    public function __construct(
        private readonly CreatePageUseCase      $createUseCase,
        private readonly UpdatePageUseCase      $updateUseCase,
        private readonly DeletePageUseCase      $deleteUseCase,
        private readonly IndexPageUseCase       $indexUseCase,
        private readonly ViewPageUseCase        $viewUseCase,
        private readonly ForceDeletePageUseCase $forceDeleteUseCase,
        private readonly RestorePageUseCase $restoreUseCase,
    )
    {
    }

    public function index(UserPermission $permissions): JsonResponse
    {
        $pages = $this->indexUseCase->execute($permissions);
        return response()->json($pages);
    }

    public function store(Request $request, UserPermission $permissions): JsonResponse
    {
        try {
            $dto = PageCreateData::validateAndCreate($request->all());
        } catch (ValidationException $e) {
            return response()->json(['errors' => $e->errors()], Response::HTTP_UNPROCESSABLE_ENTITY);
        }
        $page = $this->createUseCase->execute($dto, $permissions);
        return response()->json($page, 201);
    }

    public function show(int $id, UserPermission $permissions): JsonResponse
    {
        $page = $this->viewUseCase->execute($id, $permissions);
        return response()->json($page);
    }

    public function update(int $id, Request $request, UserPermission $permissions): JsonResponse
    {
        try {
            $dto = PageUpdateData::validateAndCreate($request->all());
        } catch (ValidationException $e) {
            return response()->json(['errors' => $e->errors()], Response::HTTP_UNPROCESSABLE_ENTITY);
        }
        $page = $this->updateUseCase->execute($id, $dto, $permissions);
        return response()->json($page);
    }

    public function destroy(int $id, UserPermission $permissions): JsonResponse
    {
        $this->deleteUseCase->execute($id, $permissions);
        return response()->json(null, 204);
    }

    public function forceDestroy(int $id, UserPermission $permissions): JsonResponse
    {
        $this->forceDeleteUseCase->execute($id, $permissions);
        return response()->json(null, 204);
    }

    /**
     * Восстановить мягко удалённую страницу.
     */
    public function restore(int $id, UserPermission $permissions): JsonResponse
    {
        $this->restoreUseCase->execute($id, $permissions);
        return response()->json(['message' => 'Страница восстановлена']);
    }
}
