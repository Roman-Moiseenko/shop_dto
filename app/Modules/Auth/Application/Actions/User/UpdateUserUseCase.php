<?php

namespace App\Modules\Auth\Application\Actions\User;

use App\Modules\Auth\Application\DTOs\User\UpdateUserData;
use App\Modules\Auth\Application\Interfaces\UserRepositoryInterface;
use App\Modules\Auth\Domain\Entities\UserEntity;
use App\Modules\Auth\Domain\Exceptions\UserAlreadyExistsException;
use App\Modules\Auth\Domain\Services\PasswordHasherInterface;
use App\Modules\Auth\Domain\ValueObjects\Email;
use App\Modules\Auth\Domain\ValueObjects\HashedPassword;
use App\Modules\Auth\Domain\ValueObjects\RoleName;
use App\Modules\Shared\Domain\Entities\UserPermission;
use App\Modules\Shared\Domain\Exceptions\AccessDeniedException;
use InvalidArgumentException;

class UpdateUserUseCase
{
    public function __construct(
        private readonly UserRepositoryInterface $userRepository,
        private readonly PasswordHasherInterface $passwordHasher
    ) {}

    public function execute(int $staffId, UpdateUserData $dto, UserPermission $permissions): UserEntity
    {
        if (!$permissions->can('auth.user.edit')) throw new AccessDeniedException();
        $user = $this->userRepository->findByStaffId($staffId);
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
            $user->updatePassword(HashedPassword::fromPlainText($dto->password, $this->passwordHasher));


        if (!$user->hasRole(RoleName::CLIENT)) {
            if(empty($dto->roleNames)) {
                throw new InvalidArgumentException('Роли пользователя не определены');
            } else {
                if (in_array(RoleName::CLIENT, $dto->roleNames))
                    throw new InvalidArgumentException('Нельзя назначить роль client');
                $user->roles = $dto->roleNames;
            }
        }

        if ($dto->active) {
            $user->unban();
        } else {
            $user->ban();
        }

        return $this->userRepository->save($user);
    }
}
