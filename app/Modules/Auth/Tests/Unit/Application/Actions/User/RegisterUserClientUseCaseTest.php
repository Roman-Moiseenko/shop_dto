<?php

namespace App\Modules\Auth\Tests\Unit\Application\Actions\User;
use App\Modules\Auth\Application\Actions\User\RegisterUserClientUseCase;
use App\Modules\Auth\Application\DTOs\User\RegisterUserData;
use App\Modules\Auth\Application\Interfaces\ClientRepositoryInterface;
use App\Modules\Auth\Application\Interfaces\UserRepositoryInterface;
use App\Modules\Auth\Domain\Entities\ClientEntity;
use App\Modules\Auth\Domain\Entities\UserEntity;
use App\Modules\Auth\Domain\Services\PasswordHasherInterface;
use App\Modules\Auth\Domain\ValueObjects\Email;
use App\Modules\Auth\Infrastructure\Exceptions\ClientNotFoundException;
use App\Modules\Auth\Infrastructure\Exceptions\UserAlreadyExistsException;
use App\Modules\Auth\Infrastructure\Models\Client;
use App\Modules\Shared\Application\Interfaces\Mail\MailServiceInterface;
use App\Modules\Shared\Domain\Entities\Mail\Recipient;
use PHPUnit\Framework\TestCase;
use Mockery;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
class RegisterUserClientUseCaseTest extends TestCase
{
    private UserRepositoryInterface $userRepo;
    private ClientRepositoryInterface $clientRepo;
    private MailServiceInterface $mailService;
    private RegisterUserClientUseCase $useCase;
    private string $frontendUrl = 'https://example.com';
    private PasswordHasherInterface $passwordHasher;
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

        $user = $this->useCase->execute($clientId, $dto);

        $this->assertEquals(10, $user->id);
        $this->assertEquals('test@example.com', (string)$user->email);
        $this->assertEquals([Client::class, $clientId], [$user->profileableType, $user->profileableId]);
        $this->assertEquals(['client'], $user->roles);
    }

    public function test_throws_exception_if_client_not_found(): void
    {
        $this->clientRepo->shouldReceive('findById')->with(99)->once()->andReturn(null);
        $this->expectException(ClientNotFoundException::class);
        $this->useCase->execute(99, new RegisterUserData(email: 'x@x.com', password: '12345678'));
    }

    public function test_throws_exception_if_email_exists(): void
    {
        $this->clientRepo->shouldReceive('findById')->with(1)->once()->andReturn($this->createMock(ClientEntity::class));
        $this->userRepo->shouldReceive('emailExists')->once()->andReturn(true);
        $this->expectException(UserAlreadyExistsException::class);
        $this->useCase->execute(1, new RegisterUserData(email: 'x@x.com', password: '12345678'));
    }
}
