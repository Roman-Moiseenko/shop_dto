<?php

namespace App\Modules\Content\Domain\ValueObjects;

use InvalidArgumentException;

final class ContactType
{
    public const string PHONE = 'phone';
    public const string EMAIL = 'email';
    public const string ADDRESS = 'address';
    public const string SOCIAL = 'social';
    public const string MESSENGER = 'messenger';
    public const string OTHER = 'other';

    private const array ALLOWED = [
        self::PHONE,
        self::EMAIL,
        self::ADDRESS,
        self::SOCIAL,
        self::MESSENGER,
        self::OTHER,
    ];

    private string $value;

    public function __construct(string $value)
    {
        $normalized = strtolower(trim($value));
        if (!in_array($normalized, self::ALLOWED, true)) {
            throw new InvalidArgumentException("Недопустимый тип контакта: {$value}");
        }
        $this->value = $normalized;
    }

    public function getValue(): string { return $this->value; }
    public function __toString(): string { return $this->value; }
    public function equals(self $other): bool { return $this->value === $other->value; }

    // Удобные проверки
    public function isPhone(): bool { return $this->value === self::PHONE; }
    public function isEmail(): bool { return $this->value === self::EMAIL; }
    public function isAddress(): bool { return $this->value === self::ADDRESS; }
    public function isSocial(): bool { return $this->value === self::SOCIAL; }
    public function isMessenger(): bool { return $this->value === self::MESSENGER; }
    public function isOther(): bool { return $this->value === self::OTHER; }

    // Статические фабрики для удобства
    public static function phone(): self { return new self(self::PHONE); }
    public static function email(): self { return new self(self::EMAIL); }
    public static function address(): self { return new self(self::ADDRESS); }
    public static function social(): self { return new self(self::SOCIAL); }
    public static function messenger(): self { return new self(self::MESSENGER); }
    public static function other(): self { return new self(self::OTHER); }

    public static function allowed(): array
    {
        return self::ALLOWED;
    }
}
