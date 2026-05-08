<?php

namespace App\Modules\Content\Domain\ValueObjects;
use InvalidArgumentException;
final class PageStatus
{
    private const string DRAFT = 'draft';
    private const string PUBLISHED = 'published';

    private const array ALLOWED = [self::DRAFT, self::PUBLISHED];

    private string $value;

    public function __construct(string $value)
    {
        $normalized = strtolower(trim($value));
        if (!in_array($normalized, self::ALLOWED, true)) {
            throw new InvalidArgumentException("Недопустимый статус страницы: {$value}");
        }
        $this->value = $normalized;
    }

    public function getValue(): string { return $this->value; }
    public function __toString(): string { return $this->value; }
    public function isDraft(): bool { return $this->value === self::DRAFT; }
    public function isPublished(): bool { return $this->value === self::PUBLISHED; }
    public function equals(self $other): bool { return $this->value === $other->value; }

    public static function draft(): self { return new self(self::DRAFT); }
    public static function published(): self { return new self(self::PUBLISHED); }
}
