<?php

namespace App\Modules\Auth\Application\Actions\Auth;

use App\Modules\Auth\Application\Interfaces\UserRepositoryInterface;
use App\Modules\Auth\Domain\ValueObjects\HashedPassword;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;

class ResetPasswordUseCase
{
    public function __construct(private UserRepositoryInterface $userRepository) {}

    public function execute(array $credentials): string
    {
        return Password::reset($credentials, function ($user, $password) {
            $domainUser = $this->userRepository->findById($user->id);
            $domainUser->updatePassword(HashedPassword::fromPlainText($password));
            $domainUser->rememberToken = Str::random(60);
            $this->userRepository->save($domainUser);

            event(new PasswordReset($user));
        });
    }
}
