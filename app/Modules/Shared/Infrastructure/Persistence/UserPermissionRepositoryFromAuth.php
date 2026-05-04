<?php

namespace App\Modules\Shared\Infrastructure\Persistence;

use App\Modules\Auth\Application\Interfaces\UserRepositoryInterface;
use App\Modules\Shared\Application\Interfaces\UserPermissionRepositoryInterface;
use App\Modules\Shared\Domain\Entities\UserPermission;
use Illuminate\Http\Request;

readonly class UserPermissionRepositoryFromAuth implements UserPermissionRepositoryInterface
{

    public function __construct(private UserRepositoryInterface $userRepository)
    {
    }

    public function getUserPermission(Request $request): UserPermission
    {
        $user = $this->userRepository->findById($request->user()->id);
        if (!$user) { return new UserPermission(null, [], []); }
    //    \Log::info(json_encode($user->roles));
    //    \Log::info(json_encode($user->permissions));
        return new UserPermission($user->id, $user->roles, $user->permissions);
    }
}
