<?php

namespace App\Modules\Content\Database\Seeders;

use App\Modules\Shared\Infrastructure\Persistence\RoleSeeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ContentRoleSeeder extends Seeder
{
    use RoleSeeder;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->addRole('content','Управление контентом');

        //Доступы к блогу
        $blog = $this->fillArrayPermissions('content', 'blog', $this->listPermissions(true, true));
        $this->createPermission($blog);
        $this->setPermissions('content', $blog);

        //Доступ к данным, наполнять страницы, создавать экземпляры виджетов
        $data = $this->fillArrayPermissions('content', 'data', $this->listPermissions(true, true));
        $this->createPermission($data);
        $this->setPermissions('content', $data);
        //Доступ к настройкам, создавать виджеты и др.
        $this->addRole('settings', 'Настройки системы'); //Дублируется по модулям
        $settings = $this->fillArrayPermissions('content', 'settings', $this->listPermissions());
        $this->createPermission($settings);
        $this->setPermissions('settings', $settings);


        $this->adminSet();
    }
}
