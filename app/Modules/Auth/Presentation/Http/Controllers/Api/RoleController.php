<?php

namespace App\Modules\Auth\Presentation\Http\Controllers\Api;
use App\Http\Controllers\Controller;
use App\Modules\Auth\Application\Actions\User\AssignRoleToUserUseCase;
use App\Modules\Auth\Application\Interfaces\UserRepositoryInterface;
use App\Modules\Auth\Domain\ValueObjects\RoleName;
use App\Modules\Auth\Infrastructure\Models\User;
use App\Modules\Shared\Application\Interfaces\UserPermissionRepositoryInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use JetBrains\PhpStorm\Deprecated;
use Spatie\Permission\Models\Role;

#[Deprecated]
class RoleController extends Controller
{
    public function __construct(
        private AssignRoleToUserUseCase $assignRoleUseCase,
        private UserPermissionRepositoryInterface $userPermissionRepository,
        private UserRepositoryInterface $userRepository,
    ) {}

    public function index(): JsonResponse
    {
        $roles = Role::all()->map(fn($role) => [
            'name' => $role->name,
            'permissions' => $role->permissions->pluck('name'),
        ]);
        return response()->json($roles);
    }

    public function assign(Request $request): JsonResponse
    {
        $request->validate([
            'user_id' => 'required|integer|exists:users,id',
            'role' => 'required|string|in:' . implode(',', [RoleName::ADMIN, RoleName::CLIENT]),
        ]);

        $this->assignRoleUseCase->execute(
            $request->user_id,
            $request->role,
            $this->userPermissionRepository->getUserPermission($request));

        return response()->json(['message' => 'Роль назначена']);
    }

    public function revoke(Request $request): JsonResponse
    {
        $request->validate([
            'user_id' => 'required|integer|exists:users,id',
            'role' => 'required|string',
        ]);

        $user = User::find($request->user_id);
        $user->removeRole($request->role);

        return response()->json(['message' => 'Роль снята']);
    }
}
