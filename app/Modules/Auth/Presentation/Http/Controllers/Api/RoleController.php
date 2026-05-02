<?php

namespace App\Modules\Auth\Presentation\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Modules\Auth\Application\Actions\Role\CreateCustomRoleUseCase;
use App\Modules\Auth\Application\Actions\Role\DeleteCustomRoleUseCase;
use App\Modules\Auth\Application\Actions\Role\UpdateCustomRoleUseCase;
use App\Modules\Auth\Application\DTOs\Role\RoleCreateData;
use App\Modules\Auth\Application\DTOs\Role\RoleUpdateData;
use App\Modules\Auth\Domain\Services\PermissionProviderInterface;
use App\Modules\Auth\Domain\ValueObjects\RoleName;
use App\Modules\Auth\Presentation\Http\Resources\RoleResource;
use Illuminate\Validation\ValidationException;
use Spatie\Permission\Models\Role;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleController extends Controller
{
    public function __construct(
        private readonly CreateCustomRoleUseCase $createRole,
        private readonly UpdateCustomRoleUseCase $updateRole,
        private readonly DeleteCustomRoleUseCase $deleteRole,
        private readonly PermissionProviderInterface $permissionProvider
    ) {}

    // Список всех ролей (можно добавить фильтр по is_system через параметр запроса)
    public function index(Request $request): JsonResponse
    {
        $query = Role::with('permissions');
        if ($request->has('type')) {
            $query->where('is_system', $request->type === 'system')
                ->whereNotIn('name', RoleName::BASE);
        }
        return RoleResource::collection($query->get())->response();
    }

    public function show(int $id): JsonResponse
    {
        $role = Role::with('permissions')->findOrFail($id);
        return new RoleResource($role)->response();
    }

    public function store(Request $request): JsonResponse
    {
        \Log::warning(json_encode($request->all()));
        try {
            $dto = RoleCreateData::validateAndCreate($request);
        } catch (ValidationException $e) {
            return response()->json(['errors' => $e->errors()], Response::HTTP_UNPROCESSABLE_ENTITY);
        }
        $role = $this->createRole->execute($dto);
        return new RoleResource($role)->response()->setStatusCode(201);
    }

    public function update(int $id, Request $request): JsonResponse
    {

        try {
            $dto = RoleUpdateData::validateAndCreate($request);
        } catch (ValidationException $e) {
            return response()->json(['errors' => $e->errors()], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $updatedRole = $this->updateRole->execute($id, $dto);
        return new RoleResource($updatedRole)->response();
    }

    public function destroy(int $id): JsonResponse
    {
        $this->deleteRole->execute($id);
        return response()->json(null, 204);
    }

    // Получение сгруппированных разрешений (по системным ролям)
    public function permissions(): JsonResponse
    {
        return response()->json(
            $this->permissionProvider->groupedBySystemRoles()
        );
    }
}
