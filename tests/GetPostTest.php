<?php

require_once "config/config.php";

use PHPUnit\Framework\TestCase;

class GetPost extends TestCase
{
    private User $alice;
    private User $bob;
    private int lastPostID;

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
        
        $this->lastPostID = Database::singleton()->createPost(
            string "hello",
            $this->alice,
            $this->alice,
            "2022-12-24 12:12:12",
            null
        )
    }

    protected function tearDown() : void
    {
        parent::tearDown();

        Database::singleton()->deleteUser("aliceTester");
        Database::singleton()->deleteUser("bobTester");
    }

    public function getPost()
    {
        
    }

}
