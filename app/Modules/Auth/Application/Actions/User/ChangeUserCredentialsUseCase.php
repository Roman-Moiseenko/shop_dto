<?php

namespace App\Modules\Auth\Application\Actions\User;

use App\Modules\Auth\Application\DTOs\User\ChangeUserCredentialsData;
use App\Modules\Auth\Application\Interfaces\UserRepositoryInterface;
use App\Modules\Auth\Domain\Exceptions\InvalidCredentialsException;
use App\Modules\Auth\Domain\Exceptions\UserAlreadyExistsException;
use App\Modules\Auth\Domain\Services\PasswordHasherInterface;
use App\Modules\Auth\Domain\ValueObjects\Email;
use App\Modules\Auth\Domain\ValueObjects\HashedPassword;
use App\Modules\Shared\Application\Interfaces\Mail\MailServiceInterface;
use App\Modules\Shared\Domain\Entities\Mail\Recipient;
use Illuminate\Support\Str;

/**
 * Меняем регистрационные данные клиента - email и пароль
 * Доступ не проверяется
 */
readonly class ChangeUserCredentialsUseCase
{
    public function __construct(
        private UserRepositoryInterface $userRepository,
        private MailServiceInterface    $mailService,
        private readonly string $frontendUrl,
        private readonly PasswordHasherInterface $passwordHasher
    ) {}

    /**
     * @return array{message: string, needsEmailConfirmation?: bool}
     */
    public function execute(int $userId, ChangeUserCredentialsData $dto): array
    {
        $user = $this->userRepository->findById($userId);
        if (!$user) {
            throw new InvalidCredentialsException('Пользователь не найден');
        }

        // Проверяем текущий пароль
        if (!$user->validatePassword($dto->currentPassword, $this->passwordHasher)) {
            throw new InvalidCredentialsException('Неверный текущий пароль');
        }

        $response = ['message' => 'Учётные данные обновлены'];

        // Смена пароля (без подтверждения)
        if ($dto->newPassword) {
            $user->updatePassword(HashedPassword::fromPlainText($dto->newPassword, $this->passwordHasher));
            $this->userRepository->save($user);
        }

        // Смена email с подтверждением
        if ($dto->newEmail && $dto->newEmail !== (string)$user->email) {
            $newEmail = new Email($dto->newEmail);

            // Проверяем, не занят ли email другим пользователем
            if ($this->userRepository->emailExists($newEmail, $userId)) {
                throw new UserAlreadyExistsException('Email уже используется');
            }

            // Генерируем токен и сохраняем запрос на смену
            $token = Str::random(60);
            $this->userRepository->saveEmailVerification($userId, $newEmail, $token);

            // Формируем ссылку подтверждения
            $verificationUrl = $this->frontendUrl . '/verify-email?token=' . $token;

            // Отправляем письмо через общий почтовый сервис
            $this->mailService->send(
                'auth.verify_email',
                [
                    'verificationUrl' => $verificationUrl,
                ],
                new Recipient($newEmail->value)
            );

            $response['message'] = 'На новый email отправлено письмо для подтверждения';
            $response['needsEmailConfirmation'] = true;
        }

        return $response;
    }
}
