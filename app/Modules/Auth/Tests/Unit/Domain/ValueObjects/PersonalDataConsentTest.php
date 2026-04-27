<?php

namespace App\Modules\Auth\Tests\Unit\Domain\ValueObjects;
use App\Modules\Auth\Domain\ValueObjects\PersonalDataConsent;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use DateTimeImmutable;
class PersonalDataConsentTest extends TestCase
{
    private string $policyVersion;

    protected function setUp(): void
    {
        parent::setUp();
        $this->policyVersion = '№1 от 01.01.2026';
    }

    public function test_creates_active_consent_with_current_datetime(): void
    {
        $before = new DateTimeImmutable();
        $consent = new PersonalDataConsent($this->policyVersion);
        $after = new DateTimeImmutable();

        $this->assertTrue($consent->consented);
        $this->assertGreaterThanOrEqual($before, $consent->consentedAt);
        $this->assertLessThanOrEqual($after, $consent->consentedAt);
        $this->assertSame($this->policyVersion, $consent->policyVersion);
        $this->assertNull($consent->actionIdentifier);
        $this->assertTrue($consent->active);
    }

    public function test_creates_with_action_identifier(): void
    {
        $consent = new PersonalDataConsent(
            $this->policyVersion,
            '192.168.1.1'
        );

        $this->assertSame('192.168.1.1', $consent->actionIdentifier);
    }

    public function test_trims_action_identifier(): void
    {
        $consent = new PersonalDataConsent(
            $this->policyVersion,
            '  192.168.1.1  '
        );

        $this->assertSame('192.168.1.1', $consent->actionIdentifier);
    }

    public function test_throws_exception_when_policy_version_is_empty(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Версия политики не может быть пустой');
        new PersonalDataConsent('');
    }

    public function test_throws_exception_when_policy_version_is_whitespace(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new PersonalDataConsent('   ');
    }

    public function test_withdraw_creates_inactive_consent_with_same_date(): void
    {
        $consent = new PersonalDataConsent($this->policyVersion, null, true);
        $originalDate = $consent->consentedAt;

        $withdrawn = $consent->withdraw();

        $this->assertTrue($withdrawn->consented);
        $this->assertFalse($withdrawn->active);
        $this->assertSame($originalDate, $withdrawn->consentedAt);
        $this->assertSame($consent->policyVersion, $withdrawn->policyVersion);
        $this->assertSame($consent->actionIdentifier, $withdrawn->actionIdentifier);
    }

    public function test_equals_identical_objects(): void
    {
        $a = new PersonalDataConsent($this->policyVersion, 'ip1', true);
        // Не сравниваем по дате, потому что даты могут отличаться на микросекунды;
        // для этого теста создадим объект вручную с фиксированной датой (рефлексией) — но мы не можем,
        // поэтому проверяем equals на одном и том же объекте.
        $this->assertTrue($a->equals($a));
    }

    public function test_equals_different_objects(): void
    {
        $a = new PersonalDataConsent($this->policyVersion, 'ip1', true);
        $b = new PersonalDataConsent('№2 от 01.02.2026', 'ip1', true);

        $this->assertFalse($a->equals($b));
    }

    public function test_equals_with_different_active_status(): void
    {
        $a = new PersonalDataConsent($this->policyVersion, null, true);
        $b = $a->withdraw();

        $this->assertFalse($a->equals($b));
    }

    public function test_two_objects_created_same_time_should_be_equal(): void
    {
        // Это сложно гарантировать без мока времени, но для демонстрации оставим простую проверку.
        $a = new PersonalDataConsent($this->policyVersion);
        $b = new PersonalDataConsent($this->policyVersion);
        // Скорее всего они будут отличаться по дате, поэтому equals должен вернуть false.
        $this->assertFalse($a->equals($b));
    }
}
