<?php

namespace App\Modules\Auth\Application\Actions\Role;

use App\Modules\Auth\Application\DTOs\Role\RoleCreateData;
use App\Modules\Auth\Domain\Services\RoleRepositoryInterface;
use InvalidArgumentException;
use Spatie\Permission\Models\Role;
class UpdateCustomRoleUseCase
{
    public function __construct(
        private readonly RoleRepositoryInterface $roleRepository
    ) {}

    public function execute(int $roleId, RoleCreateData $dto): Role
    {
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
            // Если разрешения не переданы, можно сбросить все (опционально)
            // $updatedRole->syncPermissions([]);
        }

        return $updatedRole;
    }
}
