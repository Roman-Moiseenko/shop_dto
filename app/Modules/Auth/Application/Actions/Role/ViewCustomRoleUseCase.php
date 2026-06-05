<?php

namespace App\Modules\Auth\Application\Actions\Role;

use App\Modules\Shared\Domain\Entities\UserPermission;
use App\Modules\Shared\Domain\Exceptions\AccessDeniedException;
use Spatie\Permission\Models\Role;

class ViewCustomRoleUseCase
{

    public function execute(int $id, UserPermission $permissions): Role
    {
        if (!$permissions->can('auth.settings.view')) throw new AccessDeniedException();

        return Role::with('permissions')->findOrFail($id);
    }
}
