<?php

namespace App\Modules\Auth\Domain\ValueObjects;
use InvalidArgumentException;
final class Gender
{
    public const string MALE = 'male';
    public const string FEMALE = 'female';

    private const array ALLOWED = [self::MALE, self::FEMALE];

    private ?string $value;

    public function __construct(string $value)
    {
        $normalized = strtolower(trim($value));
        if (!in_array($normalized, self::ALLOWED, true)) {
            throw new InvalidArgumentException("Недопустимое значение пола: {$value}");
        }
        $this->value = $normalized;
    }

    public function getValue(): ?string { return $this->value; }
    public function __toString(): string { return $this->value ?? ''; }
    public function equals(self $other): bool { return $this->value === $other->value; }
}
