<?php


use Iconet\PacketBuilder;
use Iconet\PacketHandler;
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

    public function testNotification(): void
    {
        $packet = PacketBuilder::notification(
            $this->id,
            $this->actor,
            $this->to,
            $this->encryptedSecret,
            $this->encryptedPredata,
            $this->formatId
        );

        $response = PacketHandler::checkPacket(json_decode($packet));
        echo "RESPONSE";
        echo $response;
        self::assertTrue($response);
    }


    public function testError(): void
    {
        $packet = PacketBuilder::error($this->error);
        $response = PacketHandler::checkPacket(json_decode($packet));

        self::assertFalse($response);
    }
}
