<?php

namespace App\Modules\Auth\Tests\Unit\Application\Actions;
use App\Modules\Auth\Application\Actions\User\UpdateUserUseCase;
use App\Modules\Auth\Application\DTOs\UpdateUserData;
use App\Modules\Auth\Application\Interfaces\UserRepositoryInterface;
use App\Modules\Auth\Domain\Entities\UserEntity;
use App\Modules\Auth\Domain\ValueObjects\Email;
use App\Modules\Auth\Domain\ValueObjects\HashedPassword;
use App\Modules\Auth\Domain\ValueObjects\RoleName;
use App\Modules\Auth\Infrastructure\Exceptions\UserAlreadyExistsException;
use Illuminate\Support\Facades\Hash;
use Mockery;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class UpdateUserUseCaseTest extends TestCase
{
    private UserRepositoryInterface $userRepo;
    private UpdateUserUseCase $useCase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->userRepo = Mockery::mock(UserRepositoryInterface::class);
        $this->useCase = new UpdateUserUseCase($this->userRepo);

        Hash::shouldReceive('make')->andReturn('$2y$10$mockedhashvalue');
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    /** Создаёт клиента (роль CLIENT) */
    private function createClientUser(int $id = 10): UserEntity
    {
        $user = new UserEntity(
            new Email('client@example.com'),
            HashedPassword::fromPlainText('clientpass')
        );
        $user->id = $id;
        $user->roles = [RoleName::CLIENT];
        return $user;
    }

    /** Создаёт сотрудника (роль staff) */
    private function createStaffUser(int $id = 20): UserEntity
    {
        $user = new UserEntity(
            new Email('staff@example.com'),
            HashedPassword::fromPlainText('staffpass')
        );
        $user->id = $id;
        $user->roles = ['staff'];
        return $user;
    }

    // ---------- Тесты ----------

    public function test_updates_email_and_password_for_client_without_changing_roles(): void
    {
        $staffId = 1;
        $user = $this->createClientUser();

        $this->userRepo->shouldReceive('findByStaffId')
            ->with($staffId)->once()->andReturn($user);
        $this->userRepo->shouldReceive('emailExists')
            ->once()->with(Mockery::on(fn(Email $e) => $e->value === 'new@example.com'), $user->id)
            ->andReturn(false);
        $this->userRepo->shouldReceive('save')
            ->once()->with($user)->andReturn($user);

        $dto = new UpdateUserData(
            active: true,
            email: 'new@example.com',
            password: 'newpassword',
            roleNames: ['admin']   // попытка сменить роль игнорируется
        );

        $result = $this->useCase->execute($staffId, $dto);

        $this->assertEquals('new@example.com', $result->email->value);
        $this->assertSame('$2y$10$mockedhashvalue', $result->getPasswordHash());
        // Роли остались неизменны
        $this->assertEquals([RoleName::CLIENT], $result->roles);
    }

    public function test_client_role_cannot_be_changed(): void
    {
        $staffId = 1;
        $user = $this->createClientUser();

        $this->userRepo->shouldReceive('findByStaffId')->once()->andReturn($user);
        $this->userRepo->shouldReceive('emailExists')->once()->andReturn(false);
        $this->userRepo->shouldReceive('save')->once()->andReturn($user);

        $dto = new UpdateUserData(
            active: true,
            email: 'client@example.com',
            password: 'clientpass',
            roleNames: ['staff', 'editor']
        );

        $result = $this->useCase->execute($staffId, $dto);
        $this->assertEquals([RoleName::CLIENT], $result->roles);
    }

    public function test_throws_exception_when_roles_empty_for_non_client(): void
    {
        $staffId = 2;
        $user = $this->createStaffUser();

        $this->userRepo->shouldReceive('findByStaffId')->once()->andReturn($user);

        $this->userRepo->shouldReceive('emailExists')
            ->once()
            ->with(Mockery::on(fn(Email $e) => $e->value === 'staff@example.com'), $user->id)
            ->andReturn(false);
        $this->userRepo->shouldReceive('save')->never();

        $dto = new UpdateUserData(
            active: true,
            email: 'staff@example.com',
            password: 'staffpass',
            roleNames: []
        );

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Роли пользователя не определены');

        $this->useCase->execute($staffId, $dto);
    }

    public function test_throws_exception_when_assigning_client_role_to_non_client(): void
    {
        $staffId = 2;
        $user = $this->createStaffUser();

        $this->userRepo->shouldReceive('findByStaffId')->once()->andReturn($user);
        $this->userRepo->shouldReceive('emailExists')
            ->once()
            ->with(Mockery::on(fn(Email $e) => $e->value === 'staff@example.com'), $user->id)
            ->andReturn(false);
        $this->userRepo->shouldReceive('save')->never();

        $dto = new UpdateUserData(
            active: true,
            email: 'staff@example.com',
            password: 'staffpass',
            roleNames: ['editor', 'client']
        );

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Нельзя назначить роль client');

        $this->useCase->execute($staffId, $dto);
    }

    public function test_updates_roles_for_non_client(): void
    {
        $staffId = 2;
        $user = $this->createStaffUser();

        $this->userRepo->shouldReceive('findByStaffId')->once()->andReturn($user);
        $this->userRepo->shouldReceive('emailExists')->once()->andReturn(false);
        $this->userRepo->shouldReceive('save')->once()->andReturn($user);

        $dto = new UpdateUserData(
            active: true,
            email: 'staff@example.com',
            password: 'staffpass',
            roleNames: ['editor', 'moderator']
        );

        $result = $this->useCase->execute($staffId, $dto);
        $this->assertEquals(['editor', 'moderator'], $result->roles);
    }

    public function test_ban_and_unban_user(): void
    {
        $staffId = 1;
        $user = $this->createClientUser();

        // Блокировка
        $this->userRepo->shouldReceive('findByStaffId')->once()->andReturn($user);
        $this->userRepo->shouldReceive('emailExists')->once()->andReturn(false);
        $this->userRepo->shouldReceive('save')->once()->andReturn($user);

        $dto = new UpdateUserData(
            active: false,
            email: 'client@example.com',
            password: 'clientpass',
            roleNames: []   // для client роли не трогаем
        );
        $result = $this->useCase->execute($staffId, $dto);
        $this->assertTrue($result->isBanned);

        // Разблокировка
        $this->userRepo->shouldReceive('findByStaffId')->once()->andReturn($user);
        $this->userRepo->shouldReceive('emailExists')->once()->andReturn(false);
        $this->userRepo->shouldReceive('save')->once()->andReturn($user);

        $dto2 = new UpdateUserData(
            active: true,
            email: 'client@example.com',
            password: 'clientpass',
            roleNames: []
        );
        $result2 = $this->useCase->execute($staffId, $dto2);
        $this->assertFalse($result2->isBanned);
    }

    public function test_throws_exception_if_user_not_found(): void
    {
        $this->userRepo->shouldReceive('findByStaffId')
            ->with(999)->once()->andReturn(null);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Пользователь не найден');

        $this->useCase->execute(999, new UpdateUserData(
            active: true,
            email: 'any@example.com',
            password: 'pass123456',
            roleNames: []
        ));
    }

    public function test_throws_exception_if_email_exists_for_another_user(): void
    {
        $staffId = 1;
        $user = $this->createClientUser();

        $this->userRepo->shouldReceive('findByStaffId')->once()->andReturn($user);
        $this->userRepo->shouldReceive('emailExists')
            ->once()
            ->with(Mockery::on(fn(Email $e) => $e->value === 'exists@example.com'), $user->id)
            ->andReturn(true);

        $dto = new UpdateUserData(
            active: true,
            email: 'exists@example.com',
            password: 'password123',
            roleNames: []
        );

        $this->expectException(UserAlreadyExistsException::class);
        $this->expectExceptionMessage('Email exists@example.com уже занят');

        $this->useCase->execute($staffId, $dto);
    }
}
