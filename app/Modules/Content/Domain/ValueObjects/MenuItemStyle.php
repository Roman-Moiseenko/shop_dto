<?php

namespace App\Modules\Content\Domain\ValueObjects;

use InvalidArgumentException;

final class MenuItemStyle
{
    public const string SALE = 'sale';
    public const string HIGHLIGHT = 'highlight';
    public const string NEW = 'new';

    private const array ALLOWED = [self::SALE, self::HIGHLIGHT, self::NEW];

    private string $value;

    public function __construct(string $value)
    {
        $normalized = strtolower(trim($value));
        if (!in_array($normalized, self::ALLOWED, true)) {
            throw new InvalidArgumentException("Недопустимый стиль пункта меню: {$value}");
        }
        $this->value = $normalized;
    }

    public function getValue(): string { return $this->value; }
    public function __toString(): string { return $this->value; }
    public function equals(self $other): bool { return $this->value === $other->value; }
}
