<?php

namespace App\Modules\Auth\Tests\Unit\Domain\ValueObjects;
use App\Modules\Auth\Domain\ValueObjects\HashedPassword;
use InvalidArgumentException;
use Mockery;
use PHPUnit\Framework\TestCase;
use Illuminate\Support\Facades\Hash;
class HashedPasswordTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
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

    public function test_from_plain_text_creates_hashed_password(): void
    {
        $password = HashedPassword::fromPlainText('secret123');
        $this->assertSame('$2y$10$mockedhashvalue', $password->getHash());
    }

    public function test_from_hash_stores_hash_directly(): void
    {
        $password = HashedPassword::fromHash('$2y$10$customhash');
        $this->assertSame('$2y$10$customhash', $password->getHash());
    }

    public function test_verify_correct_password_returns_true(): void
    {
        $password = HashedPassword::fromHash('$2y$10$mockedhashvalue');
        $this->assertTrue($password->verify('secret123'));
    }

    public function test_verify_wrong_password_returns_false(): void
    {
        $password = HashedPassword::fromHash('$2y$10$mockedhashvalue');
        $this->assertFalse($password->verify('wrongpassword'));
    }

    public function test_throws_exception_when_password_too_short(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Пароль должен содержать минимум 8 символов');
        HashedPassword::fromPlainText('short');
    }
}
