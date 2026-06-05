<?php

namespace App\Modules\Auth\Tests\Unit\Application\Actions\User;
use App\Modules\Auth\Application\Actions\User\RegisterUserClientUseCase;
use App\Modules\Auth\Application\DTOs\User\RegisterUserData;
use App\Modules\Auth\Application\Interfaces\ClientRepositoryInterface;
use App\Modules\Auth\Application\Interfaces\UserRepositoryInterface;
use App\Modules\Auth\Domain\Entities\ClientEntity;
use App\Modules\Auth\Domain\Entities\UserEntity;
use App\Modules\Auth\Domain\Exceptions\ClientNotFoundException;
use App\Modules\Auth\Domain\Exceptions\UserAlreadyExistsException;
use App\Modules\Auth\Domain\Services\PasswordHasherInterface;
use App\Modules\Auth\Domain\ValueObjects\Email;
use App\Modules\Auth\Domain\ValueObjects\FullName;
use App\Modules\Auth\Domain\ValueObjects\HashedPassword;
use App\Modules\Auth\Domain\ValueObjects\ProfileType;
use App\Modules\Shared\Application\Interfaces\Mail\MailServiceInterface;
use App\Modules\Shared\Domain\Entities\Mail\Recipient;
use App\Modules\Shared\Domain\Exceptions\AccessDeniedException;
use Illuminate\Support\Str;
use Mockery;
use PHPUnit\Framework\TestCase;
use Tests\Trait\MockPermission;

class RegisterUserClientUseCaseTest extends TestCase
{
    use MockPermission;
    private UserRepositoryInterface $userRepo;
    private ClientRepositoryInterface $clientRepo;
    private MailServiceInterface $mailService;
    private RegisterUserClientUseCase $useCase;
    private string $frontendUrl = 'https://example.com';
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
        // alias-моки
        $this->passwordHasher = Mockery::mock(PasswordHasherInterface::class);
        $this->passwordHasher->shouldReceive('make')
            ->andReturnUsing(fn($plain) => 'hashed_' . $plain);
        //Hash::shouldReceive('make')->andReturn('$2y$10$mockedhashvalue');
        $strMock = Mockery::mock('alias:' . Str::class);
        $strMock->shouldReceive('random')->andReturn('verification_token');

        $this->userRepo = Mockery::mock(UserRepositoryInterface::class);
        $this->clientRepo = Mockery::mock(ClientRepositoryInterface::class);
        $this->mailService = Mockery::mock(MailServiceInterface::class);
        $this->useCase = new RegisterUserClientUseCase(
            $this->userRepo,
            $this->clientRepo,
            $this->mailService,
            $this->frontendUrl,
            $this->passwordHasher
        );

    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_creates_user_and_sends_verification_email(): void
    {
        $clientId = 1;
        $dto = new RegisterUserData(email: 'test@example.com', password: 'secret123');

        $clientStub = $this->createMock(ClientEntity::class);
        $this->clientRepo->shouldReceive('findById')->with($clientId)->once()->andReturn($clientStub);

        $this->userRepo->shouldReceive('emailExists')->once()->andReturn(false);
        $this->userRepo->shouldReceive('save')->once()->andReturnUsing(function (UserEntity $user) {
            $user->id = 10;
            return $user;
        });
        $this->userRepo->shouldReceive('saveEmailVerification')->once()->with(
            10,
            Mockery::on(function ($email) {
                return $email instanceof Email && (string)$email === 'test@example.com';
            }),
            'verification_token'
        );

        $this->mailService->shouldReceive('send')->once()->with(
            'auth.verify_email',
            Mockery::on(function ($data) {
                return isset($data['verificationUrl']) && strpos($data['verificationUrl'], 'verification_token') !== false;
            }),
            Mockery::on(fn(Recipient $r) => $r->email === 'test@example.com')
        );
        $permission = $this->mockUserPermission(create: true);
        $user = $this->useCase->execute($clientId, $dto, $permission);

        $this->assertEquals(10, $user->id);
        $this->assertEquals('test@example.com', (string)$user->email);
        $this->assertEquals([ProfileType::CLIENT, $clientId], [$user->profileableType, $user->profileableId]);
        $this->assertEquals(['client'], $user->roles);
    }

    public function test_throws_exception_if_client_not_found(): void
    {
        $this->clientRepo->shouldReceive('findById')->with(99)->once()->andReturn(null);
        $this->expectException(ClientNotFoundException::class);
        $permission = $this->mockUserPermission(create: true);
        $this->useCase->execute(99, new RegisterUserData(email: 'x@x.com', password: '12345678'), $permission);
    }

    public function test_throws_exception_if_email_exists(): void
    {
        $this->clientRepo->shouldReceive('findById')->with(1)->once()->andReturn($this->createMock(ClientEntity::class));
        $this->userRepo->shouldReceive('emailExists')->once()->andReturn(true);
        $this->expectException(UserAlreadyExistsException::class);
        $permission = $this->mockUserPermission(create: true);
        $this->useCase->execute(1, new RegisterUserData(email: 'x@x.com', password: '12345678'), $permission);
    }

    public function test_throws_access_denied_when_authenticated_and_missing_permission(): void
    {
        $clientId = 10;
        // Аутентифицированный пользователь с id=300, но без права auth.user.create
        $permission = $this->mockUserPermission(create: false, id: 300);
        $dto = new RegisterUserData(email: 'client@test.com', password: 'password123');

        $this->clientRepo->shouldNotReceive('save');
        $this->userRepo->shouldNotReceive('save');

        $this->expectException(AccessDeniedException::class);
        $this->useCase->execute($clientId, $dto, $permission);
    }

    public function test_allows_registration_when_not_authenticated(): void
    {
        $clientId = 10;
        $permission = $this->mockUserPermission(id: null);
        $dto = new RegisterUserData(email: 'guest@test.com', password: 'password123');

        // Мок клиента (можно использовать createMockClient, который вернёт сущность)
        $clientStub = new ClientEntity(
            new FullName('Иванов Иван Иванович'),
            new Email('client@example.com')
        );
        $clientStub->id = 10;

        $this->clientRepo->shouldReceive('findById')->with($clientId)->andReturn($clientStub);
        $this->userRepo->shouldReceive('emailExists')->once()->andReturn(false);

        // Создаём реального UserEntity с ID
        $user = new UserEntity(
            new Email('guest@test.com'),
            HashedPassword::fromPlainText('password123', $this->passwordHasher)
        );
        $user->id = 30;

        $this->userRepo->shouldReceive('save')->once()->andReturn($user);
        $this->userRepo->shouldReceive('saveEmailVerification')->once()->with(30, Mockery::any(), Mockery::any());
        $this->mailService->shouldReceive('send')->once()->andReturnNull();

        $this->useCase->execute($clientId, $dto, $permission);
        // Успешно выполнено, исключений нет
        $this->assertTrue(true);
    }

}
