<?php


use Iconet\PacketBuilder;
use Iconet\PacketHandler;
use Iconet\PacketTypes;
use PHPUnit\Framework\TestCase;

class PacketBuilderTest extends TestCase
{
    private string $address;
    private string $publicKey;
    private string $actor;
    private string $encryptedSecret;
    private string $encryptedPredata;
    private string $id;
    private string $content;
    private string $formatId;
    private string $format;
    private string $to;
    private string $interactionPayload;
    private string $error;

    protected function setUp(): void
    {
        $this->address = "bob@bobnet.org";
        $this->publicKey = "-----BEGIN PUBLIC KEY-----\nM...QAB\n-----END PUBLIC KEY-----\n";
        $this->actor = "alice@alicenet.net";
        $this->id = "92defee110...";
        $this->formatId = "post-comments";
        $this->format = "<i>New Message by: ['sender'] <\/i>\n<p>['text']<\/p>\n<small>Sent: ['time']<\/small>";
        $this->to = "alice@alicenet.net";
        $this->interactionPayload = "bCsyRG5xRlF...";
        $this->error = "error";
        $this->encryptedSecret = "jtqgp5D2Z4...";
        $this->encryptedPredata = "as8d98d7fz";
        $this->content = "TestContent";
    }

    public function testPublicKeyRequest(): void
    {
        $packet = PacketBuilder::publicKey_request($this->address);
        self::assertEquals(
            PacketTypes::PUBLIC_KEY_REQUEST,
            PacketHandler::checkPacket(json_decode($packet))
        );
    }

    public function testPublicKeyResponse(): void
    {
        $packet = PacketBuilder::publicKey_response($this->address, $this->publicKey);
        $packetType = PacketHandler::checkPacket(json_decode($packet));
        self::assertEquals(PacketTypes::PUBLIC_KEY_RESPONSE, $packetType);
    }

    public function testNotification(): void
    {
        $packet = PacketBuilder::notification(
            $this->actor,
            $this->to,
            $this->encryptedSecret,
            $this->encryptedPredata
        );
        $packetType = PacketHandler::checkPacket(json_decode($packet));
        self::assertEquals(PacketTypes::NOTIFICATION, $packetType);
    }

    public function testContentRequest(): void
    {
        $packet = PacketBuilder::content_request($this->id, $this->actor);
        $packetType = PacketHandler::checkPacket(json_decode($packet));
        self::assertEquals(PacketTypes::CONTENT_REQUEST, $packetType);
    }

    public function testContentResponse(): void
    {
        $packet = PacketBuilder::content_response(
            $this->content,
            $this->formatId,
            [],
            $this->actor
        );
        $packetType = PacketHandler::checkPacket(json_decode($packet));
        self::assertEquals(PacketTypes::CONTENT_RESPONSE, $packetType);
    }

    public function testFormatRequest(): void
    {
        $packet = PacketBuilder::format_request($this->formatId);
        $packetType = PacketHandler::checkPacket(json_decode($packet));
        self::assertEquals(PacketTypes::FORMAT_REQUEST, $packetType);
    }

    public function testFormatResponse(): void
    {
        $packet = PacketBuilder::format_response($this->formatId, $this->format);
        $packetType = PacketHandler::checkPacket(json_decode($packet));
        self::assertEquals(PacketTypes::FORMAT_RESPONSE, $packetType);
    }

    public function testInteraction(): void
    {
        $packet = PacketBuilder::interaction(
            $this->address,
            $this->to,
            $this->id,
            $this->interactionPayload
        );
        $packetType = PacketHandler::checkPacket(json_decode($packet));
        self::assertEquals(PacketTypes::INTERACTION, $packetType);
    }

    public function testError(): void
    {
        $packet = PacketBuilder::error($this->error);
        $packetType = PacketHandler::checkPacket(json_decode($packet));

        self::assertEquals(PacketTypes::ERROR, $packetType);
    }
}
