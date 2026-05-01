<?php

namespace App\Modules\Auth\Domain\ValueObjects;
use DateTimeImmutable;
use InvalidArgumentException;
final class PersonalDataConsent
{
    public bool $consented {
        get => $this->consentedValue;
    }

    public DateTimeImmutable $consentedAt {
        get => $this->consentedAtValue;
        set {
            if (!is_null($value)) $this->consentedAtValue = $value;
        }
    }

    public string $policyVersion {
        get => $this->policyVersionValue;
    }

    public ?string $actionIdentifier {
        get => $this->actionIdentifierValue;
    }
    public bool $active {
        get => $this->active;
    }

    private bool $consentedValue;
    private DateTimeImmutable $consentedAtValue;
    private string $policyVersionValue;
    private ?string $actionIdentifierValue;
  //  private bool $activeValue;

    public function __construct(
        string $policyVersion,
        ?string $actionIdentifier = null,
        bool $active = true
    ) {
        $this->consentedValue = true;
        $this->consentedAtValue = new DateTimeImmutable(); // всегда текущая дата/время
        $this->policyVersionValue = $this->validatePolicyVersion($policyVersion);
        $this->actionIdentifierValue = $actionIdentifier ? trim($actionIdentifier) : null;
        $this->active = $active;
    }

    /**
     * Возвращает новый объект с отозванным согласием.
     * Дата согласия остаётся прежней.
     */
    public function withdraw(): self
    {
        $withdrawn = new self($this->policyVersionValue, $this->actionIdentifierValue, false);
        // Перетираем автоматически установленную дату на исходную
        $withdrawn->consentedAtValue = $this->consentedAtValue;
        return $withdrawn;
    }

    public function equals(self $other): bool
    {
        return $this->consentedValue === $other->consentedValue
            && $this->consentedAtValue == $other->consentedAtValue
            && $this->policyVersionValue === $other->policyVersionValue
            && $this->actionIdentifierValue === $other->actionIdentifierValue
            && $this->active === $other->active;
    }

    private function validatePolicyVersion(string $version): string
    {
        $trimmed = trim($version);
        if (empty($trimmed)) {
            throw new InvalidArgumentException('Версия политики не может быть пустой');
        }
        return $trimmed;
    }
}
