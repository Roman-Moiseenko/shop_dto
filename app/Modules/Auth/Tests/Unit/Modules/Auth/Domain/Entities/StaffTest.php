<?php

namespace App\Modules\Auth\Tests\Unit\Modules\Auth\Domain\Entities;
use App\Modules\Auth\Domain\Entities\StaffEntity;
use App\Modules\Auth\Domain\ValueObjects\Email;
use App\Modules\Auth\Domain\ValueObjects\FullName;
use App\Modules\Auth\Domain\ValueObjects\PhoneNumber;
use Illuminate\Foundation\Testing\TestCase;
use InvalidArgumentException;
use DateTimeImmutable;

class StaffTest extends TestCase
{
    private FullName $fullName;
    private PhoneNumber $workPhone;
    private Email $workEmail;

    protected function setUp(): void
    {
        parent::setUp();
        $this->fullName = new FullName('Иванов Иван Иванович');
        $this->workPhone = new PhoneNumber('+79001234567');
        $this->workEmail = new Email('ivanov@example.com');
    }

    /** @test */
    public function it_can_be_created_with_minimum_required_fields(): void
    {
        $staff = new StaffEntity(
            $this->fullName,
            'Разработчик',
        );

        $staff->department = 'IT';
        $staff->workPhone = new PhoneNumber('+79001234567');
        $staff->workEmail = new Email('ivanov@example.com');
        $this->assertNull($staff->id);
        $this->assertEquals($this->fullName, $staff->fullName);
        $this->assertEquals('Разработчик', $staff->position);
        $this->assertEquals('IT', $staff->department);
        $this->assertEquals($this->workPhone, $staff->workPhone);
        $this->assertEquals($this->workEmail, $staff->workEmail);
        $this->assertTrue($staff->isActive);
        $this->assertNull($staff->hireDate);
        $this->assertNull($staff->terminationDate);
        $this->assertNull($staff->personalPhone);
        $this->assertNull($staff->birthDate);
        $this->assertNull($staff->terminationDate);
        $this->assertNull($staff->notes);
    }

    /** @test */
    public function it_can_set_and_get_id(): void
    {
        $staff = new StaffEntity($this->fullName, 'Менеджер');
        $staff->id = 42;
        $this->assertEquals(42, $staff->id);
    }

    /** @test */
    public function it_can_set_hire_date(): void
    {
        $staff = new StaffEntity($this->fullName, 'Менеджер');
        $hireDate = new DateTimeImmutable('2025-01-15');
        $staff->hireDate = $hireDate;
        $this->assertEquals($hireDate, $staff->hireDate);
    }

    /** @test */
    public function it_can_set_birth_date(): void
    {
        $staff = new StaffEntity($this->fullName, 'Менеджер');
        $birthDate = new DateTimeImmutable('1990-05-20');
        $staff->birthDate = $birthDate;
        $this->assertEquals($birthDate, $staff->birthDate);
    }

    /** @test */
    public function it_can_set_personal_phone(): void
    {
        $staff = new StaffEntity($this->fullName, 'Менеджер');
        $phone = new PhoneNumber('+79005556677');
        $staff->personalPhone = $phone;
        $this->assertEquals($phone, $staff->personalPhone);
    }

    /** @test */
    public function it_can_set_telegram_chat_id(): void
    {
        $staff = new StaffEntity($this->fullName, 'Менеджер');
        $staff->telegramChatId = '123456789';
        $this->assertEquals('123456789', $staff->telegramChatId);
    }


    /** @test */
    public function it_can_set_notes(): void
    {
        $staff = new StaffEntity($this->fullName, 'Менеджер');
        $staff->notes = 'Важный сотрудник';
        $this->assertEquals('Важный сотрудник', $staff->notes);
    }

    /** @test */
    public function it_can_terminate_and_rehire(): void
    {
        $staff = new StaffEntity($this->fullName, 'Менеджер');
        $this->assertTrue($staff->isActive);
        $this->assertNull($staff->terminationDate);

        $terminationDate = new DateTimeImmutable('2025-06-01');
        $staff->terminate($terminationDate);

        $this->assertFalse($staff->isActive);
        $this->assertEquals($terminationDate, $staff->terminationDate);

        $staff->rehire();
        $this->assertTrue($staff->isActive);
        $this->assertNull($staff->terminationDate);
    }

    /** @test */
    public function it_can_update_full_name(): void
    {
        $staff = new StaffEntity($this->fullName, 'Менеджер');
        $newFullName = new FullName('Петров Пётр Петрович');
        $staff->fullName = $newFullName;
        $this->assertEquals($newFullName, $staff->fullName);
    }

    /** @test */
    public function it_can_update_position_and_department(): void
    {
        $staff = new StaffEntity($this->fullName, 'Менеджер', 'Продажи');
        $staff->position = 'Старший менеджер';
        $staff->department = 'VIP отдел';
        $this->assertEquals('Старший менеджер', $staff->position);
        $this->assertEquals('VIP отдел', $staff->department);
    }
}
