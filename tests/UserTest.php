<?php

require_once "config/config.php";

use PHPUnit\Framework\TestCase;

class UserTest extends TestCase
{
    private User $alice;
    private User $bob;

    protected function setUp(): void
    {
        parent::setUp();
        Database::singleton()->registerUser(
            "Alice",
            "Armstrong",
            "aliceTester",
            "alice@test.net",
            "secret",
            date('Y-m-d H:i:s'),
            "empty"
        );
        Database::singleton()->registerUser(
            "Bob",
            "Braun",
            "bobTester",
            "bob@test.net",
            "secret",
            date('Y-m-d H:i:s'),
            "empty"
        );

        $this->alice = new User('aliceTester');
        $this->bob = new User('bobTester');

    }

    protected function tearDown() : void
    {
        parent::tearDown();

        Database::singleton()->deleteUser("aliceTester");
        Database::singleton()->deleteUser("bobTester");
    }

    public function test_construct()
    {
        $alice = new User('aliceTester');
        $bob = new User('bobTester');

        self::assertIsObject($alice);
        self::assertIsObject($bob);
        self::assertEquals('aliceTester', $alice->username);
        self::assertEquals('bobTester', $bob->username);
    }

    public function testName()
    {
        $alice = new User('aliceTester');
        self::assertEquals($alice->firstname, "Alice");
        self::assertEquals($alice->lastname, "Armstrong");

        $alice = new User('bobTester');
        self::assertEquals($alice->firstname, "Bob");
        self::assertEquals($alice->lastname, "Braun");
    }

    public function testIsFriend()
    {
        $alice = $this->alice;
        $bob = $this->bob;

        self::assertTrue($alice->isFriend($alice));
        self::assertFalse($alice->isFriend($bob));
        self::assertFalse($alice->didSendFriendRequest($bob->username));
        self::assertFalse($alice->didReceiveRequest($bob->username));
        self::assertFalse($alice->didReceiveRequest($alice->username));

        self::assertTrue($bob->isFriend($bob));
        self::assertFalse($bob->isFriend($alice));
        self::assertFalse($bob->didSendFriendRequest($alice->username));
        self::assertFalse($bob->didReceiveRequest($alice->username));
        self::assertFalse($bob->didReceiveRequest($bob->username));

        $alice->sendFriendRequest('bobTester');

        self::assertTrue($alice->didSendFriendRequest($bob->username));
        self::assertTrue($bob->didReceiveRequest($alice->username));

        $bob->acceptFriendRequest($alice);

        self::assertFalse($alice->didSendFriendRequest($bob->username));
        self::assertFalse($bob->didReceiveRequest($alice->username));
        self::assertTrue($alice->isFriend($bob));
        self::assertTrue($bob->isFriend($alice));
    }

    public function testExists()
    {
        self::assertFalse(User::exists('doesNotExistTester'));
        self::assertTrue(User::exists('aliceTester'));
        self::assertTrue(User::exists('bobTester'));
    }

    public function testGetFriends()
    {
        $alice = $this->alice;
        $bob = $this->bob;

        self::assertEmpty($alice->getFriends());
        self::assertEmpty($bob->getFriends());

        $this->testIsFriend();

        self::assertEquals([$this->bob->username], $alice->getFriends());
        self::assertEquals([$this->alice->username], $bob->getFriends());
    }

}
