<?php

require_once "config/config.php";

use Iconet\UserManager;
use PHPUnit\Framework\TestCase;

class UserManagerTest extends TestCase
{

    protected function setUp(): void
    {
        parent::setUp();

        global $iconetDB;
        $iconetDB->clearTables();
    }

    public function testAddNewUser_invalid(): void
    {
        self::assertNull(UserManager::addNewUser(""));
        self::assertNull(UserManager::addNewUser("'"));
        self::assertNull(UserManager::addNewUser("@"));
        self::assertNull(UserManager::addNewUser("@alice"));
        self::assertNull(UserManager::addNewUser("With Space"));
        self::assertNull(UserManager::addNewUser("_first"));
        self::assertNull(UserManager::addNewUser("-first"));
        self::assertNull(UserManager::addNewUser(".first"));
        self::assertNull(UserManager::addNewUser("0first"));
    }

    public function testAddNewUser_valid(): void
    {
        self::assertEquals("tester", UserManager::addNewUser("tester")->username);
        self::assertEquals("JamesSmith", UserManager::addNewUser("JamesSmith")->username);
        self::assertEquals("Dolly-Dash", UserManager::addNewUser("Dolly-Dash")->username);
        self::assertEquals("X", UserManager::addNewUser("X")->username);
        self::assertEquals("a...", UserManager::addNewUser("a...")->username);
        self::assertEquals("under_dog", UserManager::addNewUser("under_dog")->username);
    }

    public function testAddNewUser_duplicate(): void
    {
        self::assertEquals("tester", UserManager::addNewUser("tester")->username);
        self::assertNull(UserManager::addNewUser("tester"));
        self::assertNull(UserManager::addNewUser("tester"));
    }

    public function testAddNewUser_database(): void
    {
        self::assertEquals("tester", UserManager::addNewUser("tester")->username);
        self::assertEquals("tester", \Iconet\User::fromUsername("tester")->username);
    }

    public function testAddContact(): void
    {
        $user = UserManager::addNewUser("tester");
        $friend = UserManager::addNewUser("friend");
        $success = UserManager::addContact($user, $friend->address);
        self::assertTrue($success);
        self::assertEquals($friend->address, Database::singleton()->getContacts($user->username)[0]->address);
    }
}
