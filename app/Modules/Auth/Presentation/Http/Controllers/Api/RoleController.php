<?php

namespace App\Modules\Auth\Presentation\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Modules\Auth\Application\Actions\Role\CreateCustomRoleUseCase;
use App\Modules\Auth\Application\Actions\Role\DeleteCustomRoleUseCase;
use App\Modules\Auth\Application\Actions\Role\IndexCustomRoleUseCase;
use App\Modules\Auth\Application\Actions\Role\UpdateCustomRoleUseCase;
use App\Modules\Auth\Application\Actions\Role\ViewCustomRoleUseCase;
use App\Modules\Auth\Application\DTOs\Role\RoleCreateData;
use App\Modules\Auth\Application\DTOs\Role\RoleUpdateData;
use App\Modules\Auth\Application\DTOs\Role\RoleViewData;
use App\Modules\Auth\Domain\Services\PermissionProviderInterface;
use App\Modules\Shared\Domain\Entities\UserPermission;
use Illuminate\Validation\ValidationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleController extends Controller
{
    public function __construct(
        private readonly CreateCustomRoleUseCase $createRole,
        private readonly UpdateCustomRoleUseCase $updateRole,
        private readonly DeleteCustomRoleUseCase $deleteRole,
        private readonly PermissionProviderInterface $permissionProvider,
        private readonly ViewCustomRoleUseCase  $viewCustomRoleUseCase,
        private readonly IndexCustomRoleUseCase $indexCustomRoleUseCase
    ) {}

    // Список всех ролей (можно добавить фильтр по is_system через параметр запроса)
    public function index(Request $request, UserPermission $userPermission): JsonResponse
    {
        $is_system = $request->has('type') && $request->type == 'system';
        $roles = $this->indexCustomRoleUseCase->execute($is_system, $userPermission);
        return response()->json(RoleViewData::collect($roles), Response::HTTP_CREATED);
    }

    public function show(int $id, UserPermission $userPermission): JsonResponse
    {
        $role = $this->viewCustomRoleUseCase->execute($id, $userPermission);
        return response()->json(RoleViewData::fromEntity($role), Response::HTTP_CREATED);
    }

    public function store(Request $request, UserPermission $userPermission): JsonResponse
    {
        \Log::warning(json_encode($request->all()));
        try {
            $dto = RoleCreateData::validateAndCreate($request);
        } catch (ValidationException $e) {
            return response()->json(['errors' => $e->errors()], Response::HTTP_UNPROCESSABLE_ENTITY);
        }
        $role = $this->createRole->execute($dto, $userPermission);
        return response()->json(RoleViewData::fromEntity($role), Response::HTTP_CREATED);
    }

    public function update(int $id, Request $request, UserPermission $userPermission): JsonResponse
    {

        try {
            $dto = RoleUpdateData::validateAndCreate($request);
        } catch (ValidationException $e) {
            return response()->json(['errors' => $e->errors()], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $updatedRole = $this->updateRole->execute($id, $dto, $userPermission);
        return response()->json(RoleViewData::fromEntity($updatedRole), Response::HTTP_CREATED);
    }

    public function destroy(int $id, UserPermission $userPermission): JsonResponse
    {
        $this->deleteRole->execute($id, $userPermission);
        return response()->json(null, 204);
    }

    // Получение сгруппированных разрешений (по системным ролям)
    public function permissions(UserPermission $userPermission): JsonResponse
    {
        return response()->json(
            $this->permissionProvider->groupedBySystemRoles()
        );
    }
}
