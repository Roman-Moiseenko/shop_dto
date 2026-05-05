<?php

namespace App\Modules\Auth\Application\Actions\User;

use App\Modules\Auth\Application\Interfaces\UserRepositoryInterface;
use InvalidArgumentException;
use App\Modules\Auth\Domain\ValueObjects\Email;

/**
 * Подтверждение смены почты
 * Доступ не проверяется
 */
readonly class ConfirmEmailUseCase
{
    public function __construct(
        private UserRepositoryInterface $userRepository,
    ) {}

    public function execute(string $token): void
    {
        $verification = $this->userRepository->findEmailVerificationByToken($token);
        if (!$verification || now()->gt($verification->expires_at)) {
            throw new InvalidArgumentException('Токен недействителен или срок его действия истёк');
        }

        $user = $this->userRepository->findById($verification->user_id);
        if (!$user) {
            throw new InvalidArgumentException('Пользователь не найден');
        }

        // Если email в профиле пользователя совпадает с new_email, это первичная верификация
        if ((string)$user->email === $verification->new_email) {
            $user->verifyEmail(); // устанавливает email_verified_at
        } else {
            // Смена email: обновляем email и отмечаем как подтверждённый
            $user->email = new Email($verification->new_email);
            $user->verifyEmail();
        }

        $this->userRepository->save($user);
        $this->userRepository->deleteEmailVerification($token);
    }
}
