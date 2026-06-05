<?php

namespace App\Modules\Auth\Tests\Unit\Domain\Entities;

use App\Modules\Auth\Domain\Entities\UserEntity;
use App\Modules\Auth\Domain\Services\PasswordHasherInterface;
use App\Modules\Auth\Domain\ValueObjects\Email;
use App\Modules\Auth\Domain\ValueObjects\HashedPassword;
use App\Modules\Auth\Domain\ValueObjects\ProfileType;
use DateTimeImmutable;
use Mockery;
use PHPUnit\Framework\TestCase;
//use Tests\TestCase;
use Illuminate\Support\Facades\Hash;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\Test;

class UserTest extends TestCase
{
    private Email $email;
    private HashedPassword $password;
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
  /*      Hash::shouldReceive('make')
            ->andReturnUsing(fn($plain) => 'hashed_' . $plain);
        Hash::shouldReceive('check')
            ->andReturnUsing(fn($plain, $hash) => $hash === 'hashed_' . $plain);
*/
        $this->email = new Email('test@example.com');
        $this->password = HashedPassword::fromPlainText('password123', $this->passwordHasher);
    }

    protected function tearDown(): void
    {
        \Illuminate\Support\Facades\Facade::clearResolvedInstance('hash');
        Mockery::close();
        parent::tearDown();
    }

    #[Test]
    public function it_can_validate_password(): void
    {
        $user = new UserEntity($this->email, $this->password);

        $this->assertTrue($user->validatePassword('password123', $this->passwordHasher));
        $this->assertFalse($user->validatePassword('wrongpassword', $this->passwordHasher));
    }

    #[Test]
    public function test_it_can_update_password(): void
    {
        $user = new UserEntity($this->email, $this->password);
        $oldHash = $user->getPasswordHash();

        $newPassword = HashedPassword::fromPlainText('new_secure_password', $this->passwordHasher);
        $user->updatePassword($newPassword);

        $this->assertNotEquals($oldHash, $user->getPasswordHash());
        $this->assertEquals('hashed_new_secure_password', $user->getPasswordHash());
    }

    #[Test]
    public function it_can_set_and_verify_email(): void
    {
        $user = new UserEntity($this->email, $this->password);
        $this->assertSame('test@example.com', $user->email->value);

        $newEmail = new Email('new@example.com');
        $user->email = $newEmail;
        $this->assertSame('new@example.com', $user->email->value);
    }



    #[Test]
    public function it_can_be_created_with_minimum_required_fields(): void
    {
        $user = new UserEntity($this->email, $this->password);


        $this->assertTrue($this->email->equals($user->email));
        $this->assertFalse($user->isEmailVerified());
        $this->assertNull($user->id);
        $this->assertNull($user->profileableType);
        $this->assertNull($user->profileableId);
        $this->assertEmpty($user->roles);
    }

    #[Test]
    public function it_can_set_and_get_id(): void
    {
        $user = new UserEntity( $this->email, $this->password);
        $user->id = 42;

        $this->assertEquals(42, $user->id);
    }

    #[Test]
    public function it_can_verify_email(): void
    {
        $user = new UserEntity( $this->email, $this->password);
        $this->assertFalse($user->isEmailVerified());

        $user->verifyEmail();
        $this->assertTrue($user->isEmailVerified());
        $this->assertInstanceOf(DateTimeImmutable::class, $user->emailVerifiedAt);
    }

    #[Test]
    public function it_can_set_email_verified_at_manually(): void
    {
        $user = new UserEntity( $this->email, $this->password);
        $date = new DateTimeImmutable('2025-01-01 12:00:00');
        $user->emailVerifiedAt = $date;

        $this->assertTrue($user->isEmailVerified());
        $this->assertEquals($date, $user->emailVerifiedAt);
    }



    #[Test]
    public function it_can_set_and_get_remember_token(): void
    {
        $user = new UserEntity( $this->email, $this->password);
        $this->assertNull($user->rememberToken);

        $token = 'some_random_token';
        $user->rememberToken = $token;
        $this->assertEquals($token, $user->rememberToken);
    }

    #[Test]
    public function it_can_set_profileable_relation(): void
    {
        $user = new UserEntity( $this->email, $this->password);
        $user->setProfile(ProfileType::STAFF, 100);

        $this->assertEquals(ProfileType::STAFF, $user->profileableType);
        $this->assertEquals(100, $user->profileableId);
    }

    #[Test]
    public function it_can_manage_roles(): void
    {
        $user = new UserEntity( $this->email, $this->password);
        $roles = ['admin', 'client'];
        $user->roles = $roles;

        $this->assertEquals($roles, $user->roles);
    }

    #[Test]
    public function it_can_check_if_has_specific_role(): void
    {
        $user = new UserEntity( $this->email, $this->password);
        $user->roles = ['admin', 'client'];
        $this->assertTrue($user->hasRole( 'admin'));
        $this->assertTrue($user->hasRole('client'));
        $this->assertFalse($user->hasRole('editor'));
    }

    #[Test]
    public function it_can_check_if_admin(): void
    {
        $user = new UserEntity( $this->email, $this->password);
        $this->assertFalse($user->isAdmin());
        $user->roles = ['admin'];
        $this->assertTrue($user->isAdmin());
    }

    #[Test]
    public function it_throws_exception_when_creating_with_invalid_email(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new Email('invalid-email');
    }

    #[Test]
    public function it_throws_exception_when_creating_with_short_password(): void
    {
        $this->expectException(InvalidArgumentException::class);
        HashedPassword::fromPlainText('short', $this->passwordHasher);
    }
}
