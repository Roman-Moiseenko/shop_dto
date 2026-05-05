<?php

namespace App\Modules\Auth\Application\Actions\Role;
use App\Modules\Auth\Application\DTOs\Role\RoleCreateData;
use App\Modules\Auth\Domain\Services\RoleRepositoryInterface;
use App\Modules\Shared\Domain\Entities\UserPermission;
use App\Modules\Shared\Domain\Services\TransactionManagerInterface;
use App\Modules\Shared\Infrastructure\Exceptions\AccessDeniedException;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\DB;
readonly class CreateCustomRoleUseCase
{
    public function __construct(
        private RoleRepositoryInterface     $roleRepository,
        private TransactionManagerInterface $transactionManager
    ) {}

    public function execute(RoleCreateData $dto, UserPermission $permissions): Role
    {
        if (!$permissions->can('auth.settings.create')) throw new AccessDeniedException();
        return $this->transactionManager->execute(function () use ($dto) {
            $role = $this->roleRepository->create([
                'name' => $dto->name,
                'guard_name' => 'api',
                'is_system' => false,
                'description' => $dto->description,
            ]);

            if (!empty($dto->permissions)) {
                $role->syncPermissions($dto->permissions);
            }

            return $role;
        });
    }
}
