<?php

namespace App\Modules\Storage\Domain\ValueObjects;

use InvalidArgumentException;

final class GalleryName
{
    private string $value;
    public function __construct(string $value)
    {
        $value = trim($value);
        if (mb_strlen($value) < 3 || mb_strlen($value) > 100) {
            throw new InvalidArgumentException('Название галереи должно быть от 3 до 100 символов');
        }
        $this->value = $value;
    }
    public function getValue(): string { return $this->value; }
    public function __toString(): string { return $this->value; }
    public function equals(self $other): bool { return $this->value === $other->value; }
}
