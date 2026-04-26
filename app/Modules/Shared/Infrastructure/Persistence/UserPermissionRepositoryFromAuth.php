<?php

namespace App\Modules\Shared\Infrastructure\Persistence;

use App\Modules\Auth\Application\Interfaces\UserRepositoryInterface;
use App\Modules\Shared\Application\Interfaces\UserPermissionRepositoryInterface;
use App\Modules\Shared\Domain\Entities\UserPermission;
use Illuminate\Http\Request;

class UserPermissionRepositoryFromAuth implements UserPermissionRepositoryInterface
{

    public function __construct(private readonly UserRepositoryInterface $userRepository)
    {
    }

    public function getUserPermission(Request $request): UserPermission
    {
        $user = $this->userRepository->findById($request->user()->id);
        return new UserPermission($user->id, $user->roles, $user->permissions);
    }
}
