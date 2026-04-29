<?php

namespace App\Modules\Auth\Tests\Unit\Application\Actions\Freelance;
use App\Modules\Auth\Application\Actions\Freelance\CreateFreelanceUseCase;
use App\Modules\Auth\Application\DTOs\Freelance\FreelanceCreateData;
use App\Modules\Auth\Application\Interfaces\FreelanceRepositoryInterface;
use App\Modules\Auth\Domain\Entities\FreelanceEntity;
use Mockery;
use PHPUnit\Framework\TestCase;

class CreateFreelanceUseCaseTest extends TestCase
{
    private FreelanceRepositoryInterface $freelanceRepo;
    private CreateFreelanceUseCase $useCase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->freelanceRepo = Mockery::mock(FreelanceRepositoryInterface::class);
        $this->useCase = new CreateFreelanceUseCase($this->freelanceRepo);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_creates_freelance_with_minimal_data(): void
    {
        $dto = new FreelanceCreateData(
            lastName: 'Иванов',
            firstName: 'Иван',
            position: 'Разработчик',
        );

        $this->freelanceRepo->shouldReceive('save')
            ->once()
            ->with(Mockery::type(FreelanceEntity::class))
            ->andReturnUsing(function (FreelanceEntity $freelance) {
                $freelance->id = 42;
                return $freelance;
            });

        $result = $this->useCase->execute($dto);

        $this->assertInstanceOf(FreelanceEntity::class, $result);
        $this->assertEquals(42, $result->id);
        $this->assertSame('Иванов Иван', (string) $result->fullName);
        $this->assertSame('Разработчик', $result->position);
        $this->assertNull($result->middleName ?? null); // middleName нет в сущности, но это часть FullName
        // Проверим, что FullName содержит правильные части
        $this->assertSame('Иванов', $result->fullName->getLastName());
        $this->assertSame('Иван', $result->fullName->getFirstName());
        $this->assertNull($result->fullName->getMiddleName());
    }

    public function test_creates_freelance_with_middle_name(): void
    {
        $dto = new FreelanceCreateData(
            lastName: 'Петров',
            firstName: 'Пётр',
            position: 'Дизайнер',
            middleName: 'Петрович',
        );

        $this->freelanceRepo->shouldReceive('save')
            ->once()
            ->with(Mockery::type(FreelanceEntity::class))
            ->andReturnUsing(function (FreelanceEntity $freelance) {
                $freelance->id = 10;
                return $freelance;
            });

        $result = $this->useCase->execute($dto);

        $this->assertSame('Петров Пётр Петрович', (string) $result->fullName);
        $this->assertSame('Петров', $result->fullName->getLastName());
        $this->assertSame('Пётр', $result->fullName->getFirstName());
        $this->assertSame('Петрович', $result->fullName->getMiddleName());
        $this->assertSame('Дизайнер', $result->position);
    }

    public function test_propagates_repository_exception(): void
    {
        $dto = new FreelanceCreateData(
            lastName: 'Иванов',
            firstName: 'Иван',
            position: 'Разработчик',
        );

        $this->freelanceRepo->shouldReceive('save')
            ->once()
            ->andThrow(new \RuntimeException('DB error'));

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('DB error');

        $this->useCase->execute($dto);
    }
}
