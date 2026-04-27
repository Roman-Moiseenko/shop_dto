<?php

namespace App\Modules\Auth\Tests\Unit\Domain\Entities;

use App\Modules\Auth\Domain\Entities\FreelanceEntity;
use App\Modules\Auth\Domain\Entities\UserEntity;
use App\Modules\Auth\Domain\ValueObjects\Email;
use App\Modules\Auth\Domain\ValueObjects\FullName;
use App\Modules\Auth\Domain\ValueObjects\HashedPassword;
use App\Modules\Auth\Domain\ValueObjects\PhoneNumber;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;

class FreelanceTest extends TestCase
{
    private FullName $fullName;

    protected function setUp(): void
    {
        parent::setUp();
        $this->fullName = new FullName('Иванов Иван Иванович');
    }

    public function test_can_be_created_with_minimum_required_fields(): void
    {
        $freelance = new FreelanceEntity($this->fullName, 'Разработчик');

        $this->assertNull($freelance->id);
        $this->assertEquals($this->fullName, $freelance->fullName);
        $this->assertEquals('Разработчик', $freelance->position);
        $this->assertNull($freelance->personalPhone);
        $this->assertNull($freelance->personalEmail);
        $this->assertNull($freelance->hireDate);
        $this->assertNull($freelance->terminationDate);
        $this->assertTrue($freelance->isActive);
        $this->assertNull($freelance->telegramChatId);
        $this->assertNull($freelance->maxChatId);
        $this->assertNull($freelance->notes);
    }

    public function test_can_set_and_get_id(): void
    {
        $freelance = new FreelanceEntity($this->fullName, 'Тестировщик');
        $freelance->id = 42;
        $this->assertEquals(42, $freelance->id);
    }

    public function test_can_set_and_get_optional_fields(): void
    {
        $freelance = new FreelanceEntity($this->fullName, 'Менеджер');
        $phone = new PhoneNumber('+79001234567');
        $email = new Email('freelance@example.com');
        $hireDate = new DateTimeImmutable('2025-01-01');

        $freelance->personalPhone = $phone;
        $freelance->personalEmail = $email;
        $freelance->hireDate = $hireDate;
        $freelance->telegramChatId = '12345';
        $freelance->maxChatId = 'max123';
        $freelance->notes = 'Заметки';

        $this->assertEquals($phone, $freelance->personalPhone);
        $this->assertEquals($email, $freelance->personalEmail);
        $this->assertEquals($hireDate, $freelance->hireDate);
        $this->assertEquals('12345', $freelance->telegramChatId);
        $this->assertEquals('max123', $freelance->maxChatId);
        $this->assertEquals('Заметки', $freelance->notes);
    }

    public function test_is_active_depends_on_termination_date(): void
    {
        $freelance = new FreelanceEntity($this->fullName, 'Дизайнер');
        $this->assertTrue($freelance->isActive);

        $freelance->terminate(new DateTimeImmutable('2025-02-01'));
        $this->assertFalse($freelance->isActive);
        $this->assertInstanceOf(DateTimeImmutable::class, $freelance->terminationDate);

        $freelance->rehire();
        $this->assertTrue($freelance->isActive);
        $this->assertNull($freelance->terminationDate);
    }

    public function test_can_terminate_and_rehire(): void
    {
        $freelance = new FreelanceEntity($this->fullName, 'Аналитик');
        $terminationDate = new DateTimeImmutable('2025-03-15');
        $freelance->terminate($terminationDate);
        $this->assertFalse($freelance->isActive);
        $this->assertEquals($terminationDate, $freelance->terminationDate);

        $freelance->rehire();
        $this->assertTrue($freelance->isActive);
        $this->assertNull($freelance->terminationDate);
    }


    public function test_user_starts_as_null(): void
    {
        $freelance = new FreelanceEntity($this->fullName, 'Разработчик');
        $this->assertNull($freelance->user);
    }
}
