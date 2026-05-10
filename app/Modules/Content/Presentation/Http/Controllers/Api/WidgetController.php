<?php

namespace App\Modules\Content\Presentation\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Modules\Content\Application\Actions\Widgets\CreateWidgetUseCase;
use App\Modules\Content\Application\Actions\Widgets\DeleteWidgetUseCase;
use App\Modules\Content\Application\Actions\Widgets\IndexWidgetUseCase;
use App\Modules\Content\Application\Actions\Widgets\UpdateWidgetUseCase;
use App\Modules\Content\Application\Actions\Widgets\ViewWidgetUseCase;
use App\Modules\Content\Application\DTOs\WidgetIndexData;
use App\Modules\Content\Application\DTOs\WidgetOptionData;
use App\Modules\Content\Application\DTOs\WidgetUpdateData;
use App\Modules\Content\Application\DTOs\WidgetViewData;
use App\Modules\Shared\Domain\Entities\UserPermission;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response;

class WidgetController extends Controller
{
    public function __construct(
        private readonly CreateWidgetUseCase $createUseCase,
        private readonly UpdateWidgetUseCase $updateUseCase,
        private readonly DeleteWidgetUseCase $deleteUseCase,
        private readonly IndexWidgetUseCase  $indexUseCase,
        private readonly ViewWidgetUseCase   $viewUseCase,
    )
    {
    }

    public function index(UserPermission $permissions): JsonResponse
    {
        $widgets = $this->indexUseCase->execute($permissions);
        return response()->json(WidgetIndexData::collect($widgets), Response::HTTP_CREATED);
    }

    public function store(Request $request, UserPermission $permissions): JsonResponse
    {

        try {
            $dto = WidgetUpdateData::validateAndCreate($request);
        } catch (ValidationException $e) {
            return response()->json(['errors' => $e->errors()], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $widget = $this->createUseCase->execute($dto, $permissions);
        return response()->json(WidgetViewData::fromEntity($widget), Response::HTTP_CREATED);
    }

    public function show(int $id, UserPermission $permissions): JsonResponse
    {
        $widget = $this->viewUseCase->execute($id, $permissions);
        return response()->json(WidgetViewData::fromEntity($widget));
    }

    public function update(int $id, Request $request, UserPermission $permissions): JsonResponse
    {
        try {
            $dto = WidgetUpdateData::validateAndCreate($request);
        } catch (ValidationException $e) {
            return response()->json(['errors' => $e->errors()], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $widget = $this->updateUseCase->execute($id, $dto, $permissions);
        return response()->json(WidgetViewData::fromEntity($widget));
    }

    public function destroy(int $id, UserPermission $permissions): JsonResponse
    {
        $this->deleteUseCase->execute($id, $permissions);
        return response()->json(null, Response::HTTP_NO_CONTENT);
    }

    /**
     * Список для select
     */
    public function options(UserPermission $permissions): JsonResponse
    {
        $widgets = $this->indexUseCase->execute($permissions);
        return response()->json(WidgetOptionData::collect($widgets), Response::HTTP_CREATED);
    }
}
