<?php

namespace App\Modules\Auth\Tests\Unit\Domain\ValueObjects;
use App\Modules\Auth\Domain\Services\PasswordHasherInterface;
use App\Modules\Auth\Domain\ValueObjects\HashedPassword;
use Illuminate\Support\Facades\Facade;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
//use Tests\TestCase;
use Illuminate\Support\Facades\Hash;
use Mockery;
class HashedPasswordTest extends TestCase
{
    private PasswordHasherInterface $hasher;
    protected function setUp(): void
    {
        parent::setUp();
        $this->hasher = Mockery::mock(PasswordHasherInterface::class);
        $this->hasher->shouldReceive('make')->andReturnUsing(fn($p) => 'hashed_'.$p);
        $this->hasher->shouldReceive('check')->andReturnUsing(fn($p, $h) => $h === 'hashed_'.$p);
    }

    protected function tearDown(): void
    {
        // Сбрасываем фасад Hash и закрываем Mockery
        Facade::clearResolvedInstance('hash');
        Mockery::close();
        parent::tearDown();
    }

    public function test_from_plain_text_creates_hashed_password(): void
    {
        $password = HashedPassword::fromPlainText('secret123', $this->hasher);
        $this->assertSame('hashed_secret123', $password->getHash());
    }

    public function test_from_hash_stores_hash_directly(): void
    {
        $password = HashedPassword::fromHash('$2y$10$customhash',);
        $this->assertSame('$2y$10$customhash', $password->getHash());
    }

    public function test_verify_correct_password_returns_true(): void
    {
        $password = HashedPassword::fromHash('hashed_secret123');
        $this->assertTrue($password->verify('secret123', $this->hasher));
    }

    public function test_verify_wrong_password_returns_false(): void
    {
        $password = HashedPassword::fromHash('hashed_secret123');
        $this->assertFalse($password->verify('wrongpassword', $this->hasher));
    }

    public function test_throws_exception_when_password_too_short(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Пароль должен содержать минимум 8 символов');
        HashedPassword::fromPlainText('short', $this->hasher);
    }
}

