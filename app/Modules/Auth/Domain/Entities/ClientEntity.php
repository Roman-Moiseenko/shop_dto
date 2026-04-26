<?php

namespace App\Modules\Auth\Domain\Entities;
use App\Modules\Auth\Domain\ValueObjects\Address;
use App\Modules\Auth\Domain\ValueObjects\Email;
use App\Modules\Auth\Domain\ValueObjects\FullName;
use App\Modules\Auth\Domain\ValueObjects\Gender;
use App\Modules\Auth\Domain\ValueObjects\PhoneNumber;
use DateTimeImmutable;
class ClientEntity
{
    private ?int $id = null;
    private FullName $fullName;
    private PhoneNumber $phone;
    private ?EmailVO $email = null;
    private ?DateTimeImmutable $birthDate = null;
    private ?Gender $gender = null;
    private ?Address $address = null;
    private bool $isActive;
    private bool $agreeToNewsletter;
    private string $preferredLanguage;
    private ?string $externalId = null;

    public function __construct(
        FullName $fullName,
        PhoneNumber $phone,
        ?Email $email = null,
        ?DateTimeImmutable $birthDate = null,
        ?Gender $gender = null,
        ?Address $address = null,
        bool $agreeToNewsletter = false,
        string $preferredLanguage = 'ru'
    ) {
        $this->fullName = $fullName;
        $this->phone = $phone;
        $this->email = $email;
        $this->birthDate = $birthDate;
        $this->gender = $gender;
        $this->address = $address;
        $this->agreeToNewsletter = $agreeToNewsletter;
        $this->preferredLanguage = $preferredLanguage;
        $this->isActive = true;
    }

    // Геттеры
    public function getId(): ?int { return $this->id; }
    public function getFullName(): FullName { return $this->fullName; }
    public function getPhone(): PhoneNumber { return $this->phone; }
    public function getEmail(): ?Email { return $this->email; }
    public function getBirthDate(): ?DateTimeImmutable { return $this->birthDate; }
    public function getGender(): ?Gender { return $this->gender; }
    public function getAddress(): ?Address { return $this->address; }
    public function isActive(): bool { return $this->isActive; }
    public function getAgreeToNewsletter(): bool { return $this->agreeToNewsletter; }
    public function getPreferredLanguage(): string { return $this->preferredLanguage; }
    public function getExternalId(): ?string { return $this->externalId; }

    // Сеттеры
    public function setId(int $id): void { $this->id = $id; }
    public function setFullName(FullName $fullName): void { $this->fullName = $fullName; }
    public function setPhone(PhoneNumber $phone): void { $this->phone = $phone; }
    public function setEmail(?Email $email): void { $this->email = $email; }
    public function setBirthDate(?DateTimeImmutable $date): void { $this->birthDate = $date; }
    public function setGender(?Gender $gender): void { $this->gender = $gender; }
    public function setAddress(?Address $address): void { $this->address = $address; }
    public function setAgreeToNewsletter(bool $value): void { $this->agreeToNewsletter = $value; }
    public function setPreferredLanguage(string $lang): void { $this->preferredLanguage = $lang; }
    public function setExternalId(?string $id): void { $this->externalId = $id; }

    public function deactivate(): void
    {
        $this->isActive = false;
    }

    public function activate(): void
    {
        $this->isActive = true;
    }
}
