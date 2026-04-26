<?php

namespace App\Modules\Auth\Domain\Entities;

use App\Modules\Auth\Domain\ValueObjects\Email;
use App\Modules\Auth\Domain\ValueObjects\FullName;
use App\Modules\Auth\Domain\ValueObjects\PhoneNumber;
use DateTimeImmutable;

class StaffEntity
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
    public ?PhoneNumber $workPhone = null {
        get => $this->workPhone;
        set => $this->workPhone = $value;
    }
    public ?PhoneNumber $personalPhone = null {
        get => $this->personalPhone;
        set => $this->personalPhone = $value;
    }
    public ?Email $workEmail = null {
        get => $this->workEmail;
        set => $this->workEmail = $value;
    }
    public string $position {
        get => $this->position;
        set => $this->position = $value;
    }
    public ?string $department = null {
        get => $this->department;
        set => $this->department = $value;
    }
    public ?DateTimeImmutable $hireDate = null {
        get => $this->hireDate;
        set => $this->hireDate = $value;
    }
    public ?DateTimeImmutable $terminationDate = null {
        get => $this->terminationDate;
        set => $this->terminationDate = $value;
    }
    public bool $isActive {
        get => $this->terminationDate == null;
    }
    public ?DateTimeImmutable $birthDate = null {
        get => $this->birthDate;
        set => $this->birthDate = $value;
    }
    public ?string $telegramChatId = null {
        get => $this->telegramChatId;
        set => $this->telegramChatId = $value;
    }
    public ?string $maxChatId = null {
        get => $this->maxChatId;
        set => $this->maxChatId = $value;
    }
    public ?string $notes = null {
        get => $this->notes;
        set => $this->notes = $value;
    }

    public function __construct(FullName $fullName, string $position)
    {
        $this->fullName = $fullName;
        $this->position = $position;
    }

    public function terminate(DateTimeImmutable $date): void
    {
        $this->terminationDate = $date;
    }

    public function rehire(): void
    {
        $this->terminationDate = null;
    }

}
