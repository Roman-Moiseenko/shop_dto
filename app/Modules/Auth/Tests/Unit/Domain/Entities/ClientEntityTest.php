<?php

namespace App\Modules\Auth\Tests\Unit\Domain\Entities;
use App\Modules\Auth\Domain\Entities\ClientEntity;
use App\Modules\Auth\Domain\Entities\UserEntity;
use App\Modules\Auth\Domain\ValueObjects\Address;
use App\Modules\Auth\Domain\ValueObjects\Email;
use App\Modules\Auth\Domain\ValueObjects\FullName;
use App\Modules\Auth\Domain\ValueObjects\Gender;
use App\Modules\Auth\Domain\ValueObjects\PersonalDataConsent;
use App\Modules\Auth\Domain\ValueObjects\PhoneNumber;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;
class ClientEntityTest extends TestCase
{
    private FullName $fullName;
    private Email $email;

    protected function setUp(): void
    {
        parent::setUp();
        $this->fullName = new FullName('Иванов Иван Иванович');
        $this->email = new Email('ivan@example.com');
    }

    public function test_creates_client_with_required_fields(): void
    {
        $client = new ClientEntity(
            $this->fullName,
            $this->email,
        );
        $this->assertNull($client->id);
        $this->assertEquals($this->fullName, $client->fullName);
        $this->assertEquals($this->email, $client->email);
        $this->assertNull($client->phone);
        $this->assertNull($client->birthDate);
        $this->assertNull($client->gender);
        $this->assertNull($client->address);
        $this->assertNull($client->bannedAt);
        $this->assertTrue($client->isActive);
    }

    public function test_creates_client_with_optional_phone(): void
    {
        $phone = new PhoneNumber('+79001234567');
        $client = new ClientEntity(
            $this->fullName,
            $this->email,
            $phone
        );

        $this->assertEquals($phone, $client->phone);
    }

    public function test_ban_and_unban(): void
    {
        $client = new ClientEntity(
            $this->fullName,
            $this->email,
        );

        $this->assertTrue($client->isActive);
        $this->assertNull($client->bannedAt);

        $client->ban();
        $this->assertNotNull($client->bannedAt);
        $this->assertFalse($client->isActive);

        $client->unban();
        $this->assertNull($client->bannedAt);
        $this->assertTrue($client->isActive);
    }

    public function test_sets_optional_fields_via_setters(): void
    {
        $client = new ClientEntity(
            $this->fullName,
            $this->email,
        );

        $birthDate = new DateTimeImmutable('1990-05-20');
        $gender = new Gender('male');
        $address = new Address('Россия', 'Москва', 'ул. Тверская, 1');

        $client->birthDate = $birthDate;
        $client->gender = $gender;
        $client->address = $address;

        $this->assertEquals($birthDate, $client->birthDate);
        $this->assertEquals($gender, $client->gender);
        $this->assertEquals($address, $client->address);
    }

    public function test_user_can_be_assigned_and_retrieved(): void
    {
        $client = new ClientEntity(
            $this->fullName,
            $this->email,
        );

        $this->assertNull($client->user);

        $user = $this->createMock(UserEntity::class);
        $client->user = $user;

        $this->assertSame($user, $client->user);
    }
}
