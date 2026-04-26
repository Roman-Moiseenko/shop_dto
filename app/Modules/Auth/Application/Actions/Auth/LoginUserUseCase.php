<?php

namespace App\Modules\Auth\Application\Actions\Auth;

use App\Modules\Auth\Application\DTOs\LoginData;
use App\Modules\Auth\Application\Interfaces\UserRepositoryInterface;
use App\Modules\Auth\Domain\ValueObjects\Email;
use App\Modules\Auth\Infrastructure\Exceptions\InvalidCredentialsException;
use Illuminate\Support\Facades\Auth;

class LoginUserUseCase
{
    public function __construct(private readonly UserRepositoryInterface $userRepository) {}

    public function execute(LoginData $dto): string
    {
        $email = new Email($dto->email);
        $user = $this->userRepository->findByEmail($email);

        if (!$user || !$user->validatePassword($dto->password)) {
            throw new InvalidCredentialsException('Неверный email или пароль');
        }

        // Создаём Sanctum токен
        $guard = Auth::guard('web'); // Используем guard с провайдером users
        $model = $guard->getProvider()->retrieveById($user->id);

        return $model->createToken('api-token')->plainTextToken;
    }
}
