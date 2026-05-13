<?php

namespace App\Modules\Content\Presentation\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Modules\Content\Application\Actions\Menu\CreateMenuUseCase;
use App\Modules\Content\Application\Actions\Menu\DeleteMenuUseCase;
use App\Modules\Content\Application\Actions\Menu\IndexMenusUseCase;
use App\Modules\Content\Application\Actions\Menu\UpdateMenuUseCase;
use App\Modules\Content\Application\Actions\Menu\ViewMenuUseCase;
use App\Modules\Content\Application\DTOs\Menu\MenuData;
use App\Modules\Content\Application\DTOs\Menu\MenuIndexData;
use App\Modules\Content\Application\DTOs\Menu\MenuViewData;
use App\Modules\Shared\Domain\Entities\UserPermission;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response;

class MenuController extends Controller
{
    public function __construct(
        private readonly IndexMenusUseCase       $indexUseCase,
        private readonly ViewMenuUseCase         $viewUseCase,
        private readonly CreateMenuUseCase       $createUseCase,
        private readonly UpdateMenuUseCase       $updateUseCase,
        private readonly DeleteMenuUseCase       $deleteUseCase,
    ) {}

    public function index(UserPermission $permissions): JsonResponse
    {
        $menus = $this->indexUseCase->execute($permissions);
        return response()->json(MenuIndexData::collect($menus));
    }

    public function store(Request $request, UserPermission $permissions): JsonResponse
    {
        try {
            $dto = MenuData::validateAndCreate($request);
        } catch (ValidationException $e) {
            return response()->json(['errors' => $e->errors()], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $menu = $this->createUseCase->execute($dto, $permissions);
        return response()->json(MenuViewData::fromEntity($menu), Response::HTTP_CREATED);
    }

    public function show(int $id, UserPermission $permissions): JsonResponse
    {
        $menu = $this->viewUseCase->execute($id, $permissions);
        return response()->json(MenuViewData::fromEntity($menu));
    }

    public function update(Request $request, int $id, UserPermission $permissions): JsonResponse
    {
        try {
            $dto = MenuData::validateAndCreate($request);
        } catch (ValidationException $e) {
            return response()->json(['errors' => $e->errors()], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $menu = $this->updateUseCase->execute($id, $dto, $permissions);
        return response()->json(MenuViewData::fromEntity($menu));
    }

    public function destroy(int $id, UserPermission $permissions): JsonResponse
    {
        $this->deleteUseCase->execute($id, $permissions);
        return response()->json(null, Response::HTTP_NO_CONTENT);
    }
}
