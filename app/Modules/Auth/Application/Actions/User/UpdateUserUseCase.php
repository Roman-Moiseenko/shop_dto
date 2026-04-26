<?php

namespace App\Modules\Auth\Application\Actions\User;

use App\Modules\Auth\Application\DTOs\UpdateUserData;
use App\Modules\Auth\Application\Interfaces\UserRepositoryInterface;
use App\Modules\Auth\Domain\Entities\UserEntity;
use App\Modules\Auth\Domain\ValueObjects\Email;
use App\Modules\Auth\Domain\ValueObjects\HashedPassword;
use App\Modules\Auth\Domain\ValueObjects\RoleName;
use App\Modules\Auth\Infrastructure\Exceptions\UserAlreadyExistsException;
use InvalidArgumentException;

class UpdateUserUseCase
{
    public function __construct(
        private readonly UserRepositoryInterface $userRepository,
    ) {}

    public function execute(int $staffId, UpdateUserData $dto): UserEntity
    {
        $user = $this->userRepository->findByStaffId($staffId);
        //$user = $this->userRepository->findById($userId);
        if (!$user) {
            throw new InvalidArgumentException('Пользователь не найден');
        }

        if ($dto->email !== null) {
            $newEmail = new Email($dto->email);
            if ($this->userRepository->emailExists($newEmail, $user->id)) {
                throw new UserAlreadyExistsException("Email {$dto->email} уже занят");
            }
            $user->email = $newEmail;
        }
        if ($dto->password !== null)
            $user->updatePassword(HashedPassword::fromPlainText($dto->password));

        if (!$user->hasRole(RoleName::CLIENT) && empty($dto->roleNames))
            throw new InvalidArgumentException('Роли пользователя не определены');
        if (empty($dto->roleNames)) {
            $user->roles = $dto->roleNames;
        }

        if ($dto->active) {
            $user->unban();
        } else {
            $user->ban();
        }


        return $this->userRepository->save($user);
    }
}
