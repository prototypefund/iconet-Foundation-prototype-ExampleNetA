<?php


use Iconet\Database;
use Iconet\IconetOutbox;
use Iconet\User;
use Iconet\UserManager;
use PHPUnit\Framework\TestCase;

class OutboxTest extends TestCase
{
    private IconetOutbox $outboxA;
    private \Iconet\IconetInbox $inboxB;
    private object $contentPacket;
    private object $contentPacketWithInter;
    private array $expectedContent;
    private string $expectedFormatId;
    private array $notification;
    private User $alice;
    private User $bob;
    private User $claire;

    protected function setUp(): void
    {
        parent::setUp();
        Iconet\Database::singleton()->clearTables();

        $this->alice = UserManager::addNewUser("alice");
        $this->bob = UserManager::addNewUser("bob");
        $this->claire = UserManager::addNewUser("claire");

        $this->alice->addContact($this->bob);
        $this->alice->addContact($this->claire);

        $this->outboxA = new IconetOutbox($this->alice);
        $this->outboxB = new IconetOutbox($this->bob);

        // Alice creates content
        $this->expectedContent = array('content' => "Hello World! Test Content", 'username' => "Alice");
        $this->expectedFormatId = "/iconet/formats/post-like-comment-netA";

        $this->outboxA->createPost($this->expectedContent, $this->expectedFormatId);

        // Get Bob's notification
        $this->notification = Database::singleton()->getNotifications($this->bob->username);
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        iconet\Database::singleton()->clearTables();
    }

    public function testOutbox()
    {
        self::assertEquals($this->expectedContent, $this->notification['payload']);
        self::assertEquals($this->expectedFormatId, $this->notification['FormatId']);
    }
}
