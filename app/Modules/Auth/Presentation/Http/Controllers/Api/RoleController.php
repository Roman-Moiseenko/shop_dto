<?php

namespace App\Modules\Auth\Presentation\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Modules\Auth\Application\Actions\Role\CreateCustomRoleUseCase;
use App\Modules\Auth\Application\Actions\Role\DeleteCustomRoleUseCase;
use App\Modules\Auth\Application\Actions\Role\UpdateCustomRoleUseCase;
use App\Modules\Auth\Application\DTOs\Role\RoleCreateData;
use App\Modules\Auth\Domain\ValueObjects\RoleName;
use App\Modules\Auth\Presentation\Http\Resources\RoleResource;
use Spatie\Permission\Models\Role;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RoleController extends Controller
{
    public function __construct(
        private readonly CreateCustomRoleUseCase $createRole,
        private readonly UpdateCustomRoleUseCase $updateRole,
        private readonly DeleteCustomRoleUseCase $deleteRole
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

    public function store(RoleCreateData $dto): JsonResponse
    {
        $role = $this->createRole->execute($dto);
        return new RoleResource($role)->response()->setStatusCode(201);
    }

    public function update(int $id, RoleCreateData $dto): JsonResponse
    {
        // При обновлении нужно исключить текущую роль из проверки уникальности имени
        // Проще всего сделать это через валидацию в контроллере, а не в DTO
        // Но мы можем изменить DTO или использовать custom request
        // Для простоты проверим здесь
        $role = Role::findOrFail($id);
        if ($role->is_system) {
            return response()->json(['message' => 'Системную роль нельзя редактировать'], 403);
        }

        $updatedRole = $this->updateRole->execute($id, $dto);
        return new RoleResource($updatedRole)->response();
    }

    public function destroy(int $id): JsonResponse
    {
        $role = Role::findOrFail($id);
        if ($role->is_system) {
            return response()->json(['message' => 'Системную роль нельзя удалить'], 403);
        }

        $this->deleteRole->execute($id);
        return response()->json(null, 204);
    }

    // Получение сгруппированных разрешений (по системным ролям)
    public function permissions(): JsonResponse
    {
        // Загружаем системные роли с их разрешениями
        $systemRoles = Role::where('is_system', true)
            ->with('permissions')
            ->get();

        $grouped = $systemRoles->map(function ($role) {
            return [
                'role' => $role->name,
                'permissions' => $role->permissions->pluck('name')->toArray(),
            ];
        });

        return response()->json($grouped);
    }
}
