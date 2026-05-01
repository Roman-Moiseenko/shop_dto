<?php

namespace App\Modules\Auth\Domain\ValueObjects;

use InvalidArgumentException;

final class Address
{
    public string $country {
        get => $this->country;
    }
    public ?string $region {
        get => $this->region;
    }
    public string $city {
        get => $this->city;
    }
    public string $street {
        get => $this->street;
    }
    public ?string $postalCode {
        get => $this->postalCode;
    }

    public function __construct(
        string  $country,
        string  $city,
        string  $street,
        ?string $region = null,
        ?string $postalCode = null
    )
    {
        $this->country = trim($country);
        $this->city = trim($city);
        $this->street = trim($street);
        $this->region = $region ? trim($region) : null;
        $this->postalCode = $postalCode ? trim($postalCode) : null;

    }

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
