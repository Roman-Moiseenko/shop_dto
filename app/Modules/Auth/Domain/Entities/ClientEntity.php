<?php

namespace App\Modules\Auth\Domain\Entities;

use App\Modules\Auth\Domain\ValueObjects\Address;
use App\Modules\Auth\Domain\ValueObjects\Email;
use App\Modules\Auth\Domain\ValueObjects\FullName;
use App\Modules\Auth\Domain\ValueObjects\Gender;
use App\Modules\Auth\Domain\ValueObjects\PersonalDataConsent;
use App\Modules\Auth\Domain\ValueObjects\PhoneNumber;
use DateTimeImmutable;

class ClientEntity
{
    public ?UserEntity $user = null {
        get => $this->user;
        set => $this->user = $value;
    }
    public ?int $id = null {
        get => $this->id;
        set => $this->id = $value;
    }
    public FullName $fullName {
        get => $this->fullName;
        set => $this->fullName = $value;
    }
    public ?PhoneNumber $phone = null {
        get => $this->phone;
        set => $this->phone = $value;
    }
    public Email $email {
        get => $this->email;
        set => $this->email = $value;
    }
    public ?DateTimeImmutable $birthDate = null {
        get => $this->birthDate;
        set => $this->birthDate = $value;
    }
    public ?Gender $gender = null {
        get => $this->gender;
        set => $this->gender = $value;
    }
    public ?Address $address = null {
        get => $this->address;
        set => $this->address = $value;
    }
    public ?DateTimeImmutable $bannedAt = null {
        get => $this->bannedAt;
    }
    public bool $isActive {
        get => $this->bannedAt == null;
    }
    public ?PersonalDataConsent $dataConsent = null {
        get => $this->dataConsent;
        set => $this->dataConsent = $value;
    }
    public function __construct(
        FullName $fullName,
        Email $email,
        ?PhoneNumber $phone = null,
    )
    {
        $this->fullName = $fullName;
        $this->email = $email;
        $this->phone = $phone;
    }

    public function ban(): void
    {
        $this->bannedAt = new DateTimeImmutable();
    }

    public function unban(): void
    {
        $this->bannedAt = null;
    }
}
