<?php

require_once "config/config.php";

use Iconet\Address;
use Iconet\Database;
use Iconet\Processor;
use Iconet\User;
use Iconet\UserManager;
use PHPUnit\Framework\TestCase;

class ProcessorTest extends TestCase
{
    private Processor $procA;
    private Database $db;
    private string $content;
    private string $expectedContent;
    private array $notification;
    private string $interaction;
    private string $fullContent;
    private User $alice;
    private User $bob;
    private User $claire;

    protected function setUp(): void
    {
        parent::setUp();
        global $iconetDB;
        $this->db = $iconetDB;
        $this->db->clearTables();

        $this->alice = UserManager::addNewUser("alice");
        $this->bob = UserManager::addNewUser("bob");
        $this->claire = UserManager::addNewUser("claire");

        $this->alice->addContact($this->bob);
        $this->alice->addContact($this->claire);

        $this->procA = new Processor($this->alice);
        $this->procB = new Processor($this->bob);

        // Alice creates content
        $this->expectedContent = "Hello World";
        $this->procA->createPost($this->expectedContent);

        // Get Bob's notification
        $this->notification = $this->procB->getNotifications()[0];
        $notification = $this->notification;

        // Get content without interactions
        $this->content = $this->procA->displayContent(
            $notification['content_id'],
            new Address($notification['sender']),
            $notification['secret']
        );

        # Bob creates an interaction
        $this->interaction = "Yeey!";
        $interaction = $this->interaction;
        $interactionType = "comment";
        $this->procA->postInteraction(
            $interaction,
            $notification['content_id'],
            $this->bob->address,
            $notification['sender'],
            $notification['secret']
        );

        # Get the content for Bob with the interactions
        $this->fullContent = $this->procB->displayContent(
            $notification['content_id'],
            new Address($notification['sender']),
            $notification['secret']
        );
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        $this->db->clearTables();
    }

    public function testGet_external_publicKey(): void
    {
        $key = $this->procA->getExternalPublicKey($this->bob->address);
        self::assertEquals($this->bob->publicKey, $key);
    }


    /**
     * Test if a created iconet post, generates the right notification in Bob's inbox
     * and after en- and decryption display the right content.
     */
    public function testCreate_content(): void
    {
        self::assertEquals($this->expectedContent, $this->content);
        self::assertEquals("Example Subject", $this->notification["subject"]);
    }


    /**
     * Test if a posted interaction, after en- and decryption,
     * displays the right interaction attached to right content.
     */
    public function testPost_interaction(): void
    {
        // FIXME displayContent is not a good API
        $ecpectedFullContent = $this->content . "<br>Comment from: " . $this->bob->address . "<br>" . $this->interaction;
        self::assertEquals($ecpectedFullContent, $this->fullContent);
    }

    /**
     * Test if the expected content with interactions is returned by using the content id.
     */
    public function testRead_content(): void
    {
        $id = $this->notification['content_id'];
        $fullContent = $this->procA->readContent($id);

        self::assertArrayHasKey('content', $fullContent);
        self::assertArrayHasKey('interactions', $fullContent);
        $interaction = $fullContent['interactions'][0];
        self::assertEquals($this->bob->address, $interaction['sender']);
        self::assertArrayHasKey('enc_int', $interaction);
    }
}
