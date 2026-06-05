<?php

namespace App\Modules\Auth\Tests\Unit\Application\Actions\User;
use App\Modules\Auth\Application\Actions\User\ChangeUserCredentialsUseCase;
use App\Modules\Auth\Application\DTOs\User\ChangeUserCredentialsData;
use App\Modules\Auth\Application\Interfaces\UserRepositoryInterface;
use App\Modules\Auth\Domain\Entities\UserEntity;
use App\Modules\Auth\Domain\Exceptions\InvalidCredentialsException;
use App\Modules\Auth\Domain\Exceptions\UserAlreadyExistsException;
use App\Modules\Auth\Domain\Services\PasswordHasherInterface;
use App\Modules\Auth\Domain\ValueObjects\Email;
use App\Modules\Auth\Domain\ValueObjects\HashedPassword;
use App\Modules\Shared\Application\Interfaces\Mail\MailServiceInterface;
use Illuminate\Support\Str;
use Mockery;
use PHPUnit\Framework\TestCase;

class ChangeUserCredentialsUseCaseTest extends TestCase
{
    private UserRepositoryInterface $userRepo;
    private MailServiceInterface $mailService;
    private ChangeUserCredentialsUseCase $useCase;
    private UserEntity $user;
    private string $frontendUrl = 'https://example.com';
    private PasswordHasherInterface $passwordHasher;
    protected function setUp(): void
    {
        parent::setUp();

        $this->passwordHasher = Mockery::mock(PasswordHasherInterface::class);
        $this->passwordHasher->shouldReceive('make')
            ->andReturnUsing(fn($plain) => 'hashed_' . $plain);
        $this->passwordHasher->shouldReceive('check')
            ->andReturnUsing(function ($plain, $hash) {
                return $hash === 'hashed_' . $plain;
            });


        $strMock = Mockery::mock('alias:' . Str::class);
        $strMock->shouldReceive('random')->andReturn('change_token');

        $this->userRepo = Mockery::mock(UserRepositoryInterface::class);
        $this->mailService = Mockery::mock(MailServiceInterface::class);
        $this->useCase = new ChangeUserCredentialsUseCase(
            $this->userRepo,
            $this->mailService,
            $this->frontendUrl,
            $this->passwordHasher
        );

        // Создаем тестового пользователя
        $this->user = new UserEntity(
            new Email('old@example.com'),
            HashedPassword::fromPlainText('correct_pass', $this->passwordHasher),
        );
        $this->user->id = 42;
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_changes_password_only(): void
    {
        $this->userRepo->shouldReceive('findById')->with(42)->once()->andReturn($this->user);
        $this->userRepo->shouldReceive('save')->once()->with($this->user);

        $dto = new ChangeUserCredentialsData(
            currentEmail: 'old@example.com',
            currentPassword: 'correct_pass',
            newEmail: null,
            newPassword: 'new_strong_pw'
        );

        $result = $this->useCase->execute(42, $dto);

        $this->assertEquals('Учётные данные обновлены', $result['message']);
        $this->assertStringContainsString('hashed_new_strong_pw', $this->user->getPasswordHash());
    }

    public function test_initiates_email_change(): void
    {
        $this->userRepo->shouldReceive('findById')->with(42)->once()->andReturn($this->user);
        $this->userRepo->shouldReceive('emailExists')->with(Mockery::any(), 42)->once()->andReturn(false);
        $this->userRepo->shouldReceive('saveEmailVerification')->once()->with(
            42,
            Mockery::on(fn(Email $e) => (string)$e === 'new@example.com'),
            'change_token'
        );
        $this->mailService->shouldReceive('send')->once()->with(
            'auth.verify_email',
            Mockery::on(function ($data) {
                return isset($data['verificationUrl']) && strpos($data['verificationUrl'], 'change_token') !== false;
            }),
            Mockery::on(fn($r) => $r->email === 'new@example.com')
        );

        $dto = new ChangeUserCredentialsData(
            currentEmail: 'old@example.com',
            currentPassword: 'correct_pass',
            newEmail: 'new@example.com'
        );

        $result = $this->useCase->execute(42, $dto);

        $this->assertEquals('На новый email отправлено письмо для подтверждения', $result['message']);
        $this->assertTrue($result['needsEmailConfirmation']);
        $this->assertEquals('old@example.com', (string)$this->user->email); // не изменился
    }

    public function test_throws_on_wrong_password(): void
    {
        $this->userRepo->shouldReceive('findById')->with(42)->once()->andReturn($this->user);
        $dto = new ChangeUserCredentialsData(
            currentEmail: 'old@example.com',
            currentPassword: 'wrong_pass',
            newPassword: 'new_pw'
        );
        $this->expectException(InvalidCredentialsException::class);
        $this->useCase->execute(42, $dto);
    }

    public function test_throws_when_new_email_occupied(): void
    {
        $this->userRepo->shouldReceive('findById')->with(42)->once()->andReturn($this->user);
        $this->userRepo->shouldReceive('emailExists')->once()->andReturn(true);
        $dto = new ChangeUserCredentialsData(
            currentEmail: 'old@example.com',
            currentPassword: 'correct_pass',
            newEmail: 'occupied@example.com'
        );
        $this->expectException(UserAlreadyExistsException::class);
        $this->useCase->execute(42, $dto);
    }
}
