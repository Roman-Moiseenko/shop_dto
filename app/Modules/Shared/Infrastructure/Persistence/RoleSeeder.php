<?php

namespace App\Modules\Shared\Infrastructure\Persistence;

use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

trait RoleSeeder
{

    public function CreateAndFilled(string $nameModule, string $nameRole, string $nameEntity, string $caption, bool $soft = false, bool $blocked = false): void
    {
        $this->addRole($nameRole, $caption);

        $list = $this->fillArrayPermissions($nameModule, $nameEntity, $this->listPermissions($soft, $blocked));
        $this->createPermission($list);
        $this->setPermissions($nameRole, $list);
    }

    protected function getGuardName(): string //Можно переписать в используемых классах
    {
        return 'api';
    }
    protected function adminSet(): void
    {
        Role::findByName('admin', $this->getGuardName())?->givePermissionTo(Permission::all());
    }

    protected function addRole(string $role, string $description = ''): void
    {
        if (is_null(Role::findByParam(['name' => $role, 'guard_name' => $this->getGuardName()])))
            Role::create(['name' => $role, 'guard_name' => $this->getGuardName(), 'description' => $description]);
    }

    protected function createPermission(array $items): void
    {
        foreach ($items as $item) {
            if (is_null(Permission::getPermission(['name' => $item, 'guard_name' => $this->getGuardName()])))
                Permission::create(['name' => $item, 'guard_name' => $this->getGuardName()]);
        }
    }

    protected function setPermissions(string $roleName, array $permission): void
    {
        Role::findByName($roleName, $this->getGuardName())?->givePermissionTo($permission);
    }

    final protected function listPermissions(bool $soft = false, bool $blocked = false): array
    {
        $base = [
            'create',
            'edit',
            'view',
            'delete',
        ];
        if ($soft) $base[] = 'force';
        if ($blocked) $base[] = 'blocked';
        return $base;
    }

    protected function fillArrayPermissions(string $module, string $entity, array $permissions): array
    {
        $result = [];
        foreach ($permissions as $permission) {
            $result[] = $module. '.' . $entity .'.' . $permission;
        }
        return $result;
    }
}
