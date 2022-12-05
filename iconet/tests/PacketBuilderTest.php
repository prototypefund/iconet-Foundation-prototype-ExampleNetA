<?php


use Iconet\PackageBuilder;
use Iconet\PackageHandler;
use Iconet\PackageTypes;
use PHPUnit\Framework\TestCase;

class PacketBuilderTest extends TestCase
{
    private string $address;
    private string $publicKey;
    private string $actor;
    private string $encryptedSecret;
    private string $encryptedPredata;
    private string $id;
    private object $content;
    private string $formatId;
    private string $format;
    private string $to;
    private string $interactionType;
    private string $interaction;
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
        $this->interactionType = "comment";
        $this->interaction = "bCsyRG5xRlF...";
        $this->error = "error";
        $this->encryptedSecret = "jtqgp5D2Z4...";
        $this->encryptedPredata = "as8d98d7fz";
        $this->content = (object)[
            "content" => "ZXloOUp2Nn...",
            // TODO add interactions
        ];
    }

    public function testPublicKeyRequest(): void
    {
        $packet = PackageBuilder::publicKey_request($this->address);
        self::assertEquals(
            PackageTypes::PUBLICKEY_REQUEST,
            PackageHandler::checkPackage(json_decode($packet))
        );
    }

    public function testPublicKeyResponse(): void
    {
        $packet = PackageBuilder::publicKey_response($this->address, $this->publicKey);
        $packetType = PackageHandler::checkPackage(json_decode($packet));
        self::assertEquals(PackageTypes::PUBLICKEY_RESPONSE, $packetType);
    }

    public function testNotification(): void
    {
        $packet = PackageBuilder::notification(
            $this->actor,
            $this->to,
            $this->encryptedSecret,
            $this->encryptedPredata
        );
        $packageType = PackageHandler::checkPackage(json_decode($packet));
        self::assertEquals(PackageTypes::NOTIFICATION, $packageType);
    }

    public function testContentRequest(): void
    {
        $packet = PackageBuilder::content_request($this->id, $this->actor);
        $packageType = PackageHandler::checkPackage(json_decode($packet));
        self::assertEquals(PackageTypes::CONTENT_REQUEST, $packageType);
    }

    public function testContentResponse(): void
    {
        $packet = PackageBuilder::content_response($this->content, $this->formatId, $this->actor);
        $packageType = PackageHandler::checkPackage(json_decode($packet));
        self::assertEquals(PackageTypes::CONTENT_RESPONSE, $packageType);
    }

    public function testFormatRequest(): void
    {
        $packet = PackageBuilder::format_request($this->formatId);
        $packageType = PackageHandler::checkPackage(json_decode($packet));
        self::assertEquals(PackageTypes::FORMAT_REQUEST, $packageType);
    }

    public function testFormatResponse(): void
    {
        $packet = PackageBuilder::format_response($this->formatId, $this->format);
        $packageType = PackageHandler::checkPackage(json_decode($packet));
        self::assertEquals(PackageTypes::FORMAT_RESPONSE, $packageType);
    }

    public function testInteraction(): void
    {
        $packet = PackageBuilder::interaction(
            $this->address,
            $this->to,
            $this->id,
            $this->interactionType,
            $this->interaction
        );
        $packageType = PackageHandler::checkPackage(json_decode($packet));
        self::assertEquals(PackageTypes::INTERACTION, $packageType);
    }

    public function testError(): void
    {
        $packet = PackageBuilder::error($this->error);
        $packetType = PackageHandler::checkPackage(json_decode($packet));

        self::assertEquals(PackageTypes::INVALID, $packetType);
    }
}
