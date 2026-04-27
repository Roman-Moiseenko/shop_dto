<?php

namespace App\Modules\Auth\Tests\Unit\Application\Actions;
use App\Modules\Auth\Application\Actions\User\RegisterFreelanceUserUseCase;
use App\Modules\Auth\Application\DTOs\UpdateUserData;
use App\Modules\Auth\Application\Interfaces\UserRepositoryInterface;
use App\Modules\Auth\Domain\Entities\UserEntity;
use App\Modules\Auth\Domain\ValueObjects\Email;
use App\Modules\Auth\Infrastructure\Exceptions\UserAlreadyExistsException;
use App\Modules\Auth\Infrastructure\Models\Freelance;
use PHPUnit\Framework\TestCase;
use Mockery;
use InvalidArgumentException;
use Illuminate\Support\Facades\Hash;
class RegisterFreelanceUserUseCaseTest extends TestCase
{
    private UserRepositoryInterface $userRepo;
    private RegisterFreelanceUserUseCase $useCase;

    protected function setUp(): void
    {
        parent::setUp();

        // Изолируем фасад Hash
        Hash::shouldReceive('make')
            ->andReturn('$2y$10$mockedhashvalue');

        $this->userRepo = Mockery::mock(UserRepositoryInterface::class);
        $this->useCase = new RegisterFreelanceUserUseCase($this->userRepo);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    /** @test */
    public function it_creates_user_with_valid_roles(): void
    {
        $dto = new UpdateUserData(
            active: true,
            email: 'freelancer@example.com',
            password: 'password123',
            roleNames: ['editor', 'moderator']
        );
        $freelanceId = 5;

        $this->userRepo->shouldReceive('emailExists')
            ->once()
            ->with(Mockery::on(fn(Email $e) => $e->value === 'freelancer@example.com'))
            ->andReturn(false);

        $this->userRepo->shouldReceive('save')
            ->once()
            ->with(Mockery::type(UserEntity::class))
            ->andReturnUsing(function (UserEntity $user) {
                $user->id = 30;
                return $user;
            });

        $result = $this->useCase->execute($freelanceId, $dto);

        $this->assertEquals(30, $result->id);
        $this->assertEquals('freelancer@example.com', $result->email->value);
        $this->assertSame('$2y$10$mockedhashvalue', $result->getPasswordHash());
        $this->assertEquals(['editor', 'moderator'], $result->roles);
        $this->assertEquals(Freelance::class, $result->profileableType);
        $this->assertEquals($freelanceId, $result->profileableId);
    }

    /** @test */
    public function it_throws_exception_when_roles_are_empty(): void
    {
        $dto = new UpdateUserData(
            active: true,
            email: 'freelancer@example.com',
            password: 'password123',
            roleNames: []
        );
        $freelanceId = 1;

        // emailExists вызывается до проверки ролей
        $this->userRepo->shouldReceive('emailExists')->once()->andReturn(false);
        $this->userRepo->shouldNotReceive('save');

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Роли пользователя не определены');

        $this->useCase->execute($freelanceId, $dto);
    }

    /** @test */
    public function it_throws_exception_when_client_role_is_included(): void
    {
        $dto = new UpdateUserData(
            active: true,
            email: 'freelancer@example.com',
            password: 'password123',
            roleNames: ['editor', 'client']
        );
        $freelanceId = 2;

        $this->userRepo->shouldReceive('emailExists')->once()->andReturn(false);
        $this->userRepo->shouldNotReceive('save');

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Нельзя назначить роль client');

        $this->useCase->execute($freelanceId, $dto);
    }

    /** @test */
    public function it_throws_exception_if_email_already_exists(): void
    {
        $dto = new UpdateUserData(
            active: true,
            email: 'existing@example.com',
            password: 'password123',
            roleNames: ['editor']
        );

        $this->userRepo->shouldReceive('emailExists')
            ->once()
            ->with(Mockery::on(fn(Email $e) => $e->value === 'existing@example.com'))
            ->andReturn(true);
        $this->userRepo->shouldNotReceive('save');

        $this->expectException(UserAlreadyExistsException::class);
        $this->expectExceptionMessage('Пользователь с email existing@example.com уже существует');

        $this->useCase->execute(1, $dto);
    }
}
