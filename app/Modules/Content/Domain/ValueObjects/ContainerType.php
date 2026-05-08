<?php

namespace App\Modules\Content\Domain\ValueObjects;

use InvalidArgumentException;

final class ContainerType
{
    public const string PAGE = 'page';
    public const string POST = 'post';

    private const array ALLOWED = [self::PAGE, self::POST];

    private string $value;

    public function __construct(string $value)
    {
        $normalized = strtolower(trim($value));
        if (!in_array($normalized, self::ALLOWED, true)) {
            throw new InvalidArgumentException("Недопустимый тип контейнера: {$value}");
        }
        $this->value = $normalized;
    }

    public function getValue(): string { return $this->value; }
    public function __toString(): string { return $this->value; }
    public function equals(self $other): bool { return $this->value === $other->value; }

    public static function page(): self { return new self(self::PAGE); }
    public static function post(): self { return new self(self::POST); }
}
