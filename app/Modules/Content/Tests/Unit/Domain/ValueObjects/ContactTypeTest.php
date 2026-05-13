<?php

namespace App\Modules\Content\Tests\Unit\Domain\ValueObjects;

use App\Modules\Content\Domain\ValueObjects\ContactType;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class ContactTypeTest extends TestCase
{
    #[Test]
    public function creates_valid_types(): void
    {
        foreach (['phone', 'email', 'address', 'social', 'messenger', 'other'] as $type) {
            $contactType = new ContactType($type);
            $this->assertSame($type, $contactType->getValue());
        }
    }

    #[Test]
    public function normalizes_case(): void
    {
        $type = new ContactType('PHONE');
        $this->assertSame('phone', $type->getValue());
    }

    #[Test]
    public function throws_exception_on_invalid(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new ContactType('fax');
    }

    #[Test]
    public function equals_works(): void
    {
        $a = new ContactType('phone');
        $b = new ContactType('phone');
        $c = new ContactType('email');
        $this->assertTrue($a->equals($b));
        $this->assertFalse($a->equals($c));
    }

    #[Test]
    public function is_phone_check(): void
    {
        $type = new ContactType('phone');
        $this->assertTrue($type->isPhone());
        $this->assertFalse($type->isEmail());
    }

    #[Test]
    public function is_email_check(): void
    {
        $type = new ContactType('email');
        $this->assertTrue($type->isEmail());
        $this->assertFalse($type->isAddress());
    }

    #[Test]
    public function is_address_check(): void
    {
        $type = new ContactType('address');
        $this->assertTrue($type->isAddress());
        $this->assertFalse($type->isSocial());
    }

    #[Test]
    public function is_social_check(): void
    {
        $type = new ContactType('social');
        $this->assertTrue($type->isSocial());
        $this->assertFalse($type->isMessenger());
    }

    #[Test]
    public function is_messenger_check(): void
    {
        $type = new ContactType('messenger');
        $this->assertTrue($type->isMessenger());
        $this->assertFalse($type->isOther());
    }

    #[Test]
    public function is_other_check(): void
    {
        $type = new ContactType('other');
        $this->assertTrue($type->isOther());
        $this->assertFalse($type->isPhone());
    }

    #[Test]
    public function static_factories(): void
    {
        $this->assertSame('phone', ContactType::phone()->getValue());
        $this->assertSame('email', ContactType::email()->getValue());
        $this->assertSame('address', ContactType::address()->getValue());
        $this->assertSame('social', ContactType::social()->getValue());
        $this->assertSame('messenger', ContactType::messenger()->getValue());
        $this->assertSame('other', ContactType::other()->getValue());
    }

    #[Test]
    public function allowed_returns_all_values(): void
    {
        $expected = ['phone', 'email', 'address', 'social', 'messenger', 'other'];
        $this->assertSame($expected, ContactType::allowed());
    }
}
