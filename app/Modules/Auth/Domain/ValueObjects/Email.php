<?php

namespace App\Modules\Auth\Domain\ValueObjects;

use InvalidArgumentException;

final class Email
{
    public string $value {
        get => $this->value;
    }

    /**
     * @throws InvalidArgumentException
     */
    public function __construct(string $value)
    {
        $normalized = $this->normalize($value);
        $this->ensureIsValid($normalized);
        $this->value = $normalized;
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
     * Возвращает локальную часть (до символа '@')
     */
    public function getLocalPart(): string
    {
        return explode('@', $this->value)[0];
    }

    /**
     * Возвращает доменную часть (после символа '@')
     */
    public function getDomain(): string
    {
        return explode('@', $this->value)[1];
    }

    /**
     * Нормализует email: приводит к нижнему регистру, удаляет пробелы
     */
    private function normalize(string $value): string
    {
        return strtolower(trim($value));
    }

    /**
     * Проверяет корректность email-адреса
     */
    private function ensureIsValid(string $value): void
    {
        if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
            throw new InvalidArgumentException(
                sprintf('Некорректный email-адрес: "%s"', $value)
            );
        }

        if (!$this->isRfcCompliant($value)) {
            throw new InvalidArgumentException(
                sprintf('Email-адрес содержит недопустимые символы: "%s"', $value)
            );
        }
    }

    /**
     * Проверяет соответствие RFC 5322 (упрощённая версия)
     */
    private function isRfcCompliant(string $value): bool
    {
        $pattern = '/^[a-zA-Z0-9.!#$%&\'*+\/=?^_`{|}~-]+@[a-zA-Z0-9-]+(?:\.[a-zA-Z0-9-]+)*$/';
        return preg_match($pattern, $value) === 1;
    }
}
