<?php

namespace App\Modules\Auth\Database\Seeders;

use App\Modules\Auth\Domain\ValueObjects\RoleName;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class AuthRoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //Базовые роли, доступы не назначаются
        $this->addRole(RoleName::ADMIN);
        $this->addRole(RoleName::CLIENT);
        $this->addRole(RoleName::STAFF);

        //Системные роли и доступы текущего модуля
        $this->addRole('employee');
        $this->addRole('buyer');
        $this->addRole('user');

        $employee = [
            'auth.employee.create',
            'auth.employee.edit',
            'auth.employee.view',
            'auth.employee.delete',
            'auth.employee.force',
            'auth.employee.blocked',
        ];
        $buyer = [
            'auth.buyer.create',
            'auth.buyer.edit',
            'auth.buyer.view',
            'auth.buyer.delete',
            'auth.buyer.force',
            'auth.buyer.blocked',
        ];
        $user = [
            'auth.user.create',
            'auth.user.view',
            'auth.user.edit',
            'auth.user.delete',
            'auth.user.force',
            'auth.user.blocked',
        ];

        $this->createPermission($employee);
        $this->createPermission($buyer);
        $this->createPermission($user);

        Role::findByName('employee', 'api')?->givePermissionTo($employee);
        Role::findByName('buyer', 'api')?->givePermissionTo($buyer);
        Role::findByName('user', 'api')?->givePermissionTo($user);
    }

    private function addRole(string $role): void
    {
        if (is_null(Role::findByParam(['name' => $role, 'guard_name' => 'api']))) Role::create(['name' => $role, 'guard_name' => 'api']);
    }

    public function createPermission(array $items): void
    {
        foreach ($items as $item)
            if (is_null(Permission::getPermission(['name' => $item, 'guard_name' => 'api']))) Permission::create(['name' => $item, 'guard_name' => 'api']);
    }
}
