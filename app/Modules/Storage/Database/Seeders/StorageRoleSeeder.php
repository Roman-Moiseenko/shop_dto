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
        $this->addRole('media', 'Работа с медиа');
        $media = [
            'storage.media.create',
            'storage.media.edit',
            'storage.media.view',
            'storage.media.delete',
            'storage.media.force',
            'storage.media.blocked',
        ];

        $this->createPermission($media);

        $this->setPermissions('media', $media);

        $this->adminSet();
    }


}
