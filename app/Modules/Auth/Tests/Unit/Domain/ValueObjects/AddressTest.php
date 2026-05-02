<?php

namespace App\Modules\Auth\Tests\Unit\Domain\ValueObjects;
use App\Modules\Auth\Domain\ValueObjects\Address;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class AddressTest extends TestCase
{
    public function test_creates_with_required_fields(): void
    {
        $address = new Address('Россия', 'Москва', 'ул. Тверская, 1');
        $this->assertSame('Россия', $address->country);
        $this->assertSame('Москва', $address->city);
        $this->assertSame('ул. Тверская, 1', $address->street);
        $this->assertNull($address->region);
        $this->assertNull($address->postalCode);
    }

    public function test_creates_with_all_fields(): void
    {
        $address = new Address('Россия', 'Москва', 'ул. Тверская, 1', 'Московская обл.', '125009');
        $this->assertSame('Московская обл.', $address->region);
        $this->assertSame('125009', $address->postalCode);
    }

    public function test_full_address_generation(): void
    {
        $address = new Address('Россия', 'Москва', 'ул. Тверская, 1', 'Московская обл.', '125009');
        $this->assertSame('Россия, Московская обл., Москва, ул. Тверская, 1, 125009', $address->getFullAddress());
    }

    public function test_equals(): void
    {
        $a = new Address('Россия', 'Москва', 'ул. Тверская');
        $b = new Address('Россия', 'Москва', 'ул. Тверская');
        $this->assertTrue($a->equals($b));

        $c = new Address('Россия', 'Санкт-Петербург', 'Невский пр.');
        $this->assertFalse($a->equals($c));
    }
}
