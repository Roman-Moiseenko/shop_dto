<?php

namespace App\Modules\Auth\Domain\ValueObjects;
use InvalidArgumentException;
final class Address
{
    private string $country;
    private ?string $region;
    private string $city;
    private string $street;
    private ?string $postalCode;

    public function __construct(
        string $country,
        string $city,
        string $street,
        ?string $region = null,
        ?string $postalCode = null
    ) {
        $this->country = trim($country);
        $this->city = trim($city);
        $this->street = trim($street);
        $this->region = $region ? trim($region) : null;
        $this->postalCode = $postalCode ? trim($postalCode) : null;

        if (empty($this->country) || empty($this->city) || empty($this->street)) {
            throw new InvalidArgumentException('Страна, город и улица обязательны');
        }
    }

    public function getCountry(): string { return $this->country; }
    public function getRegion(): ?string { return $this->region; }
    public function getCity(): string { return $this->city; }
    public function getStreet(): string { return $this->street; }
    public function getPostalCode(): ?string { return $this->postalCode; }

    public function getFullAddress(): string
    {
        $parts = [
            $this->country,
            $this->region,
            $this->city,
            $this->street,
            $this->postalCode,
        ];
        return implode(', ', array_filter($parts));
    }

    public function __toString(): string
    {
        return $this->getFullAddress();
    }

    public function equals(self $other): bool
    {
        return $this->country === $other->country
            && $this->region === $other->region
            && $this->city === $other->city
            && $this->street === $other->street
            && $this->postalCode === $other->postalCode;
    }
}
