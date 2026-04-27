<?php

namespace App\Modules\Auth\Tests\Unit\Application\Actions;
use App\Modules\Auth\Application\Actions\User\RegisterStaffUserUseCase;
use App\Modules\Auth\Application\DTOs\UpdateUserData;
use App\Modules\Auth\Application\Interfaces\UserRepositoryInterface;
use App\Modules\Auth\Domain\Entities\UserEntity;
use App\Modules\Auth\Domain\ValueObjects\Email;
use App\Modules\Auth\Domain\ValueObjects\RoleName;
use App\Modules\Auth\Infrastructure\Exceptions\UserAlreadyExistsException;
use App\Modules\Auth\Infrastructure\Models\Staff;

use Illuminate\Support\Facades\Hash;
use Mockery;
//use Tests\TestCase;
use PHPUnit\Framework\TestCase;
class RegisterStaffUserUseCaseTest extends TestCase
{
    private UserRepositoryInterface $userRepo;
    private RegisterStaffUserUseCase $useCase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->userRepo = Mockery::mock(UserRepositoryInterface::class);
        $this->useCase = new RegisterStaffUserUseCase($this->userRepo);
        Hash::shouldReceive('make')
            ->andReturn('$2y$10$mockedhashvalue');
        Hash::shouldReceive('check')
            ->andReturnUsing(function ($plain, $hash) {
                return $plain === 'secret123' && $hash === '$2y$10$mockedhashvalue';
            });
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_creates_user_with_default_role_when_roles_are_empty(): void
    {
        $dto = new UpdateUserData(
            active: true,
            email: 'staff@example.com',
            password: 'password123',
            roleNames: []
        );
        $staffId = 1;

        $this->userRepo->shouldReceive('emailExists')
            ->once()
            ->with(Mockery::on(fn(Email $email) => $email->value === 'staff@example.com'))
            ->andReturn(false);

        $this->userRepo->shouldReceive('save')
            ->once()
            ->with(Mockery::type(UserEntity::class))
            ->andReturnUsing(function (UserEntity $user) {
                $user->id = 42;
                return $user;
            });

        $user = $this->useCase->execute($staffId, $dto);

        $this->assertEquals(42, $user->id);
        $this->assertEquals([RoleName::CLIENT], $user->roles);
        $this->assertEquals(Staff::class, $user->profileableType);
        $this->assertEquals($staffId, $user->profileableId);
    }

    public function test_creates_user_with_specified_roles(): void
    {
        $dto = new UpdateUserData(
            active: true,
            email: 'staff2@example.com',
            password: 'password123',
            roleNames: ['staff', 'editor']
        );
        $staffId = 2;

        $this->userRepo->shouldReceive('emailExists')->once()->andReturn(false);
        $this->userRepo->shouldReceive('save')->once()->andReturnUsing(function (UserEntity $user) {
            $user->id = 99;
            return $user;
        });

        $user = $this->useCase->execute($staffId, $dto);

        $this->assertEquals(['staff', 'editor'], $user->roles);
    }

    public function test_throws_exception_if_email_already_exists(): void
    {
        $dto = new UpdateUserData(
            active: true,
            email: 'existing@example.com',
            password: 'password123',
            roleNames: []
        );

        $this->userRepo->shouldReceive('emailExists')
            ->once()
            ->andReturn(true);

        $this->expectException(UserAlreadyExistsException::class);
        $this->expectExceptionMessage('Пользователь с email existing@example.com уже существует');

        $this->useCase->execute(1, $dto);
    }
}
