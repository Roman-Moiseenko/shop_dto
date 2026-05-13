<?php

namespace App\Modules\Content\Presentation\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Modules\Content\Application\Actions\Menu\ActivateMenuItemUseCase;
use App\Modules\Content\Application\Actions\Menu\ChangeMenuItemParentUseCase;
use App\Modules\Content\Application\Actions\Menu\CreateMenuItemUseCase;
use App\Modules\Content\Application\Actions\Menu\DeactivateMenuItemUseCase;
use App\Modules\Content\Application\Actions\Menu\DeleteMenuItemUseCase;
use App\Modules\Content\Application\Actions\Menu\IndexMenuItemsUseCase;
use App\Modules\Content\Application\Actions\Menu\ReorderMenuItemUseCase;
use App\Modules\Content\Application\Actions\Menu\UpdateMenuItemUseCase;
use App\Modules\Content\Application\Actions\Menu\ViewMenuItemUseCase;
use App\Modules\Content\Application\DTOs\Menu\ChangeMenuItemParentData;
use App\Modules\Content\Application\DTOs\Menu\MenuItemCreateData;
use App\Modules\Content\Application\DTOs\Menu\MenuItemTreeData;
use App\Modules\Content\Application\DTOs\Menu\MenuItemUpdateData;
use App\Modules\Content\Application\DTOs\Menu\MenuItemListData;
use App\Modules\Content\Application\DTOs\Menu\MenuItemViewData;
use App\Modules\Content\Application\DTOs\Menu\ReorderMenuItemData;
use App\Modules\Shared\Domain\Entities\UserPermission;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response;
class MenuItemController extends Controller
{
    public function __construct(
        private readonly IndexMenuItemsUseCase        $indexUseCase,
        private readonly ViewMenuItemUseCase          $viewUseCase,
        private readonly CreateMenuItemUseCase        $createUseCase,
        private readonly UpdateMenuItemUseCase        $updateUseCase,
        private readonly DeleteMenuItemUseCase        $deleteUseCase,
        private readonly ChangeMenuItemParentUseCase  $changeParentUseCase,
        private readonly ReorderMenuItemUseCase       $reorderUseCase,
        private readonly ActivateMenuItemUseCase   $activateUseCase,
        private readonly DeactivateMenuItemUseCase $deactivateUseCase,
    ) {}

    public function index(int $menuId, UserPermission $permissions): JsonResponse
    {
        $items = $this->indexUseCase->execute($menuId, $permissions);
        return response()->json(MenuItemTreeData::collect($items));
    }

    public function store(int $menuId, Request $request, UserPermission $permissions): JsonResponse
    {
        try {
            $dto = MenuItemCreateData::validateAndCreate($request);
        } catch (ValidationException $e) {
            return response()->json(['errors' => $e->errors()], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $item = $this->createUseCase->execute($menuId, $dto, $permissions);
        return response()->json(MenuItemViewData::fromEntity($item), Response::HTTP_CREATED);
    }

    public function show(int $menuId, int $itemId, UserPermission $permissions): JsonResponse
    {
        $item = $this->viewUseCase->execute($itemId, $permissions);
        return response()->json(MenuItemViewData::fromEntity($item));
    }

    public function update(int $menuId, int $itemId, Request $request, UserPermission $permissions): JsonResponse
    {
        try {
            $dto = MenuItemUpdateData::validateAndCreate($request);
        } catch (ValidationException $e) {
            return response()->json(['errors' => $e->errors()], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $item = $this->updateUseCase->execute($menuId, $itemId, $dto, $permissions);
        return response()->json(MenuItemViewData::fromEntity($item));
    }

    public function destroy(int $menuId, int $itemId, UserPermission $permissions): JsonResponse
    {
        $this->deleteUseCase->execute($itemId, $permissions);
        return response()->json(null, Response::HTTP_NO_CONTENT);
    }

    public function changeParent(int $menuId, int $itemId, Request $request, UserPermission $permissions): JsonResponse
    {
        try {
            $dto = ChangeMenuItemParentData::validateAndCreate($request);
        } catch (ValidationException $e) {
            return response()->json(['errors' => $e->errors()], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $this->changeParentUseCase->execute($itemId, $dto, $permissions);
        return response()->json(['message' => 'Родитель изменён']);
    }

    public function reorder(int $menuId, Request $request, UserPermission $permissions): JsonResponse
    {
        try {
            $dto = ReorderMenuItemData::validateAndCreate($request);
        } catch (ValidationException $e) {
            return response()->json(['errors' => $e->errors()], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $this->reorderUseCase->execute($dto, $permissions);
        return response()->json(['message' => 'Сортировка обновлена']);
    }

    /**
     * Активировать пункт меню.
     */
    public function activate(int $menuId, int $itemId, UserPermission $permissions): JsonResponse
    {
        $this->activateUseCase->execute($itemId, $permissions);
        return response()->json(['message' => 'Пункт меню активирован']);
    }

    /**
     * Деактивировать пункт меню.
     */
    public function deactivate(int $menuId, int $itemId, UserPermission $permissions): JsonResponse
    {
        $this->deactivateUseCase->execute($itemId, $permissions);
        return response()->json(['message' => 'Пункт меню деактивирован']);
    }
}
