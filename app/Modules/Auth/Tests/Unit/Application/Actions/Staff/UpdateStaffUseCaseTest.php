<?php

namespace App\Modules\Auth\Tests\Unit\Application\Actions\Staff;

use App\Modules\Auth\Application\Actions\Staff\UpdateStaffUseCase;
use App\Modules\Auth\Application\DTOs\Staff\StaffUpdateData;
use App\Modules\Auth\Application\Interfaces\StaffRepositoryInterface;
use App\Modules\Auth\Domain\Entities\StaffEntity;
use App\Modules\Auth\Domain\ValueObjects\Email;
use App\Modules\Auth\Domain\ValueObjects\FullName;
use App\Modules\Auth\Domain\ValueObjects\PhoneNumber;
use App\Modules\Shared\Domain\Exceptions\AccessDeniedException;
use DateTimeImmutable;
use InvalidArgumentException;
use Mockery;
use PHPUnit\Framework\TestCase;
use Tests\Trait\MockPermission;

class UpdateStaffUseCaseTest extends TestCase
{
    use MockPermission;
    private StaffRepositoryInterface $staffRepo;
    private UpdateStaffUseCase $useCase;

    function getModuleName(): string
    {
        return  'auth';
    }

    function getEntityName(): string
    {
        return 'employee';
    }

    protected function setUp(): void
    {
        $this->staffRepo = Mockery::mock(StaffRepositoryInterface::class);
        $this->useCase = new UpdateStaffUseCase($this->staffRepo);
    }

    protected function tearDown(): void
    {
        Mockery::close();
    }

    private function createExistingStaff(): StaffEntity
    {
        $staff = new StaffEntity(
            new FullName('Иванов Иван Иванович'),
            'Старая должность'
        );
        $staff->id = 1;
        $staff->department = 'Старый отдел';
        $staff->workPhone = new PhoneNumber('+79001234567');
        $staff->personalPhone = new PhoneNumber('+79007654321');
        $staff->workEmail = new Email('old@example.com');
        $staff->hireDate = new DateTimeImmutable('2020-01-01');
        $staff->birthDate = new DateTimeImmutable('1990-05-15');
        $staff->telegramChatId = 'old_telegram';
        $staff->notes = 'Старые заметки';
        return $staff;
    }

    public function test_updates_all_fields(): void
    {
        $existing = $this->createExistingStaff();

        $this->staffRepo->shouldReceive('findById')
            ->with(1)
            ->andReturn($existing);

        $this->staffRepo->shouldReceive('save')
            ->once()
            ->with(Mockery::type(StaffEntity::class))
            ->andReturn($existing);

        $dto = new StaffUpdateData(
            lastName: 'Петров',
            firstName: 'Пётр',
            position: 'Новая должность',
            middleName: 'Петрович',
            department: 'Новый отдел',
            workPhone: '+79001112233',
            personalPhone: '+79004445566',
            workEmail: 'new@example.com',
            hireDate: '2025-01-01',
            birthDate: '1995-01-01',
            telegramChatId: 'new_telegram',
            maxChatId: 'new_max',
            notes: 'Новые заметки',
            terminated: false,
        );
        $permission = $this->mockUserPermission(edit: true);
        $result = $this->useCase->execute(1, $dto, $permission);

        $this->assertSame('Петров Пётр Петрович', (string) $result->fullName);
        $this->assertSame('Новая должность', $result->position);
        $this->assertSame('Новый отдел', $result->department);
        $this->assertEquals(new PhoneNumber('+79001112233'), $result->workPhone);
        $this->assertEquals(new PhoneNumber('+79004445566'), $result->personalPhone);
        $this->assertEquals(new Email('new@example.com'), $result->workEmail);
        $this->assertEquals(new DateTimeImmutable('2025-01-01'), $result->hireDate);
        $this->assertEquals(new DateTimeImmutable('1995-01-01'), $result->birthDate);
        $this->assertSame('new_telegram', $result->telegramChatId);
        $this->assertSame('new_max', $result->maxChatId);
        $this->assertSame('Новые заметки', $result->notes);
        $this->assertFalse(!$result->isActive);
    }

    public function test_throws_access_denied_when_missing_permission(): void
    {
        $existing = $this->createExistingStaff();
        $this->staffRepo->shouldReceive('findById')->with(1)->andReturn($existing);
        $this->staffRepo->shouldNotReceive('save');

        // Запрещаем edit
        $permission = $this->mockUserPermission();

        $dto = new StaffUpdateData(
            lastName: 'Иванов',
            firstName: 'Иван',
            position: 'Без прав',
        );

        $this->expectException(AccessDeniedException::class);
        $this->useCase->execute(1, $dto, $permission);
    }

    /**
     * @throws \DateMalformedStringException
     */
    public function test_clearing_optional_fields(): void
    {
        $existing = $this->createExistingStaff();

        $this->staffRepo->shouldReceive('findById')->with(1)->andReturn($existing);
        $this->staffRepo->shouldReceive('save')->once()->andReturn($existing);

        $dto = new StaffUpdateData(
            lastName: 'Иванов',
            firstName: 'Иван',
            position: 'Должность',
            middleName: 'Иванович',
        );

        $permission = $this->mockUserPermission(edit: true);
        $result = $this->useCase->execute(1, $dto, $permission);

        $this->assertNull($result->department);
        $this->assertNull($result->workPhone);
        $this->assertNull($result->personalPhone);
        $this->assertNull($result->workEmail);
        $this->assertNull($result->hireDate);
        $this->assertNull($result->birthDate);
        $this->assertNull($result->telegramChatId);
        $this->assertNull($result->maxChatId);
        $this->assertNull($result->notes);
    }

    public function test_terminate_and_rehire(): void
    {
        $existing = $this->createExistingStaff();
        $existing->rehire();

        $this->staffRepo->shouldReceive('findById')->with(1)->andReturn($existing);
        $this->staffRepo->shouldReceive('save')->once()->andReturn($existing);

        $dto = new StaffUpdateData(
            lastName: 'Иванов',
            firstName: 'Иван',
            position: 'Должность',
            middleName: null,
            terminated: true,
        );

        $permission = $this->mockUserPermission(edit: true);
        $result = $this->useCase->execute(1, $dto, $permission);
        $this->assertTrue(!$result->isActive);
        $this->assertInstanceOf(DateTimeImmutable::class, $result->terminationDate);

        $dto2 = new StaffUpdateData(
            lastName: 'Иванов',
            firstName: 'Иван',
            position: 'Должность',
            middleName: null,
            terminated: false,
        );

        $this->staffRepo->shouldReceive('findById')->with(1)->andReturn($result);
        $this->staffRepo->shouldReceive('save')->once()->andReturn($result);

        $result2 = $this->useCase->execute(1, $dto2, $permission);
        $this->assertFalse(!$result2->isActive);
        $this->assertNull($result2->terminationDate);
    }

    public function test_throws_exception_if_staff_not_found(): void
    {
        $this->staffRepo->shouldReceive('findById')
            ->with(999)
            ->andReturn(null);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Сотрудник не найден');

        $dto = new StaffUpdateData(
            lastName: 'Иванов',
            firstName: 'Иван',
            position: 'Должность',
            middleName: null,
        );

        $permission = $this->mockUserPermission(edit: true);
        $this->useCase->execute(999, $dto, $permission);
    }
}
