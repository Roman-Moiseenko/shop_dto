<?php

namespace App\Modules\Auth\Database\Seeders;

use App\Modules\Auth\Domain\ValueObjects\RoleName;
use App\Modules\Shared\Infrastructure\Persistence\RoleSeeder;
use Illuminate\Database\Seeder;

class AuthRoleSeeder extends Seeder
{
    use RoleSeeder;
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
        $this->addRole('employee', 'Персонал');
        $this->addRole('buyer','Покупатель');
        $this->addRole('user', 'Пользователь системы');
        $this->addRole('settings', 'Настройки системы');

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
        $settings = [
            'auth.settings.create',
            'auth.settings.view',
            'auth.settings.edit',
            'auth.settings.delete',
        ];


        $this->createPermission($employee);
        $this->createPermission($buyer);
        $this->createPermission($user);
        $this->createPermission($settings);

        $this->setPermissions('employee', $employee);
        $this->setPermissions('buyer', $buyer);
        $this->setPermissions('client', $buyer);
        $this->setPermissions('user', $user);
        $this->setPermissions('settings', $settings);

        $this->adminSet();
    }

}
