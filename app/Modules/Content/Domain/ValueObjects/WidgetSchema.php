<?php

namespace App\Modules\Content\Domain\ValueObjects;

use InvalidArgumentException;

/**
 * WidgetSchema — хранит JSON-схему.
 * Может быть простой обёрткой над строкой или массивом, но лучше сделать класс, который гарантирует валидность JSON.
 */
final class WidgetSchema
{
    private array $schema;

    public function __construct(array $schema)
    {
        // Минимальная проверка: корень должен быть объектом со свойствами
        if (!isset($schema['type']) || $schema['type'] !== 'object' || !isset($schema['properties'])) {
            throw new InvalidArgumentException('Схема виджета должна быть JSON Schema типа object с полем properties');
        }
        $this->schema = $schema;
    }

    public function toArray(): array
    {
        return $this->schema;
    }

    public function getProperties(): array
    {
        return $this->schema['properties'] ?? [];
    }

    public function equals(self $other): bool
    {
        return $this->schema === $other->schema;
    }

    public static function fromArray(array $data): self
    {
        return new self($data);
    }
}
