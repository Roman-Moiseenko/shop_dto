<?php

namespace App\Modules\Auth\Domain\ValueObjects;
use InvalidArgumentException;
final class FullName
{
    private string $fullName;
    private string $lastName;   // Фамилия
    private string $firstName;  // Имя
    private ?string $middleName; // Отчество (может быть null)

    /**
     * @throws InvalidArgumentException
     */
    public function __construct(string $fullName)
    {
        $normalized = $this->normalize($fullName);
        $this->ensureIsValid($normalized);

        $parts = $this->splitIntoParts($normalized);
        $this->lastName = $parts['lastName'];
        $this->firstName = $parts['firstName'];
        $this->middleName = $parts['middleName'] ?? null;

        $this->fullName = $this->buildFullName();
    }

    /**
     * Возвращает полное имя (Фамилия Имя Отчество) в нормализованном виде.
     */
    public function getValue(): string
    {
        return $this->fullName;
    }

    /**
     * Возвращает фамилию.
     */
    public function getLastName(): string
    {
        return $this->lastName;
    }

    /**
     * Возвращает имя.
     */
    public function getFirstName(): string
    {
        return $this->firstName;
    }

    /**
     * Возвращает отчество (или null, если отсутствует).
     */
    public function getMiddleName(): ?string
    {
        return $this->middleName;
    }

    /**
     * Возвращает инициалы (например, "Иванов И.И.").
     */
    public function getInitials(): string
    {
        $initials = $this->lastName . ' ' . mb_substr($this->firstName, 0, 1) . '.';
        if ($this->middleName !== null) {
            $initials .= mb_substr($this->middleName, 0, 1) . '.';
        }
        return $initials;
    }

    /**
     * Возвращает краткую форму (например, "Иван Иванов").
     */
    public function getShortName(): string
    {
        return $this->firstName . ' ' . $this->lastName;
    }

    public function __toString(): string
    {
        return $this->fullName;
    }

    public function equals(self $other): bool
    {
        return $this->fullName === $other->fullName;
    }

    /**
     * Нормализует строку: удаляет лишние пробелы, приводит к верхнему регистру первую букву каждого слова.
     */
    private function normalize(string $value): string
    {
        // Удаляем лишние пробелы и приводим к нижнему регистру
        $value = mb_strtolower(trim(preg_replace('/\s+/', ' ', $value)));

        // Разбиваем на слова
        $words = explode(' ', $value);

        // Каждое слово: разбиваем по дефису, делаем заглавными первые буквы
        $words = array_map(function ($word) {
            $parts = explode('-', $word);
            $capitalizedParts = array_map(function ($part) {
                return mb_strtoupper(mb_substr($part, 0, 1)) . mb_substr($part, 1);
            }, $parts);
            return implode('-', $capitalizedParts);
        }, $words);

        return implode(' ', $words);
    }

    /**
     * Проверяет, что строка содержит хотя бы два слова (фамилия и имя) и не содержит недопустимых символов.
     */
    private function ensureIsValid(string $value): void
    {
        if (empty($value)) {
            throw new InvalidArgumentException('Имя не может быть пустым');
        }

        // Проверка на допустимые символы (буквы, пробелы, дефисы)
        if (!preg_match('/^[\p{L}\s\-]+$/u', $value)) {
            throw new InvalidArgumentException('Имя содержит недопустимые символы');
        }

        $parts = explode(' ', $value);
        if (count($parts) < 2) {
            throw new InvalidArgumentException('Имя должно содержать хотя бы фамилию и имя');
        }

        // Проверяем, что каждое слово не короче 2 символов (кроме инициалов, если они будут)
        foreach ($parts as $part) {
            if (mb_strlen($part) < 2 && !preg_match('/^[A-ZА-ЯЁ]\.$/u', $part)) {
                throw new InvalidArgumentException('Каждая часть имени должна быть не короче 2 символов');
            }
        }
    }

    /**
     * Разбивает полное имя на составляющие.
     * Предполагает порядок: Фамилия Имя Отчество (стандарт для русского языка).
     * Если слов больше трёх, всё после второго считается отчеством (например, двойная фамилия или двойное имя).
     */
    private function splitIntoParts(string $fullName): array
    {
        $parts = explode(' ', $fullName);

        $lastName = $parts[0];
        $firstName = $parts[1];
        $middleName = isset($parts[2]) ? implode(' ', array_slice($parts, 2)) : null;

        // Если слов два, считаем, что это Фамилия Имя
        if ($middleName === null) {
            return [
                'lastName' => $lastName,
                'firstName' => $firstName,
                'middleName' => null,
            ];
        }

        return [
            'lastName' => $lastName,
            'firstName' => $firstName,
            'middleName' => $middleName,
        ];
    }

    /**
     * Собирает полное имя из частей.
     */
    private function buildFullName(): string
    {
        $parts = [$this->lastName, $this->firstName];
        if ($this->middleName !== null) {
            $parts[] = $this->middleName;
        }
        return implode(' ', $parts);
    }
}
