<?php

namespace App\Modules\Auth\Application\Actions\Role;

use App\Modules\Auth\Application\DTOs\Role\RoleUpdateData;
use App\Modules\Auth\Domain\Services\RoleRepositoryInterface;
use App\Modules\Shared\Domain\Entities\UserPermission;
use App\Modules\Shared\Domain\Exceptions\AccessDeniedException;
use InvalidArgumentException;
use Spatie\Permission\Models\Role;

readonly class UpdateCustomRoleUseCase
{
    public function __construct(
        private RoleRepositoryInterface $roleRepository
    ) {}

    public function execute(int $roleId, RoleUpdateData $dto, UserPermission $permissions): Role
    {
        if (!$permissions->can('auth.settings.edit')) throw new AccessDeniedException();

        $role = $this->roleRepository->findById($roleId);

        if (!$role) {
            throw new InvalidArgumentException('Роль не найдена');
        }

        if ($role->is_system) {
            throw new InvalidArgumentException('Нельзя редактировать системную роль');
        }

        // Обновляем имя и описание через репозиторий
        $updatedRole = $this->roleRepository->update($roleId, [
            'name' => $dto->name,
            'description' => $dto->description,
        ]);

        // Синхронизация разрешений
        if (!empty($dto->permissions)) {
            $updatedRole->syncPermissions($dto->permissions);
        } else {
            $updatedRole->syncPermissions([]);
        }

        return $updatedRole;
    }
}
