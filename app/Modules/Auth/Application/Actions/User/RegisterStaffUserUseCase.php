<?php

namespace App\Modules\Auth\Application\Actions\User;

use App\Modules\Auth\Application\DTOs\User\UpdateUserData;
use App\Modules\Auth\Application\Interfaces\UserRepositoryInterface;
use App\Modules\Auth\Domain\Entities\UserEntity;
use App\Modules\Auth\Domain\Services\PasswordHasherInterface;
use App\Modules\Auth\Domain\ValueObjects\Email;
use App\Modules\Auth\Domain\ValueObjects\HashedPassword;
use App\Modules\Auth\Domain\ValueObjects\StaffRolesAssignment;
use App\Modules\Auth\Infrastructure\Exceptions\UserAlreadyExistsException;
use App\Modules\Auth\Infrastructure\Models\Staff;
use App\Modules\Shared\Domain\Entities\UserPermission;
use App\Modules\Shared\Infrastructure\Exceptions\AccessDeniedException;


readonly class RegisterStaffUserUseCase
{
    public function __construct(private UserRepositoryInterface $userRepository,
                                private PasswordHasherInterface $passwordHasher
    ) {}

    public function execute(int $staffId, UpdateUserData $dto, UserPermission $permissions): UserEntity
    {
        if (!$permissions->can('auth.user.create')) throw new AccessDeniedException();

        $email = new Email($dto->email);

        if ($this->userRepository->emailExists($email)) {
            throw new UserAlreadyExistsException("Пользователь с email {$dto->email} уже существует");
        }

        $user = new UserEntity(
            $email,
            HashedPassword::fromPlainText($dto->password, $this->passwordHasher),
        );

        $user->setProfile(Staff::class, $staffId);
        $staffRoles = new StaffRolesAssignment($dto->roleNames);
        $user->roles = $staffRoles->toArrayOfStrings();

        return $this->userRepository->save($user);
    }
}
