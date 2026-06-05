<?php

namespace App\Modules\Shared\Infrastructure\Persistence;

use App\Modules\Auth\Application\Interfaces\UserRepositoryInterface;
use App\Modules\Shared\Application\Interfaces\UserPermissionRepositoryInterface;
use App\Modules\Shared\Domain\Entities\UserPermission;
use App\Modules\Shared\Domain\Exceptions\AccessDeniedException;
use Illuminate\Http\Request;

readonly class UserPermissionRepositoryFromAuth implements UserPermissionRepositoryInterface
{

    public function __construct(private UserRepositoryInterface $userRepository)
    {
    }

    public function getUserPermission(Request $request): UserPermission
    {
        if (is_null($request->user())) return new UserPermission(null, [], []);
        $user = $this->userRepository->findById($request->user()->id);
        if (is_null($user)) throw new AccessDeniedException();
        return new UserPermission($user->id, $user->roles, $user->permissions);
    }
}
