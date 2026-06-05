<?php

namespace App\Modules\Auth\Presentation\Console\Commands;

use App\Modules\Auth\Application\Actions\User\RegisterAdminUseCase;
use App\Modules\Auth\Application\DTOs\AdminData;
use App\Modules\Auth\Domain\Exceptions\UserAlreadyExistsException;
use Illuminate\Console\Command;

class AdminCreateCommand extends Command
{
    protected $signature = 'admin:create
                            {name : Имя пользователя}
                            {password : Пароль (минимум 8 символов)}';

    protected $description = 'Создать админа {name} с паролем {password}';

    public function __construct(
        private readonly RegisterAdminUseCase $registerAdmin
    )
    {
        parent::__construct();
    }

    public function handle(): bool
    {
        $name = $this->argument('name');
        $password = $this->argument('password');
        $email = $name . '@shop.api';
        try {
            $dto = new AdminData(
                email: $email,
                password: $password,
            );

            $user = $this->registerAdmin->execute($dto);

            $this->info('✅ Пользователь успешно зарегистрирован!');
            $this->table(
                ['ID', 'Email', 'Password'], // 'Профиль'
                [
                    [
                        $user->id,
                        (string)$user->email,
                        $user->getPasswordHash(),
                    ]
                ]
            );

            return self::SUCCESS;
        } catch (UserAlreadyExistsException $e) {
            $this->error('❌ Ошибка: ' . $e->getMessage());
            return self::FAILURE;
        } catch (\InvalidArgumentException $e) {
            $this->error('❌ Некорректные данные: ' . $e->getMessage());
            return self::FAILURE;
        } catch (\Throwable $e) {
            $this->error('❌ Непредвиденная ошибка: ' . $e->getMessage());
            return self::FAILURE;
        }
    }
}
