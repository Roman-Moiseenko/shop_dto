<?php

namespace App\Modules\Auth\Presentation\Console\Commands;

use Illuminate\Console\Command;
use JetBrains\PhpStorm\Deprecated;
use Spatie\Permission\Models\Role;

#[Deprecated]
class RoleCreateCommand extends Command
{
    protected $signature = 'role:create {name}';
    protected $description = 'Command description';

    public function handle(): bool
    {
        $name = $this->argument('name');
        $role = Role::create(['name' => $name]);
        $this->info('Роль создана');
        return true;
    }
}
