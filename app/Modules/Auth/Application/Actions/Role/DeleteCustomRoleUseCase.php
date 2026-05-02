<?php

namespace App\Modules\Auth\Application\Actions\Role;
use App\Modules\Auth\Domain\Services\RoleRepositoryInterface;
use InvalidArgumentException;
class DeleteCustomRoleUseCase
{
    public function __construct(
        private readonly RoleRepositoryInterface $roleRepository
    ) {}

    public function execute(int $roleId): void
    {
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
