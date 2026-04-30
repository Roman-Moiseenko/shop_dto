<?php

namespace App\Modules\Auth\Application\Actions\User;

use App\Modules\Auth\Application\DTOs\User\UpdateUserData;
use App\Modules\Auth\Application\Interfaces\UserRepositoryInterface;
use App\Modules\Auth\Domain\Entities\UserEntity;
use App\Modules\Auth\Domain\Services\PasswordHasherInterface;
use App\Modules\Auth\Domain\ValueObjects\Email;
use App\Modules\Auth\Domain\ValueObjects\HashedPassword;
use App\Modules\Auth\Domain\ValueObjects\RoleName;
use App\Modules\Auth\Infrastructure\Exceptions\UserAlreadyExistsException;
use App\Modules\Auth\Infrastructure\Models\Freelance;
use InvalidArgumentException;

readonly class RegisterFreelanceUserUseCase
{
    public function __construct(private UserRepositoryInterface $userRepository,
                                private readonly PasswordHasherInterface $passwordHasher
    ) {}

    public function execute(int $freelanceId, UpdateUserData $dto): UserEntity
    {
        $email = new Email($dto->email);

        if ($this->userRepository->emailExists($email))
            throw new UserAlreadyExistsException("Пользователь с email {$dto->email} уже существует");

        $user = new UserEntity(
            $email,
            HashedPassword::fromPlainText($dto->password, $this->passwordHasher),
        );

        $user->setProfile(Freelance::class, $freelanceId);

        if (empty($dto->roleNames)) throw new InvalidArgumentException('Роли пользователя не определены');
        if (in_array(RoleName::CLIENT, $dto->roleNames))
            throw new InvalidArgumentException('Нельзя назначить роль client');
        $user->roles = $dto->roleNames;

        return $this->userRepository->save($user);
    }
}
