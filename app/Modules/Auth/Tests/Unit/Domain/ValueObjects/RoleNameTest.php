<?php

namespace App\Modules\Auth\Tests\Unit\Domain\ValueObjects;
use App\Modules\Auth\Domain\ValueObjects\RoleName;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class RoleNameTest extends TestCase
{
    public function test_creates_valid_admin_role(): void
    {
        $role = new RoleName('admin');
        $this->assertTrue($role->isAdmin());
        $this->assertFalse($role->isClient());
    }

    public function test_creates_valid_client_role(): void
    {
        $role = new RoleName('client');
        $this->assertTrue($role->isClient());
        $this->assertFalse($role->isAdmin());
    }

    public function test_normalizes_case(): void
    {
        $role = new RoleName('ADMIN');
        $this->assertSame('admin', $role->getValue());
    }
/*
    public function test_throws_exception_on_invalid_role(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new RoleName('superadmin');
    }
*/
    public function test_equals(): void
    {
        $a = new RoleName('admin');
        $b = new RoleName('admin');
        $this->assertTrue($a->equals($b));

        $c = new RoleName('client');
        $this->assertFalse($a->equals($c));
    }
}
