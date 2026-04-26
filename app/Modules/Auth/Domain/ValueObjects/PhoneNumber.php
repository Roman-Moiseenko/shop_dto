<?php

namespace App\Modules\Auth\Domain\ValueObjects;

use InvalidArgumentException;
use libphonenumber\NumberParseException;
use libphonenumber\PhoneNumberFormat;
use Illuminate\Http\Request;
use libphonenumber\PhoneNumberUtil;

final class PhoneNumber
{
    private string $rawInput;
    private string $e164;          // +79991234567
    private string $national;      // 8 (999) 123-45-67 (зависит от страны)
    private string $international; // +7 999 123-45-67
    private int $countryCode;      // 7
    private string $nationalNumber; // 9991234567

    /**
     * @throws InvalidArgumentException
     */
    public function __construct(string $value, ?string $defaultRegion = 'RU')
    {
        $this->rawInput = trim($value);
        $phoneUtil = PhoneNumberUtil::getInstance();

        try {
            $parsed = $phoneUtil->parse($this->rawInput, $defaultRegion);
        } catch (NumberParseException $e) {
            throw new InvalidArgumentException(
                sprintf('Некорректный номер телефона: "%s". Ошибка: %s', $this->rawInput, $e->getMessage())
            );
        }

        if (!$phoneUtil->isValidNumber($parsed)) {
            throw new InvalidArgumentException(
                sprintf('Номер телефона "%s" не является действительным.', $this->rawInput)
            );
        }

        $this->e164 = $phoneUtil->format($parsed, PhoneNumberFormat::E164);
        $this->national = $phoneUtil->format($parsed, PhoneNumberFormat::NATIONAL);
        $this->international = $phoneUtil->format($parsed, PhoneNumberFormat::INTERNATIONAL);
        $this->countryCode = $parsed->getCountryCode();
        $this->nationalNumber = $parsed->getNationalNumber();
    }

    /**
     * Возвращает номер в формате E.164 (+79991234567) — оптимально для хранения в БД.
     */
    public function getValue(): string
    {
        return $this->e164;
    }

    /**
     * Возвращает исходную строку, переданную в конструктор.
     */
    public function getRawInput(): string
    {
        return $this->rawInput;
    }

    /**
     * Возвращает национальный формат (например, "8 (999) 123-45-67").
     */
    public function getNational(): string
    {
        return $this->national;
    }

    /**
     * Возвращает международный формат (например, "+7 999 123-45-67").
     */
    public function getInternational(): string
    {
        return $this->international;
    }

    /**
     * Возвращает код страны (например, 7 для России).
     */
    public function getCountryCode(): int
    {
        return $this->countryCode;
    }

    /**
     * Возвращает национальный номер без кода страны (например, "9991234567").
     */
    public function getNationalNumber(): string
    {
        return $this->nationalNumber;
    }

    public function __toString(): string
    {
        return $this->e164;
    }

    public function equals(self $other): bool
    {
        return $this->e164 === $other->e164;
    }
}
