<?php

namespace App\Modules\Auth\Tests\Unit\Domain\ValueObjects;
use App\Modules\Auth\Domain\ValueObjects\FullName;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class FullNameTest extends TestCase
{
    public function test_creates_with_full_name(): void
    {
        $fullName = new FullName('Иванов Иван Иванович');
        $this->assertSame('Иванов Иван Иванович', $fullName->getValue());
        $this->assertSame('Иванов', $fullName->getLastName());
        $this->assertSame('Иван', $fullName->getFirstName());
        $this->assertSame('Иванович', $fullName->getMiddleName());
    }

    public function test_creates_without_middle_name(): void
    {
        $fullName = new FullName('Петров Пётр');
        $this->assertSame('Петров Пётр', $fullName->getValue());
        $this->assertSame('Петров', $fullName->getLastName());
        $this->assertSame('Пётр', $fullName->getFirstName());
        $this->assertNull($fullName->getMiddleName());
    }

    public function test_normalizes_case(): void
    {
        $fullName = new FullName('иванов иван иванович');
        $this->assertSame('Иванов Иван Иванович', $fullName->getValue());
    }

    public function test_trims_and_removes_extra_spaces(): void
    {
        $fullName = new FullName('   Сидоров   Алексей   Петрович   ');
        $this->assertSame('Сидоров Алексей Петрович', $fullName->getValue());
    }

    public function test_supports_double_barreled_last_name(): void
    {
        $fullName = new FullName('Петров-Водкин Кузьма Сергеевич');
        $this->assertSame('Петров-Водкин Кузьма Сергеевич', $fullName->getValue());
        $this->assertSame('Петров-Водкин', $fullName->getLastName());
    }

    public function test_supports_double_first_name(): void
    {
        // Например, двойное имя через пробел, что редко, но допустим
        $fullName = new FullName('Иванов Анна-Мария Ивановна'); // дефис в имени
        $this->assertSame('Анна-Мария', $fullName->getFirstName());
    }

    public function test_get_initials(): void
    {
        $fullName = new FullName('Иванов Иван Иванович');
        $this->assertSame('Иванов И.И.', $fullName->getInitials());
    }

    public function test_get_initials_without_middle_name(): void
    {
        $fullName = new FullName('Петров Пётр');
        $this->assertSame('Петров П.', $fullName->getInitials());
    }

    public function test_get_short_name(): void
    {
        $fullName = new FullName('Сидоров Алексей Петрович');
        $this->assertSame('Алексей Сидоров', $fullName->getShortName());
    }

    public function test_to_string_returns_full_name(): void
    {
        $fullName = new FullName('Иванов Иван Иванович');
        $this->assertSame('Иванов Иван Иванович', (string) $fullName);
    }

    public function test_equals_with_same_full_name(): void
    {
        $a = new FullName('Иванов Иван Иванович');
        $b = new FullName('Иванов Иван Иванович');
        $this->assertTrue($a->equals($b));
    }

    public function test_not_equals_with_different_full_name(): void
    {
        $a = new FullName('Иванов Иван Иванович');
        $b = new FullName('Петров Пётр');
        $this->assertFalse($a->equals($b));
    }

    public function test_throws_exception_on_empty_string(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Имя не может быть пустым');
        new FullName('');
    }

    public function test_throws_exception_on_single_word(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Имя должно содержать хотя бы фамилию и имя');
        new FullName('Иванов');
    }

    public function test_throws_exception_if_any_part_too_short(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Каждая часть имени должна быть не короче 2 символов');
        new FullName('Иванов И');
    }

    public function test_throws_exception_on_invalid_characters(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Имя содержит недопустимые символы');
        new FullName('Иванов Иван123');
    }

    public function test_throws_exception_on_special_characters(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new FullName('Иванов @#$');
    }

    public function test_equals_ignores_case_differences(): void
    {
        $a = new FullName('иванов иван иванович');
        $b = new FullName('Иванов Иван Иванович');
        $this->assertTrue($a->equals($b));
    }
}
