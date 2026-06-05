<?php

namespace App\Modules\Auth\Application\Actions\User;

use App\Modules\Auth\Application\DTOs\AdminData;
use App\Modules\Auth\Application\Interfaces\UserRepositoryInterface;
use App\Modules\Auth\Domain\Entities\UserEntity;
use App\Modules\Auth\Domain\Exceptions\UserAlreadyExistsException;
use App\Modules\Auth\Domain\Services\PasswordHasherInterface;
use App\Modules\Auth\Domain\ValueObjects\Email;
use App\Modules\Auth\Domain\ValueObjects\HashedPassword;
use App\Modules\Auth\Domain\ValueObjects\RoleName;

/**
 * Для консольной команды
 */
readonly class RegisterAdminUseCase
{
    public function __construct(private UserRepositoryInterface $userRepository,
                                private PasswordHasherInterface $passwordHasher
    ) {}

    public function execute(AdminData $dto): UserEntity
    {
        $email = new Email($dto->email);

        if ($this->userRepository->emailExists($email)) {
            throw new UserAlreadyExistsException("Пользователь с email {$dto->email} уже существует");
        }
        $user = new UserEntity(
            $email,
            HashedPassword::fromPlainText($dto->password, $this->passwordHasher),
        );

        $user->roles = [RoleName::ADMIN];

        return $this->userRepository->save($user);
    }
}
