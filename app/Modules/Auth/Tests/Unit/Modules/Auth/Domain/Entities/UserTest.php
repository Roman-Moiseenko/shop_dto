<?php

namespace App\Modules\Auth\Tests\Unit\Modules\Auth\Domain\Entities;

use App\Modules\Auth\Domain\Entities\UserEntity;
use App\Modules\Auth\Domain\ValueObjects\Email;
use App\Modules\Auth\Domain\ValueObjects\HashedPassword;
use App\Modules\Auth\Domain\ValueObjects\RoleName;

use Illuminate\Foundation\Testing\TestCase;
use InvalidArgumentException;
use DateTimeImmutable;
class UserTest extends TestCase
{
    private Email $email;
    private HashedPassword $password;

    protected function setUp(): void
    {
        parent::setUp();
        $this->email = new Email('test@example.com');
        $this->password = HashedPassword::fromPlainText('password123');
    }

    /** @test */
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

    /** @test */
    public function it_can_set_and_get_id(): void
    {
        $user = new UserEntity( $this->email, $this->password);
        $user->id = 42;

        $this->assertEquals(42, $user->id);
    }

    /** @test */
    public function it_can_verify_email(): void
    {
        $user = new UserEntity( $this->email, $this->password);
        $this->assertFalse($user->isEmailVerified());

        $user->verifyEmail();
        $this->assertTrue($user->isEmailVerified());
        $this->assertInstanceOf(DateTimeImmutable::class, $user->emailVerifiedAt);
    }

    /** @test */
    public function it_can_set_email_verified_at_manually(): void
    {
        $user = new UserEntity( $this->email, $this->password);
        $date = new DateTimeImmutable('2025-01-01 12:00:00');
        $user->emailVerifiedAt = $date;

        $this->assertTrue($user->isEmailVerified());
        $this->assertEquals($date, $user->emailVerifiedAt);
    }

    /** @test */
    public function it_can_validate_password(): void
    {
        $user = new UserEntity( $this->email, $this->password);

        $this->assertTrue($user->validatePassword('password123'));
        $this->assertFalse($user->validatePassword('wrongpassword'));
    }

    /** @test */
    public function it_can_update_password(): void
    {
        $user = new UserEntity( $this->email, $this->password);
        $newPassword = HashedPassword::fromPlainText('new_secure_password');

        $user->updatePassword($newPassword);

        $this->assertTrue($user->validatePassword('new_secure_password'));
        $this->assertFalse($user->validatePassword('password123'));
        $this->assertEquals($newPassword->getHash(), $user->getPasswordHash());
    }

    /** @test */
    public function it_can_set_and_get_remember_token(): void
    {
        $user = new UserEntity( $this->email, $this->password);
        $this->assertNull($user->rememberToken);

        $token = 'some_random_token';
        $user->rememberToken = $token;
        $this->assertEquals($token, $user->rememberToken);
    }

    /** @test */
    public function it_can_set_profileable_relation(): void
    {
        $user = new UserEntity( $this->email, $this->password);
        $user->setProfile('Modules\Staff\Models\Staff', 100);

        $this->assertEquals('Modules\Staff\Models\Staff', $user->profileableType);
        $this->assertEquals(100, $user->profileableId);
    }

    /** @test */
    public function it_can_manage_roles(): void
    {
        $user = new UserEntity( $this->email, $this->password);
        $roles = ['admin', 'client'];
        $user->roles = $roles;

        $this->assertEquals($roles, $user->roles);
    }

    /** @test */
    public function it_can_check_if_has_specific_role(): void
    {
        $user = new UserEntity( $this->email, $this->password);
        $user->roles = ['admin', 'client'];
        $this->assertTrue($user->hasRole( 'admin'));
        $this->assertTrue($user->hasRole('client'));
        $this->assertFalse($user->hasRole('editor'));
  //      $this->assertTrue($user->hasRole(new RoleName('admin')));
 //       $this->assertTrue($user->hasRole(new RoleName('client')));
//        $this->assertFalse($user->hasRole(new RoleName('editor')));
    }

    /** @test */
    public function it_can_check_if_admin(): void
    {
        $user = new UserEntity( $this->email, $this->password);
        $this->assertFalse($user->isAdmin());
        $user->roles = ['admin'];
        $this->assertTrue($user->isAdmin());
    }

    // Тесты на исключения при создании Email и Password находятся в отдельных тестах для Value Objects,
    // но мы можем проверить, что исключения пробрасываются корректно.
    /** @test */
    public function it_throws_exception_when_creating_with_invalid_email(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new Email('invalid-email');
    }

    /** @test */
    public function it_throws_exception_when_creating_with_short_password(): void
    {
        $this->expectException(InvalidArgumentException::class);
        HashedPassword::fromPlainText('short');
    }
}
