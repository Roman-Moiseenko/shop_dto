<?php

namespace App\Modules\Auth\Presentation\Console\Commands;

use Illuminate\Console\Command;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleFillStartCommand extends Command
{
    protected $signature = 'role:auth';
    protected $description = 'Создание основных ролей и доступов для Auth';

    public function handle(): bool
    {
        //Базовые роли
        $this->addRole('admin');
        $this->addRole('client');
        $this->addRole('staff');

        //Доступы по модулю

        //Раздел каталог
        $catalogItems = ['create catalog', 'edit catalog', 'view catalog', 'delete catalog', 'force catalog', 'published catalog'];
        //Раздел продажи
        $orderItems = ['create order', 'edit order', 'view order', 'delete order'];
        //Раздел Настройки
        $settingsItems = ['create settings', 'edit settings', 'view settings', 'delete settings'];
        //Раздел веб-сайт
        $webItems = ['create web', 'edit web', 'view web', 'delete web'];
        //Раздел обратная связь
        $feedItems = ['create feed', 'edit feed', 'view feed', 'delete feed'];
        //Раздел Аналитика
        $analyticItems = ['create analytic', 'edit analytic', 'view analytic', 'delete analytic'];



        $this->createPermission($catalogItems);

        $this->createPermission($orderItems);
        $this->createPermission($settingsItems);
        $this->createPermission($webItems);
        $this->createPermission($feedItems);
        $this->createPermission($analyticItems);


        $admin = Role::findByName('admin');
        $admin->givePermissionTo(Permission::all());

        $product = Role::findByName('catalog');
        $product->givePermissionTo($catalogItems);

        $reader = Role::findByName('reader');
        $reader->givePermissionTo([
            'view catalog', 'view order', 'view settings', 'view web', 'view feed', 'view analytic',
        ]);
        $this->info('Роли и доступы созданы');
        return true;
    }

    private function addRole(string $role): void
    {
        if (is_null(Role::findByParam(['name' => $role, 'guard_name' => 'api']))) Role::create(['name' => $role, 'guard_name' => 'api']);
    }

    public function createPermission(array $items): void
    {
        foreach ($items as $item)
            if(is_null(Permission::getPermission(['name' => $item, 'guard_name' => 'api']))) Permission::create(['name' => $item, 'guard_name' => 'api']);
    }
}
