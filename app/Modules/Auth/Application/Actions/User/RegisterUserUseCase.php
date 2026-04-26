<?php

namespace App\Modules\Auth\Application\Actions\User;

use App\Modules\Auth\Application\DTOs\RegisterUserData;
use App\Modules\Auth\Application\Interfaces\UserRepositoryInterface;
use App\Modules\Auth\Domain\Entities\UserEntity;
use App\Modules\Auth\Domain\ValueObjects\Email;
use App\Modules\Auth\Domain\ValueObjects\HashedPassword;
use App\Modules\Auth\Domain\ValueObjects\RoleName;
use App\Modules\Auth\Infrastructure\Exceptions\InvalidCredentialsException;
use App\Modules\Auth\Infrastructure\Exceptions\UserAlreadyExistsException;

class RegisterUserUseCase
{
    public function __construct(private readonly UserRepositoryInterface $userRepository) {}

    public function execute(RegisterUserData $dto): UserEntity
    {
        $email = new Email($dto->email);

        if ($this->userRepository->emailExists($email)) {
            throw new UserAlreadyExistsException("Пользователь с email {$dto->email} уже существует");
        }

        $user = new UserEntity(
            $dto->name,
            $email,
            HashedPassword::fromPlainText($dto->password),
        );

        if ($dto->profileableType && $dto->profileableId) {
            $user->setProfile($dto->profileableType, $dto->profileableId);
        } else {
            throw new InvalidCredentialsException("Не задан тип пользователя Client, Staff или Freelance");
        }
        //Если роль не задана (с сайта), то используем по умолчанию - клиент
        $user->roles = empty($dto->roleNames) ? [RoleName::CLIENT] : $dto->roleNames;

        return $this->userRepository->save($user);
    }
}
