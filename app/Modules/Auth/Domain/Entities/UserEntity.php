<?php

namespace App\Modules\Auth\Domain\Entities;

use App\Modules\Auth\Domain\Services\PasswordHasherInterface;
use App\Modules\Auth\Domain\ValueObjects\Email;
use App\Modules\Auth\Domain\ValueObjects\HashedPassword;
use App\Modules\Auth\Domain\ValueObjects\RoleName;
use App\Modules\Auth\Domain\ValueObjects\ProfileType;
use DateTimeImmutable;

class UserEntity
{
    public ?int $id = null {
        get => $this->id;
        set => $this->id = $value;
    }
    public Email $email {
        get => $this->email;
        set => $this->email = $value;
    }
    //TODO Перевести остальные в хуки
    public ?DateTimeImmutable $emailVerifiedAt = null {
        get => $this->emailVerifiedAt;
        set => $this->emailVerifiedAt = $value;
    }
    private HashedPassword $password;
    public ?string $rememberToken = null {
        get => $this->rememberToken;
        set => $this->rememberToken = $value;
    }
    public ?ProfileType $profileableType = null {
        get => $this->profileableType;
    }
    public ?int $profileableId = null {
        get => $this->profileableId;
    }
    public array $roles = [] {
        get => $this->roles;
        set => $this->roles = $value;
    }
    public bool $isBanned {
        get => $this->bannedAt !== null;
    }

    public array $permissions = [] {
        get => $this->permissions;
        set => $this->permissions = $value;
    }
    private ?DateTimeImmutable $bannedAt = null;

    public function __construct(
        Email          $email,
        HashedPassword $password,
    )
    {
        $this->email = $email;
        $this->password = $password;
    }

    public function getPasswordHash(): string
    {
        return $this->password->getHash();
    }
    public function getBannedAt(): ?DateTimeImmutable
    {
        return $this->bannedAt;
    }

    // Сеттеры (используются репозиторием)

    public function setProfile(?ProfileType $type, ?int $id): void
    {
        $this->profileableType = $type;       // Храним 'staff', 'client' или 'freelance'
        $this->profileableId = $id;
    }

    // Блокировка
    public function ban(): void
    {
        $this->bannedAt = new DateTimeImmutable();
    }

    public function unban(): void
    {
        $this->bannedAt = null;
    }

    // Бизнес-методы
    public function verifyEmail(): void
    {
        $this->emailVerifiedAt = new DateTimeImmutable();
    }

    public function isEmailVerified(): bool
    {
        return $this->emailVerifiedAt !== null;
    }

    public function updatePassword(HashedPassword $newPassword): void
    {
        $this->password = $newPassword;
    }

    public function validatePassword(string $plain, PasswordHasherInterface $hasher): bool
    {
        return $this->password->verify($plain, $hasher);
    }

    public function hasRole(string $roleName): bool
    {
        return in_array($roleName, $this->roles, true);
    }

    public function isAdmin(): bool
    {
        return $this->hasRole(RoleName::ADMIN);
    }

    public function isStaff(): bool
    {
        return $this->profileableType === ProfileType::STAFF;
    }

    public function isFreelance(): bool
    {
        return $this->profileableType === ProfileType::FREELANCE;
    }

    public function isClient(): bool
    {
        return $this->profileableType === ProfileType::CLIENT;
    }

    public function setBannedAt(DateTimeImmutable $date): void
    {
        $this->bannedAt = $date;
    }
}
