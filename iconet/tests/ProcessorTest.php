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
    private object $contentPacket;
    private object $contentPacketWithInter;
    private string $expectedContent;
    private string $expectedFormatId;
    private string $expectedPayload;
    private array $notification;
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
        $this->expectedContent = "Hello World! Test Content";
        $this->expectedFormatId = "/iconet/formats/interaction";

        $this->procA->createPost($this->expectedContent, $this->expectedFormatId);

        // Get Bob's notification
        $this->notification = $this->procB->getNotifications()[0];
        $notification = $this->notification;

        // Get content without interactions
        $encContentPacket = $this->procA->requestContent(
            $notification['content_id'],
            new Address($notification['sender'])
        );
        $this->contentPacket = $this->procA->decryptContentPacket(
            $encContentPacket,
            $notification['secret']
        );

        # Bob creates an interaction
        $this->expectedPayload = "Yeey! Interaction content";
        $this->procA->postInteraction(
            $this->expectedPayload,
            $notification['content_id'],
            $this->bob->address,
            $notification['sender'],
            $notification['secret']
        );

        # Get the content for Bob with the interactions
        $encContentPacket = $this->procA->requestContent(
            $notification['content_id'],
            new Address($notification['sender'])
        );
        $this->contentPacketWithInter = $this->procA->decryptContentPacket(
            $encContentPacket,
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
        self::assertEquals($this->expectedContent, $this->contentPacket->content);
        self::assertEquals($this->expectedFormatId, $this->contentPacket->formatId);
        self::assertEquals("Example Subject", $this->notification["subject"]);
        $interactions = $this->contentPacket->interactions;
        self::assertEmpty($interactions);
    }


    /**
     * Test if a posted interaction, after en- and decryption,
     * displays the right interaction attached to right content.
     */
    public function testPost_interaction(): void
    {
        self::assertEquals($this->expectedContent, $this->contentPacketWithInter->content);
        self::assertEquals($this->expectedFormatId, $this->contentPacketWithInter->formatId);

        $interactions = $this->contentPacketWithInter->interactions;
        self::assertNotEmpty($interactions);
        $inter = $interactions[0];
        self::assertEquals($this->expectedPayload, $inter->payload);
        self::assertEquals($this->bob->address, $inter->actor);
    }

    /**
     * Test if the expected content with interactions is returned by using the content id.
     */
    public function test_getEncryptedPost(): void
    {
        $id = $this->notification['content_id'];
        $fullContent = $this->procA->getEncryptedPostFromDB($id);
        self::assertObjectHasAttribute('id', $fullContent);
        self::assertObjectHasAttribute('content', $fullContent);
        self::assertObjectHasAttribute('secret', $fullContent);
        self::assertObjectHasAttribute('formatId', $fullContent);
    }

    /**
     * Test if the expected content with interactions is returned by using the content id.
     */
    public function test_getEncryptedInteractions(): void
    {
        $id = $this->notification['content_id'];
        $interactions = $this->procA->getEncryptedInteractions($id);

        self::assertIsArray($interactions);
        self::assertNotEmpty($interactions);
        $interaction = $interactions[0];
        self::assertEquals($this->bob->address, $interaction->actor);
        self::assertObjectHasAttribute('encPayload', $interaction);
    }
}
