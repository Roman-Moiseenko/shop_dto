<?php

namespace App\Modules\Auth\Application\Actions\User;

use App\Modules\Auth\Application\DTOs\User\UpdateUserData;
use App\Modules\Auth\Application\Interfaces\UserRepositoryInterface;
use App\Modules\Auth\Domain\Entities\UserEntity;
use App\Modules\Auth\Domain\Exceptions\UserAlreadyExistsException;
use App\Modules\Auth\Domain\Services\PasswordHasherInterface;
use App\Modules\Auth\Domain\ValueObjects\Email;
use App\Modules\Auth\Domain\ValueObjects\HashedPassword;
use App\Modules\Auth\Domain\ValueObjects\ProfileType;
use App\Modules\Auth\Domain\ValueObjects\StaffRolesAssignment;
use App\Modules\Shared\Domain\Entities\UserPermission;
use App\Modules\Shared\Domain\Exceptions\AccessDeniedException;

readonly class RegisterFreelanceUserUseCase
{
    public function __construct(private UserRepositoryInterface $userRepository,
                                private PasswordHasherInterface $passwordHasher
    ) {}

    public function execute(int $freelanceId, UpdateUserData $dto, UserPermission $permissions): UserEntity
    {
        if (!$permissions->can('auth.user.create')) throw new AccessDeniedException();
        $email = new Email($dto->email);

        if ($this->userRepository->emailExists($email))
            throw new UserAlreadyExistsException("Пользователь с email {$dto->email} уже существует");

        $user = new UserEntity(
            $email,
            HashedPassword::fromPlainText($dto->password, $this->passwordHasher),
        );

        $user->setProfile(ProfileType::FREELANCE, $freelanceId);
/*
        if (empty($dto->roleNames)) throw new InvalidArgumentException('Роли пользователя не определены');
        if (in_array(RoleName::CLIENT, $dto->roleNames)) throw new InvalidArgumentException('Нельзя назначить роль client');
        //Если нет Роли Сотрудника, то добавляем ее
        if (!in_array(RoleName::STAFF, $dto->roleNames)) $dto->roleNames[] = RoleName::STAFF;
        $user->roles = $dto->roleNames;
*/
        $staffRoles = new StaffRolesAssignment($dto->roleNames);
        $user->roles = $staffRoles->toArrayOfStrings();





        return $this->userRepository->save($user);
    }
}
