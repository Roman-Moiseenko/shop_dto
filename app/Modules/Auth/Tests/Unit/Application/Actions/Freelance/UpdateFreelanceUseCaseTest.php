<?php

namespace App\Modules\Auth\Tests\Unit\Application\Actions\Freelance;
use App\Modules\Auth\Application\Actions\Freelance\UpdateFreelanceUseCase;
use App\Modules\Auth\Application\DTOs\Freelance\FreelanceUpdateData;
use App\Modules\Auth\Application\Interfaces\FreelanceRepositoryInterface;
use App\Modules\Auth\Domain\Entities\FreelanceEntity;
use App\Modules\Auth\Domain\ValueObjects\Email;
use App\Modules\Auth\Domain\ValueObjects\FullName;
use App\Modules\Auth\Domain\ValueObjects\PhoneNumber;
use App\Modules\Shared\Domain\Exceptions\AccessDeniedException;
use DateTimeImmutable;
use InvalidArgumentException;
use Mockery;
use PHPUnit\Framework\TestCase;
use Tests\Trait\MockPermission;

class UpdateFreelanceUseCaseTest extends TestCase
{
    use MockPermission;
    private FreelanceRepositoryInterface $freelanceRepo;
    private UpdateFreelanceUseCase $useCase;
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
        parent::setUp();
        $this->freelanceRepo = Mockery::mock(FreelanceRepositoryInterface::class);
        $this->useCase = new UpdateFreelanceUseCase($this->freelanceRepo);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    private function createExistingFreelance(): FreelanceEntity
    {
        $freelance = new FreelanceEntity(
            new FullName('Иванов Иван Иванович'),
            'Старая должность'
        );
        $freelance->id = 1;
        $freelance->personalPhone = new PhoneNumber('+79001234567');
        $freelance->personalEmail = new Email('old@example.com');
        $freelance->hireDate = new DateTimeImmutable('2020-01-01');
        $freelance->telegramChatId = 'old_telegram';
        $freelance->maxChatId = 'old_max';
        $freelance->notes = 'Старые заметки';
        return $freelance;
    }

    public function test_updates_all_fields(): void
    {
        $existing = $this->createExistingFreelance();

        $this->freelanceRepo->shouldReceive('findById')
            ->with(1)
            ->andReturn($existing);
        $this->freelanceRepo->shouldReceive('save')
            ->once()
            ->with(Mockery::type(FreelanceEntity::class))
            ->andReturn($existing);

        $dto = new FreelanceUpdateData(
            lastName: 'Петров',
            firstName: 'Пётр',
            position: 'Новая должность',
            middleName: 'Петрович',
            personalPhone: '+79001112233',
            personalEmail: 'new@example.com',
            hireDate: '2025-01-01',
            telegramChatId: 'new_telegram',
            maxChatId: 'new_max',
            notes: 'Новые заметки',
            terminated: false,
        );
        $permission = $this->mockUserPermission(edit: true);
        $result = $this->useCase->execute(1, $dto, $permission);

        $this->assertSame('Петров Пётр Петрович', (string) $result->fullName);
        $this->assertSame('Новая должность', $result->position);
        $this->assertEquals(new PhoneNumber('+79001112233'), $result->personalPhone);
        $this->assertEquals(new Email('new@example.com'), $result->personalEmail);
        $this->assertEquals(new DateTimeImmutable('2025-01-01'), $result->hireDate);
        $this->assertSame('new_telegram', $result->telegramChatId);
        $this->assertSame('new_max', $result->maxChatId);
        $this->assertSame('Новые заметки', $result->notes);
        $this->assertTrue($result->isActive);
        $this->assertNull($result->terminationDate);
    }

    public function test_throws_access_denied_when_missing_permission(): void
    {
        $existing = $this->createExistingFreelance();
        $this->freelanceRepo->shouldReceive('findById')->with(1)->andReturn($existing);
        $this->freelanceRepo->shouldNotReceive('save');

        // Запрещаем edit
        $permission = $this->mockUserPermission();

        $dto = new FreelanceUpdateData(
            lastName: 'Иванов',
            firstName: 'Иван',
            position: 'Без прав',
        );

        $this->expectException(AccessDeniedException::class);
        $this->useCase->execute(1, $dto, $permission);
    }

    public function test_clearing_optional_fields(): void
    {
        $existing = $this->createExistingFreelance();

        $this->freelanceRepo->shouldReceive('findById')->with(1)->andReturn($existing);
        $this->freelanceRepo->shouldReceive('save')->once()->andReturn($existing);

        $dto = new FreelanceUpdateData(
            lastName: 'Иванов',
            firstName: 'Иван',
            position: 'Должность',
            middleName: null,
        // все остальные поля по умолчанию null
        );
        $permission = $this->mockUserPermission(edit: true);
        $result = $this->useCase->execute(1, $dto, $permission);

        $this->assertNull($result->personalPhone);
        $this->assertNull($result->personalEmail);
        $this->assertNull($result->hireDate);
        $this->assertNull($result->telegramChatId);
        $this->assertNull($result->maxChatId);
        $this->assertNull($result->notes);
    }

    public function test_terminate_and_rehire(): void
    {
        $existing = $this->createExistingFreelance();
        $existing->rehire(); // убедимся, что активен

        $this->freelanceRepo->shouldReceive('findById')->with(1)->andReturn($existing);
        $this->freelanceRepo->shouldReceive('save')->once()->andReturn($existing);

        $dto = new FreelanceUpdateData(
            lastName: 'Иванов',
            firstName: 'Иван',
            position: 'Должность',
            terminated: true,
        );
        $permission = $this->mockUserPermission(edit: true);
        $result = $this->useCase->execute(1, $dto, $permission);
        $this->assertFalse($result->isActive);
        $this->assertInstanceOf(DateTimeImmutable::class, $result->terminationDate);

        // rehire
        $existing->rehire(); // сбросим для следующего шага
        $this->freelanceRepo->shouldReceive('findById')->with(1)->andReturn($existing);
        $this->freelanceRepo->shouldReceive('save')->once()->andReturn($existing);

        $dto2 = new FreelanceUpdateData(
            lastName: 'Иванов',
            firstName: 'Иван',
            position: 'Должность',
            terminated: false,
        );

        $result2 = $this->useCase->execute(1, $dto2, $permission);
        $this->assertTrue($result2->isActive);
        $this->assertNull($result2->terminationDate);
    }

    public function test_throws_exception_if_freelance_not_found(): void
    {
        $this->freelanceRepo->shouldReceive('findById')
            ->with(999)
            ->andReturn(null);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Сотрудник не найден');

        $dto = new FreelanceUpdateData(
            lastName: 'Иванов',
            firstName: 'Иван',
            position: 'Должность',
        );
        $permission = $this->mockUserPermission(edit: true);
        $this->useCase->execute(999, $dto, $permission);
    }

}
