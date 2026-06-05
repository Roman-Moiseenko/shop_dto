<?php

namespace App\Modules\Auth\Application\Actions\Auth;

use App\Modules\Auth\Application\DTOs\LoginData;
use App\Modules\Auth\Application\Interfaces\UserRepositoryInterface;
use App\Modules\Auth\Domain\Exceptions\InvalidCredentialsException;
use App\Modules\Auth\Domain\Services\PasswordHasherInterface;
use App\Modules\Auth\Domain\ValueObjects\Email;
use Illuminate\Support\Facades\Auth;

readonly class LoginUserUseCase
{
    public function __construct(private UserRepositoryInterface $userRepository,
    private PasswordHasherInterface                             $passwordHasher,) {}

    public function execute(LoginData $dto): string
    {
        $email = new Email($dto->email);
        $user = $this->userRepository->findByEmail($email);

        if (!$user || !$user->validatePassword($dto->password, $this->passwordHasher)) {
            throw new InvalidCredentialsException('Неверный email или пароль');
        }

        // Создаём Sanctum токен
        $guard = Auth::guard('api');
        $model = $guard->getProvider()->retrieveById($user->id);

        return $model->createToken('api-token')->plainTextToken;
    }
}
