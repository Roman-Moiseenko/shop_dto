<?php

namespace App\Modules\Auth\Application\Actions\User;

use App\Modules\Auth\Application\DTOs\User\UpdateUserData;
use App\Modules\Auth\Application\Interfaces\UserRepositoryInterface;
use App\Modules\Auth\Domain\Entities\UserEntity;
use App\Modules\Auth\Domain\ValueObjects\Email;
use App\Modules\Auth\Domain\ValueObjects\HashedPassword;
use App\Modules\Auth\Domain\ValueObjects\RoleName;
use App\Modules\Auth\Infrastructure\Exceptions\UserAlreadyExistsException;
use App\Modules\Auth\Infrastructure\Models\Staff;
use InvalidArgumentException;

readonly class RegisterStaffUserUseCase
{
    public function __construct(private UserRepositoryInterface $userRepository) {}

    public function execute(int $staffId, UpdateUserData $dto): UserEntity
    {
        $email = new Email($dto->email);

        if ($this->userRepository->emailExists($email)) {
            throw new UserAlreadyExistsException("Пользователь с email {$dto->email} уже существует");
        }

        $user = new UserEntity(
            $email,
            HashedPassword::fromPlainText($dto->password),
        );

        $user->setProfile(Staff::class, $staffId);

        if (empty($dto->roleNames)) throw new InvalidArgumentException('Роли пользователя не определены');
        if (in_array(RoleName::CLIENT, $dto->roleNames))
            throw new InvalidArgumentException('Нельзя назначить роль client');
        $user->roles = $dto->roleNames;

        return $this->userRepository->save($user);
    }
}
