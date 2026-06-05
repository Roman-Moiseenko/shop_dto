<?php

namespace App\Modules\Auth\Tests\Unit\Application\Actions\User;
use App\Modules\Auth\Application\Actions\User\RegisterStaffUserUseCase;
use App\Modules\Auth\Application\DTOs\User\UpdateUserData;
use App\Modules\Auth\Application\Interfaces\UserRepositoryInterface;
use App\Modules\Auth\Domain\Entities\UserEntity;
use App\Modules\Auth\Domain\Exceptions\UserAlreadyExistsException;
use App\Modules\Auth\Domain\Services\PasswordHasherInterface;
use App\Modules\Auth\Domain\ValueObjects\Email;
use App\Modules\Auth\Domain\ValueObjects\ProfileType;
use App\Modules\Auth\Domain\ValueObjects\RoleName;
use App\Modules\Shared\Domain\Exceptions\AccessDeniedException;
use InvalidArgumentException;
use Mockery;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Tests\Trait\MockPermission;

class RegisterStaffUserUseCaseTest extends TestCase
{
    use MockPermission;
    private UserRepositoryInterface $userRepo;
    private RegisterStaffUserUseCase $useCase;
    private PasswordHasherInterface $passwordHasher;
    function getModuleName(): string
    {
        return  'auth';
    }
    function getEntityName(): string
    {
        return 'user';
    }
    protected function setUp(): void
    {
        parent::setUp();

        $this->passwordHasher = Mockery::mock(PasswordHasherInterface::class);
        $this->passwordHasher->shouldReceive('make')
            ->andReturnUsing(fn($plain) => 'hashed_' . $plain);
        //Hash::shouldReceive('make')->andReturn('$2y$10$mockedhashvalue');
        $this->userRepo = Mockery::mock(UserRepositoryInterface::class);
        $this->useCase = new RegisterStaffUserUseCase($this->userRepo,
            $this->passwordHasher);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    #[Test]
    public function it_creates_user_with_valid_roles(): void
    {
        $dto = new UpdateUserData(
            active: true,
            email: 'staff@example.com',
            password: 'password123',
            roleNames: ['staff', 'editor']
        );
        $staffId = 1;

        $this->userRepo->shouldReceive('emailExists')
            ->once()
            ->with(Mockery::on(fn(Email $e) => $e->value === 'staff@example.com'))
            ->andReturn(false);

        $this->userRepo->shouldReceive('save')
            ->once()
            ->with(Mockery::type(UserEntity::class))
            ->andReturnUsing(function (UserEntity $user) {
                $user->id = 42;
                return $user;
            });
        $permission = $this->mockUserPermission(create: true);
        $result = $this->useCase->execute($staffId, $dto, $permission);

        $this->assertEquals(42, $result->id);
        $this->assertEquals('staff@example.com', $result->email->value);
        $this->assertSame('hashed_password123', $result->getPasswordHash());
        $this->assertEquals(['staff', 'editor'], $result->roles);
        $this->assertEquals(ProfileType::STAFF, $result->profileableType);
        $this->assertEquals($staffId, $result->profileableId);
    }

    #[Test]
    public function it_throws_exception_when_roles_are_empty(): void
    {
        $dto = new UpdateUserData(
            active: true,
            email: 'staff@example.com',
            password: 'password123',
            roleNames: []
        );
        $staffId = 1;

        // emailExists всё ещё вызывается, т.к. проверка до ролей
        $this->userRepo->shouldReceive('emailExists')
            ->once()
            ->with(Mockery::on(fn(Email $e) => $e->value === 'staff@example.com'))
            ->andReturn(false);

        // save не должен вызываться
        $this->userRepo->shouldNotReceive('save');

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Роли пользователя не определены');
        $permission = $this->mockUserPermission(create: true);
        $this->useCase->execute($staffId, $dto, $permission);
    }

    #[Test]
    public function it_throws_exception_when_client_role_is_included(): void
    {
        $dto = new UpdateUserData(
            active: true,
            email: 'staff@example.com',
            password: 'password123',
            roleNames: ['staff', 'client']
        );
        $staffId = 1;

        $this->userRepo->shouldReceive('emailExists')
            ->once()
            ->with(Mockery::on(fn(Email $e) => $e->value === 'staff@example.com'))
            ->andReturn(false);

        $this->userRepo->shouldNotReceive('save');

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Нельзя назначить роль client');
        $permission = $this->mockUserPermission(create: true);
        $this->useCase->execute($staffId, $dto, $permission);
    }

    #[Test]
    public function it_throws_exception_if_email_already_exists(): void
    {
        $dto = new UpdateUserData(
            active: true,
            email: 'existing@example.com',
            password: 'password123',
            roleNames: ['staff']
        );

        $this->userRepo->shouldReceive('emailExists')
            ->once()
            ->with(Mockery::on(fn(Email $e) => $e->value === 'existing@example.com'))
            ->andReturn(true);

        $this->userRepo->shouldNotReceive('save');

        $this->expectException(UserAlreadyExistsException::class);
        $this->expectExceptionMessage('Пользователь с email existing@example.com уже существует');
        $permission = $this->mockUserPermission(create: true);
        $this->useCase->execute(1, $dto, $permission);
    }

    #[Test]
    public function test_throws_access_denied_when_missing_permission(): void
    {
        $freelanceId = 5;
        $permission = $this->mockUserPermission(id: 100); // id не null
        $dto = new UpdateUserData(true, 'free@test.com',  'password123', [RoleName::STAFF]);

        $this->userRepo->shouldNotReceive('save');

        $this->expectException(AccessDeniedException::class);
        $this->useCase->execute($freelanceId, $dto, $permission);
    }
}
