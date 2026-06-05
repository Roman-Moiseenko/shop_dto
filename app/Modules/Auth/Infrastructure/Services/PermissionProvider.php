<?php

namespace App\Modules\Auth\Infrastructure\Services;

use App\Modules\Auth\Domain\Services\PermissionProviderInterface;
use App\Modules\Auth\Domain\ValueObjects\RoleName;
use Spatie\Permission\Models\Role;

class PermissionProvider implements PermissionProviderInterface
{
    public function groupedBySystemRoles(): array
    {
        $systemRoles = Role::where('is_system', true)
            ->whereNotIn('name', RoleName::BASE)
            ->with('permissions')
            ->get();

        return $systemRoles->map(function ($role) {
            return [
                'role' => $role->name,
                'description' => $role->description,
                'permissions' => $role->permissions->pluck('name')->toArray(),
            ];
        })->toArray();
    }
}
