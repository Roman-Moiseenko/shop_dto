<?php

namespace App\Modules\Auth\Tests\Unit\Domain\ValueObjects;

use App\Modules\Auth\Domain\Exceptions\RoleInvalidArgumentException;
use App\Modules\Auth\Domain\ValueObjects\StaffRolesAssignment;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class StaffRolesAssignmentTest extends TestCase
{
    #[Test]
    public function creates_valid_assignment(): void
    {
        $assignment = new StaffRolesAssignment(['staff', 'editor']);
        $this->assertSame(['staff', 'editor'], $assignment->toArrayOfStrings());
    }

    #[Test]
    public function throws_exception_when_empty_array(): void
    {
        $this->expectException(RoleInvalidArgumentException::class);
        $this->expectExceptionMessage('Роли пользователя не определены');
        new StaffRolesAssignment([]);
    }

    #[Test]
    public function throws_exception_when_client_role_included(): void
    {
        $this->expectException(RoleInvalidArgumentException::class);
        $this->expectExceptionMessage('Нельзя назначить роль client');
        new StaffRolesAssignment(['staff', 'client']);
    }

    #[Test]
    public function accepts_role_with_staff(): void
    {
        $assignment = new StaffRolesAssignment(['staff']);
        $this->assertSame(['staff'], $assignment->toArrayOfStrings());
    }
}
