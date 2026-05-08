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
        $employee = $this->fillArrayPermissions('auth', 'employee', $this->listPermissions(true, true));
        $this->createPermission($employee);
        $this->setPermissions('employee', $employee);

        $this->addRole('buyer','Покупатель');
        $buyer = $this->fillArrayPermissions('auth', 'buyer', $this->listPermissions(true, true));
        $this->createPermission($buyer);
        $this->setPermissions('buyer', $buyer);
        $this->setPermissions('client', $buyer);

        $this->addRole('user', 'Пользователь системы');
        $user = $this->fillArrayPermissions('auth', 'user', $this->listPermissions(true, true));
        $this->createPermission($user);
        $this->setPermissions('user', $user);

        $this->addRole('settings', 'Настройки системы');
        $settings = $this->fillArrayPermissions('auth', 'settings', $this->listPermissions());
        $this->createPermission($settings);
        $this->setPermissions('settings', $settings);


        $this->adminSet();
    }

}
