<?php

namespace App\Modules\Auth\Tests\Unit\Application\Actions;

use App\Modules\Auth\Application\Actions\Staff\CreateStaffUseCase;
use App\Modules\Auth\Application\DTOs\Staff\StaffCreateData;
use App\Modules\Auth\Application\Interfaces\StaffRepositoryInterface;
use App\Modules\Auth\Domain\Entities\StaffEntity;
use App\Modules\Auth\Domain\ValueObjects\FullName;
use Mockery;
use PHPUnit\Framework\TestCase;


class CreateStaffUseCaseTest extends TestCase
{
    private StaffRepositoryInterface $staffRepo;
    private CreateStaffUseCase $useCase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->staffRepo = Mockery::mock(StaffRepositoryInterface::class);
        $this->useCase = new CreateStaffUseCase($this->staffRepo);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_creates_staff_from_dto_and_saves(): void
    {
        $dto = new StaffCreateData(
            lastName: 'Иванов',
            firstName: 'Иван',
            position: 'Разработчик',
            middleName: 'Иванович',
        );

        $this->staffRepo->shouldReceive('save')
            ->once()
            ->with(Mockery::type(StaffEntity::class))
            ->andReturnUsing(function (StaffEntity $staff) {
                $staff->id = 42;
                return $staff;
            });

        $result = $this->useCase->execute($dto);

        $this->assertInstanceOf(StaffEntity::class, $result);
        $this->assertEquals(42, $result->id);
        $fullName = $result->fullName; // предположим, что геттер существует
        $this->assertInstanceOf(FullName::class, $fullName);
        $this->assertSame('Иванов Иван Иванович', (string) $fullName);
        $this->assertSame('Разработчик', $result->position);
    }

    /**
     * @throws \Throwable
     */
    public function test_creates_staff_without_middle_name(): void
    {
        $dto = new StaffCreateData(
            lastName: 'Петров',
            firstName: 'Пётр',
            position: 'Менеджер',
            middleName: null,
        );

        $this->staffRepo->shouldReceive('save')
            ->once()
            ->with(Mockery::type(StaffEntity::class))
            ->andReturnUsing(function (StaffEntity $staff) {
                $staff->id = 1;
                return $staff;
            });

        $result = $this->useCase->execute($dto);

        $this->assertSame('Петров Пётр', (string) $result->fullName);
        $this->assertNull($result->fullName->getMiddleName());
    }

    public function test_propagates_exception_from_repository(): void
    {
        $dto = new StaffCreateData(
            lastName: 'Иванов',
            firstName: 'Иван',
            position: 'Разработчик',
            middleName: null,
        );

        $this->staffRepo->shouldReceive('save')
            ->once()
            ->andThrow(new \RuntimeException('DB error'));

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('DB error');

        $this->useCase->execute($dto);
    }
}
