<?php

namespace App\Modules\Auth\Presentation\Console\Commands;
use App\Modules\Auth\Application\Actions\Client\CreateClientUseCase;
use App\Modules\Auth\Application\DTOs\Client\ClientUserData;
use Illuminate\Console\Command;

class CreateClientCommand extends Command
{
    protected $signature = 'auth:create-client
                            {--last-name= : Фамилия}
                            {--first-name= : Имя}
                            {--middle-name= : Отчество}
                            {--phone= : Телефон}
                            {--email= : Дополнительный email}
                            {--user-email= : Email для входа}
                            {--password= : Пароль}
                            {--name= : Отображаемое имя}';

    protected $description = 'Создать нового клиента';

    public function __construct(private CreateClientUseCase $createClientUseCase)
    {
        parent::__construct();
    }

    public function handle(): int
    {
        $dto = new ClientUserData(
            lastName: $this->option('last-name') ?? $this->ask('Фамилия'),
            firstName: $this->option('first-name') ?? $this->ask('Имя'),
            middleName: $this->option('middle-name'),
            phone: $this->option('phone') ?? $this->ask('Телефон'),
            email: $this->option('email'),
            name: $this->option('name') ?? $this->ask('Отображаемое имя', $this->option('first-name')),
            userEmail: $this->option('user-email') ?? $this->ask('Email для входа'),
            password: $this->option('password') ?? $this->secret('Пароль')
        );

        try {
            $client = $this->createClientUseCase->execute($dto);
            $this->info("✅ Клиент создан. ID: {$client->getId()}");
            return self::SUCCESS;
        } catch (\Exception $e) {
            $this->error('❌ ' . $e->getMessage());
            return self::FAILURE;
        }
    }
}
