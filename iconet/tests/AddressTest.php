<?php


use Iconet\Address;
use PHPUnit\Framework\TestCase;

class AddressTest extends TestCase
{

    public function test_validate(): void
    {
        self::assertTrue(Address::validate('alice@neta.localhost'));
        self::assertTrue(Address::validate('alice@neta.net'));
        self::assertTrue(Address::validate('alice-cool123@localhost'));
        self::assertTrue(Address::validate('my.address@domain.tld'));

        self::assertTrue(Address::validate('alice@neta.localhost', true));
        self::assertFalse(Address::validate('alice@neta.net', true));
        self::assertFalse(Address::validate('alice-cool123@localhost', true));
        self::assertFalse(Address::validate('my.address@domain.tld', true));

        self::assertFalse(Address::validate(''));
        self::assertFalse(Address::validate('@neta.net'));
        self::assertFalse(Address::validate('123@localhost'));
        self::assertFalse(Address::validate('-@domain.tld'));
        self::assertFalse(Address::validate('.@neta.localhost'));
        self::assertFalse(Address::validate('alice@neta@net'));
        self::assertFalse(Address::validate('alice-cool123@@localhost'));
        self::assertFalse(Address::validate('my.address@§-test.tld'));
    }
}
