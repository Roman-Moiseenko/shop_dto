<?php

namespace App\Modules\Auth\Application\Actions\Role;
use App\Modules\Auth\Domain\Services\RoleRepositoryInterface;
use App\Modules\Shared\Domain\Entities\UserPermission;
use App\Modules\Shared\Domain\Exceptions\AccessDeniedException;
use InvalidArgumentException;

readonly class DeleteCustomRoleUseCase
{
    public function __construct(
        private RoleRepositoryInterface $roleRepository
    ) {}

    public function execute(int $roleId, UserPermission $permissions): void
    {
        if (!$permissions->can('auth.settings.delete')) throw new AccessDeniedException();
        $role = $this->roleRepository->findById($roleId);

        if (!$role) {
            throw new InvalidArgumentException('Роль не найдена');
        }

        if ($role->is_system) {
            throw new InvalidArgumentException('Нельзя удалить системную роль');
        }

        $this->roleRepository->delete($roleId);
    }
}
