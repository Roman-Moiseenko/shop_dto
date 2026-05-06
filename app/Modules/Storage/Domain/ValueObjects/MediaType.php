<?php

namespace App\Modules\Storage\Domain\ValueObjects;
use InvalidArgumentException;
final class MediaType
{
    public const string ICON = 'icon';
    public const string IMAGE = 'image';
    public const string GALLERY = 'gallery';

    private const array ALLOWED = [self::ICON, self::IMAGE, self::GALLERY];

    private string $value;

    public function __construct(string $value)
    {
        $normalized = strtolower(trim($value));
        if (!in_array($normalized, self::ALLOWED, true)) {
            throw new InvalidArgumentException("Недопустимый тип медиа: {$value}");
        }
        $this->value = $normalized;
    }

    public function getValue(): string { return $this->value; }
    public function __toString(): string { return $this->value; }

    public function isSingle(): bool
    {
        return $this->value !== self::GALLERY;
    }

    public function equals(self $other): bool
    {
        return $this->value === $other->value;
    }
}
