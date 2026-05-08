<?php

namespace App\Modules\Content\Domain\ValueObjects;

use InvalidArgumentException;

class PageTemplate
{
    public const string DEFAULT = 'default';
    public const string LANDING = 'landing';
    public const string FULL_WIDTH = 'full-width';
    public const string CONTACTS = 'contacts';
    public const string CUSTOM = 'custom'; // если понадобится позже

    private const array ALLOWED = [
        self::DEFAULT,
        self::LANDING,
        self::FULL_WIDTH,
        self::CONTACTS,
        self::CUSTOM,
    ];

    private string $value;

    public function __construct(string $value)
    {
        $normalized = strtolower(trim($value));
        if (!in_array($normalized, self::ALLOWED, true)) {
            throw new InvalidArgumentException("Недопустимый шаблон страницы: {$value}");
        }
        $this->value = $normalized;
    }

    public function getValue(): string
    {
        return $this->value;
    }

    public function __toString(): string
    {
        return $this->value;
    }

    public function equals(self $other): bool
    {
        return $this->value === $other->value;
    }

    /**
     * Является ли шаблон нестандартным (т.е. не default).
     */
    public function isCustom(): bool
    {
        return $this->value !== self::DEFAULT;
    }

    // Статические конструкторы для удобства
    public static function default(): self
    {
        return new self(self::DEFAULT);
    }

    public static function landing(): self
    {
        return new self(self::LANDING);
    }

    public static function fullWidth(): self
    {
        return new self(self::FULL_WIDTH);
    }

    public static function contacts(): self
    {
        return new self(self::CONTACTS);
    }
}
