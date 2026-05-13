<?php

namespace App\Modules\Auth\Domain\ValueObjects;
use InvalidArgumentException;
use JetBrains\PhpStorm\Deprecated;

final class RoleName
{
    public const string ADMIN = 'admin'; //Профиль с полным доступом
    public const string CLIENT = 'client'; //Клиент
    public const string STAFF = 'staff'; //Сотрудник
    const array BASE = [
        self::ADMIN,
        self::CLIENT,
        self::STAFF,
    ];

    private string $value;

    public function __construct(string $value)
    {
        $normalized = strtolower(trim($value));
        //$this->ensureIsValid($normalized);
        $this->value = $normalized;
    }

    public function getValue(): string { return $this->value; }
    public function __toString(): string { return $this->value; }
    public function equals(self $other): bool { return $this->value === $other->value; }
    public function isAdmin(): bool { return $this->value === self::ADMIN; }
    public function isClient(): bool { return $this->value === self::CLIENT; }
    public function isStaff(): bool{ return $this->value === self::STAFF; }

    //Фабрика
    public static function fromNames(array $names): array
    {
        $roles = [];
        foreach ($names as $name) {
            $roles[] = new RoleName($name);
        }
        return empty($roles) ? [new RoleName(self::CLIENT)] : $roles;
    }

    //Кол-во ролей не ограничено, создаются в админке
    #[Deprecated]
    private function ensureIsValid(string $value): void
    {
        if (!in_array($value, self::BASE, true)) {
            throw new InvalidArgumentException("Недопустимое имя роли: {$value}");
        }
    }
}
