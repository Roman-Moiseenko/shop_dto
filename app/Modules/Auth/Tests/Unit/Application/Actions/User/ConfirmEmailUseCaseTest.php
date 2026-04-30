<?php

namespace App\Modules\Auth\Tests\Unit\Application\Actions\User;
use App\Modules\Auth\Application\Actions\User\ConfirmEmailUseCase;
use App\Modules\Auth\Application\Interfaces\UserRepositoryInterface;
use App\Modules\Auth\Domain\Entities\UserEntity;
use App\Modules\Auth\Domain\ValueObjects\Email;
use App\Modules\Auth\Domain\ValueObjects\HashedPassword;
use PHPUnit\Framework\TestCase;
use Mockery;
use InvalidArgumentException;
class ConfirmEmailUseCaseTest extends TestCase
{
    private UserRepositoryInterface $userRepo;
    private ConfirmEmailUseCase $useCase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->userRepo = Mockery::mock(UserRepositoryInterface::class);
        $this->useCase = new ConfirmEmailUseCase($this->userRepo);
        // подменяем время "сейчас" для проверки срока действия (опционально)
        \Carbon\Carbon::setTestNow('2026-01-01 12:00:00');
    }

    protected function tearDown(): void
    {
        \Carbon\Carbon::setTestNow(null);
        Mockery::close();
        parent::tearDown();
    }

    private function createUser(string $email = 'old@example.com'): UserEntity
    {
        $user = new UserEntity(new Email($email), HashedPassword::fromHash('hash'));
        $user->id = 1;
        return $user;
    }

    public function test_confirms_primary_email(): void
    {
        $token = 'valid_token';
        $user = $this->createUser('user@example.com');
        $verification = (object)[
            'user_id' => 1,
            'new_email' => 'user@example.com',
            'expires_at' => now()->addHour(),
        ];

        $this->userRepo->shouldReceive('findEmailVerificationByToken')->with($token)->once()->andReturn($verification);
        $this->userRepo->shouldReceive('findById')->with(1)->once()->andReturn($user);
        $this->userRepo->shouldReceive('save')->once()->with($user);
        $this->userRepo->shouldReceive('deleteEmailVerification')->once()->with($token);

        $this->useCase->execute($token);

        $this->assertTrue($user->isEmailVerified());
        $this->assertEquals('user@example.com', (string)$user->email);
    }

    public function test_confirms_email_change(): void
    {
        $token = 'change_token';
        $user = $this->createUser('old@example.com');
        $verification = (object)[
            'user_id' => 1,
            'new_email' => 'new@example.com',
            'expires_at' => now()->addHour(),
        ];

        $this->userRepo->shouldReceive('findEmailVerificationByToken')->with($token)->once()->andReturn($verification);
        $this->userRepo->shouldReceive('findById')->with(1)->once()->andReturn($user);
        $this->userRepo->shouldReceive('save')->once()->with($user);
        $this->userRepo->shouldReceive('deleteEmailVerification')->once()->with($token);

        $this->useCase->execute($token);

        $this->assertTrue($user->isEmailVerified());
        $this->assertEquals('new@example.com', (string)$user->email);
    }

    public function test_throws_exception_if_token_expired(): void
    {
        $token = 'expired_token';
        $verification = (object)[
            'user_id' => 1,
            'new_email' => 'any@example.com',
            'expires_at' => now()->subMinute(),
        ];
        $this->userRepo->shouldReceive('findEmailVerificationByToken')->with($token)->once()->andReturn($verification);
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Токен недействителен или срок его действия истёк');
        $this->useCase->execute($token);
    }

    public function test_throws_exception_if_token_not_found(): void
    {
        $this->userRepo->shouldReceive('findEmailVerificationByToken')->with('bad_token')->once()->andReturn(null);
        $this->expectException(InvalidArgumentException::class);
        $this->useCase->execute('bad_token');
    }

    public function test_throws_exception_if_user_not_found(): void
    {
        $token = 'token';
        $verification = (object)['user_id' => 99, 'new_email' => 'x@x.com', 'expires_at' => now()->addHour()];
        $this->userRepo->shouldReceive('findEmailVerificationByToken')->once()->andReturn($verification);
        $this->userRepo->shouldReceive('findById')->with(99)->once()->andReturn(null);
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Пользователь не найден');
        $this->useCase->execute($token);
    }
}
