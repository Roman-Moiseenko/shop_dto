<?php

namespace App\Modules\Shared\Infrastructure\Persistence;

use App\Modules\Shared\Application\Interfaces\UserPermissionRepositoryInterface;
use App\Modules\Shared\Domain\Entities\UserPermission;
use Illuminate\Http\Request;

class UserPermissionRepositoryFromHeader implements UserPermissionRepositoryInterface
{
    public function getUserPermission(Request $request): UserPermission
    {
        //Получаем из заголовков
        $userId = (int)$request->header('X-User-Id') ?? null; //протестить null

        $userRoles = explode(',', $request->header('X-User-Roles', ''));
        $userPermissions = explode(',', $request->header('X-User-Permissions', ''));
        return new UserPermission($userId, $userRoles, $userPermissions);
    }
}
