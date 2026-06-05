<?php

namespace App\Modules\Auth\Application\Actions\Role;

use App\Modules\Auth\Domain\ValueObjects\RoleName;
use App\Modules\Shared\Domain\Entities\UserPermission;
use App\Modules\Shared\Domain\Exceptions\AccessDeniedException;
use Illuminate\Database\Eloquent\Collection;
use Spatie\Permission\Models\Role;

class IndexCustomRoleUseCase
{
    public function execute(bool $is_system, UserPermission $permissions): Collection
    {
        if (!$permissions->can('auth.settings.view')) throw new AccessDeniedException();

        return Role::with('permissions')
            ->where('is_system', $is_system)
            ->whereNotIn('name', RoleName::BASE)
            ->get();
    }
}
