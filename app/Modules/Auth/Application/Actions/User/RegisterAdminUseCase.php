<?php

namespace App\Modules\Auth\Application\Actions\User;

use App\Modules\Auth\Application\DTOs\AdminData;
use App\Modules\Auth\Application\DTOs\RegisterUserData;
use App\Modules\Auth\Application\Interfaces\UserRepositoryInterface;
use App\Modules\Auth\Domain\Entities\UserEntity;
use App\Modules\Auth\Domain\ValueObjects\Email;
use App\Modules\Auth\Domain\ValueObjects\HashedPassword;
use App\Modules\Auth\Domain\ValueObjects\RoleName;
use App\Modules\Auth\Infrastructure\Exceptions\InvalidCredentialsException;
use App\Modules\Auth\Infrastructure\Exceptions\UserAlreadyExistsException;

class RegisterAdminUseCase
{
    public function __construct(private readonly UserRepositoryInterface $userRepository) {}

    public function execute(AdminData $dto): UserEntity
    {
        $email = new Email($dto->email);

        if ($this->userRepository->emailExists($email)) {
            throw new UserAlreadyExistsException("Пользователь с email {$dto->email} уже существует");
        }
        $user = new UserEntity(
            $email,
            HashedPassword::fromPlainText($dto->password),
        );

        $user->roles = [RoleName::ADMIN];

        return $this->userRepository->save($user);
    }
}
