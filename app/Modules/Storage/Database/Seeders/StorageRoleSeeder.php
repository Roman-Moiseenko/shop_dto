<?php

namespace App\Modules\Storage\Database\Seeders;

use App\Modules\Shared\Infrastructure\Persistence\RoleSeeder;
use Illuminate\Database\Seeder;

class StorageRoleSeeder extends Seeder
{
    use RoleSeeder;
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //Системные роли и доступы текущего модуля
        $this->addRole('media','Работа с медиа');

        //Доступ к данным
        $media = $this->fillArrayPermissions('storage', 'media', $this->listPermissions(true, true));
        $this->createPermission($media);
        $this->setPermissions('media', $media);
        //Доступ к настройкам
        $this->addRole('settings', 'Настройки системы'); //Дублируется по модулям
        $settings = $this->fillArrayPermissions('storage', 'settings', $this->listPermissions());
        $this->createPermission($settings);
        $this->setPermissions('settings', $settings);

        $this->adminSet();
    }


}
