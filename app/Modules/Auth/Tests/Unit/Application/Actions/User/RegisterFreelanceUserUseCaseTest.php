<?php

namespace App\Modules\Auth\Tests\Unit\Application\Actions\User;
use App\Modules\Auth\Application\Actions\User\RegisterFreelanceUserUseCase;
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

class RegisterFreelanceUserUseCaseTest extends TestCase
{
    use MockPermission;
    private UserRepositoryInterface $userRepo;
    private RegisterFreelanceUserUseCase $useCase;
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
        // Изолируем фасад Hash
        //Hash::shouldReceive('make')->andReturn('$2y$10$mockedhashvalue');

        $this->userRepo = Mockery::mock(UserRepositoryInterface::class);
        $this->useCase = new RegisterFreelanceUserUseCase($this->userRepo, $this->passwordHasher);
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
        $permission = $this->mockUserPermission(create: true);
        $result = $this->useCase->execute($freelanceId, $dto, $permission);

        $this->assertEquals(30, $result->id);
        $this->assertEquals('freelancer@example.com', $result->email->value);
        $this->assertSame('hashed_password123', $result->getPasswordHash());
        $this->assertEquals(['editor', 'moderator', RoleName::STAFF], $result->roles);
        $this->assertEquals(ProfileType::FREELANCE, $result->profileableType);
        $this->assertEquals($freelanceId, $result->profileableId);
    }

    #[Test]
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
        $permission = $this->mockUserPermission(create: true);
        $this->useCase->execute($freelanceId, $dto, $permission);
    }

    #[Test]
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
        $permission = $this->mockUserPermission(create: true);
        $this->useCase->execute($freelanceId, $dto, $permission);
    }

    #[Test]
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
