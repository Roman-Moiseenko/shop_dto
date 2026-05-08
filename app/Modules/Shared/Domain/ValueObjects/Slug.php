<?php

namespace App\Modules\Shared\Domain\ValueObjects;

//use Illuminate\Support\Str;
use InvalidArgumentException;

final class Slug
{
    private string $value;

    /**
     * @param array|string $segments Один сегмент или массив сегментов пути
     */
    public function __construct(array|string $segments)
    {
        if (is_array($segments)) {
            // Собираем составной slug из массива (например, parent/child)
            $segments = array_map(fn($s) => self::slugify($s), $segments);
            $this->value = implode('/', array_filter($segments));
        } else {
            $this->value = self::slugify($segments);
        }

        if (empty($this->value)) {
            throw new InvalidArgumentException('Slug не может быть пустым');
        }
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
     * Создаёт Slug из родительского Slug и дочернего сегмента.
     */
    public function withParent(self $parentSlug): self
    {
        return new self([$parentSlug->getValue(), $this->value]);
    }

    /**
     * Возвращает последний сегмент пути (для отображения).
     */
    public function lastSegment(): string
    {
        $parts = explode('/', $this->value);
        return end($parts);
    }

    /**
     * Простейшая реализация slug‑преобразования без фасадов.
     */
    private static function slugify(string $text): string
    {
        // Переводим в нижний регистр
        $text = mb_strtolower($text, 'UTF-8');
        // Заменяем пробелы, слэши и дефисы на одиночный дефис
        $text = preg_replace('/[\s\/_]+/', '-', $text);
        // Удаляем все кроме букв, цифр и дефисов
        $text = preg_replace('/[^\p{L}\p{N}\-]+/u', '', $text);
        // Обрезаем дефисы по краям
        $text = trim($text, '-');

        return $text;
    }
}
