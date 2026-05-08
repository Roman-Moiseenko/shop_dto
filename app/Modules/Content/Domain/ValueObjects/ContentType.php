<?php

namespace App\Modules\Content\Domain\ValueObjects;

use InvalidArgumentException;

final class ContentType
{
    private const string SIMPLE = 'simple';
    private const string WIDGET_BASED = 'widget_based';

    private const array ALLOWED = [self::SIMPLE, self::WIDGET_BASED];

    private string $value;

    public function __construct(string $value)
    {
        $normalized = strtolower(trim($value));
        if (!in_array($normalized, self::ALLOWED, true)) {
            throw new InvalidArgumentException("Недопустимый тип контента: {$value}");
        }
        $this->value = $normalized;
    }

    public function getValue(): string { return $this->value; }
    public function __toString(): string { return $this->value; }
    public function isSimple(): bool { return $this->value === self::SIMPLE; }
    public function isWidgetBased(): bool { return $this->value === self::WIDGET_BASED; }
    public function equals(self $other): bool { return $this->value === $other->value; }

    public static function simple(): self { return new self(self::SIMPLE); }
    public static function widgetBased(): self { return new self(self::WIDGET_BASED); }

}
