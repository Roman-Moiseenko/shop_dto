<?php

namespace App\Modules\Storage\Domain\ValueObjects;

use InvalidArgumentException;

final class TagName
{
    private string $value;

    public function __construct(string $value)
    {
        $value = trim($value);
        if (mb_strlen($value) < 2 || mb_strlen($value) > 50) {
            throw new InvalidArgumentException('Название тега должно быть от 2 до 50 символов');
        }
        $this->value = $value;
    }

    public function getValue(): string { return $this->value; }
    public function __toString(): string { return $this->value; }
    public function equals(self $other): bool { return $this->value === $other->value; }
}
