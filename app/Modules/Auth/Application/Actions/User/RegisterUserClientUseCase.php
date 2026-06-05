<?php

namespace App\Modules\Auth\Application\Actions\User;

use App\Modules\Auth\Application\DTOs\User\RegisterUserData;
use App\Modules\Auth\Application\Interfaces\ClientRepositoryInterface;
use App\Modules\Auth\Application\Interfaces\UserRepositoryInterface;
use App\Modules\Auth\Domain\Entities\UserEntity;
use App\Modules\Auth\Domain\Exceptions\ClientNotFoundException;
use App\Modules\Auth\Domain\Exceptions\UserAlreadyExistsException;
use App\Modules\Auth\Domain\Services\PasswordHasherInterface;
use App\Modules\Auth\Domain\ValueObjects\Email;
use App\Modules\Auth\Domain\ValueObjects\HashedPassword;
use App\Modules\Auth\Domain\ValueObjects\ProfileType;
use App\Modules\Shared\Application\Interfaces\Mail\MailServiceInterface;
use App\Modules\Shared\Domain\Entities\Mail\Recipient;
use App\Modules\Shared\Domain\Entities\UserPermission;
use App\Modules\Shared\Domain\Exceptions\AccessDeniedException;
use Illuminate\Support\Str;

/**
 * Регистрация доступа клиенту
 */
readonly class RegisterUserClientUseCase
{
    public function __construct(
        private UserRepositoryInterface   $userRepository,
        private ClientRepositoryInterface $clientRepository,
        private readonly MailServiceInterface $mailService,
        private readonly string $frontendUrl,
        private readonly PasswordHasherInterface $passwordHasher
    ) {}

    public function execute(int $clientId, RegisterUserData $dto, UserPermission $permissions): UserEntity
    {
        //Исключаем для самостоятельной регистрации Id = null
        if ($permissions->getId() != null && !$permissions->can('auth.user.create'))
            throw new AccessDeniedException();
        // Проверяем, что клиент существует
        $client = $this->clientRepository->findById($clientId);
        if (!$client) {
            throw new ClientNotFoundException("Клиент с ID {$clientId} не найден");
        }

        $email = new Email($dto->email);

        // Проверяем уникальность email среди пользователей
        if ($this->userRepository->emailExists($email)) {
            throw new UserAlreadyExistsException("Пользователь с email {$dto->email} уже существует");
        }

        $user = new UserEntity(
            $email,
            HashedPassword::fromPlainText($dto->password, $this->passwordHasher),
        );

        $user->setProfile(ProfileType::CLIENT, $clientId);
        $user->roles = ['client'];

        $savedUser = $this->userRepository->save($user);
        // Генерация токена и отправка подтверждения
        $token = Str::random(60);
        $this->userRepository->saveEmailVerification($savedUser->id, $email, $token);

        $verificationUrl = $this->frontendUrl . '/verify-email?token=' . $token;
        $this->mailService->send(
            'auth.verify_email',
            ['verificationUrl' => $verificationUrl],
            new Recipient($email->value)
        );

        return $savedUser;
    }
}
