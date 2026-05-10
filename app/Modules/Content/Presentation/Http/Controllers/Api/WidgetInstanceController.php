<?php

namespace App\Modules\Content\Presentation\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Modules\Content\Application\Actions\Widgets\CreateWidgetInstanceUseCase;
use App\Modules\Content\Application\Actions\Widgets\DeleteWidgetInstanceUseCase;
use App\Modules\Content\Application\Actions\Widgets\IndexWidgetInstanceUseCase;
use App\Modules\Content\Application\Actions\Widgets\UpdateWidgetInstanceUseCase;
use App\Modules\Content\Application\Actions\Widgets\ViewWidgetInstanceUseCase;
use App\Modules\Content\Application\DTOs\WidgetInstanceData;
use App\Modules\Content\Application\DTOs\WidgetInstanceIndexData;
use App\Modules\Content\Application\DTOs\WidgetInstanceViewData;
use App\Modules\Shared\Domain\Entities\UserPermission;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response;

class WidgetInstanceController extends Controller
{
    public function __construct(
        private readonly IndexWidgetInstanceUseCase  $indexUseCase,
        private readonly ViewWidgetInstanceUseCase   $viewUseCase,
        private readonly CreateWidgetInstanceUseCase $createUseCase,
        private readonly UpdateWidgetInstanceUseCase $updateUseCase,
        private readonly DeleteWidgetInstanceUseCase $deleteUseCase,
    ) {}

    public function index(Request $request, UserPermission $permissions): JsonResponse
    {
        $widgetId = $request->query('widget_id');
        $instances = $this->indexUseCase->execute($widgetId, $permissions);
        return response()->json(WidgetInstanceIndexData::collect($instances), RESPONSE::HTTP_OK);
    }

    public function store(Request $request, UserPermission $permissions): JsonResponse
    {
        try {
            $dto = WidgetInstanceData::validateAndCreate($request->all());
        } catch (ValidationException $e) {
            return response()->json(['errors' => $e->errors()], Response::HTTP_UNPROCESSABLE_ENTITY);
        }
        $instance = $this->createUseCase->execute($dto, $permissions);
        return response()->json(WidgetInstanceViewData::fromEntity($instance), Response::HTTP_CREATED);
    }

    public function show(int $id, UserPermission $permissions): JsonResponse
    {
        $instance = $this->viewUseCase->execute($id, $permissions);
        return response()->json(WidgetInstanceViewData::fromEntity($instance), Response::HTTP_OK);
    }

    public function update(int $id, Request $request, UserPermission $permissions): JsonResponse
    {
        try {
            $dto = WidgetInstanceData::validateAndCreate($request->all());
        } catch (ValidationException $e) {
            return response()->json(['errors' => $e->errors()], Response::HTTP_UNPROCESSABLE_ENTITY);
        }
        $instance = $this->updateUseCase->execute($id, $dto, $permissions);
        return response()->json(WidgetInstanceViewData::fromEntity($instance), Response::HTTP_CREATED);
    }

    public function destroy(int $id, UserPermission $permissions): JsonResponse
    {
        $this->deleteUseCase->execute($id, $permissions);
        return response()->json(null, Response::HTTP_NO_CONTENT);
    }
}
