<?php

namespace App\Modules\Auth\Presentation\Console\Commands;

use Illuminate\Console\Command;

class RoleAssignCommand extends Command
{
    protected $signature = 'role:assign {name} {role}';
    protected $description = 'Command description';


    public function handle(): bool
    {
        $name = $this->argument('name');
        $role = $this->argument('role');
        $email = $name . '@shop.api';

        if (is_null($user = User::where('email', $email)->first())) {
            $this->error('Пользователь не найден');
            return false;
        }
        $user->assignRole($role);

        $this->info('Пользователю ' . $name . ' назначена роль ' . $role);

        return true;
    }
}
