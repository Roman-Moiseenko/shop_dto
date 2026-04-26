<?php

namespace App\Modules\Auth\Application\Actions\User;

use App\Modules\Auth\Application\Interfaces\UserRepositoryInterface;
use App\Modules\Auth\Domain\ValueObjects\RoleName;
use App\Modules\Auth\Infrastructure\Models\User as EloquentUser;
use App\Modules\Shared\Domain\Entities\UserPermission;
use Symfony\Component\HttpFoundation\File\Exception\AccessDeniedException;

class AssignRoleToUserUseCase
{
    public function __construct(private UserRepositoryInterface $userRepository) {}

    public function execute(int $userId, string $roleName, ?UserPermission $currentUser = null): void
    {

        if ($currentUser != null && !$currentUser->hasRole('admin')) {
            throw new AccessDeniedException('Недостаточно прав для редактирования роли');
        }

        $role = new RoleName($roleName);
        $eloquentUser = EloquentUser::find($userId);
        if (!$eloquentUser) {
            throw new \InvalidArgumentException('Пользователь не найден');
        }

        $eloquentUser->assignRole($role->getValue());
    }
}
