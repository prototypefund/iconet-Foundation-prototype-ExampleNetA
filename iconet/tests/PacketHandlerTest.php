<?php


use Iconet\PacketHandler;
use Iconet\PacketTypes;
use PHPUnit\Framework\TestCase;

/**
 * Tests if the PacketHandler::checkPacket correctly recognizes
 * valid and invalid packets and their type.
 */
class PacketHandlerTest extends TestCase
{


    public function testPublicKeyRequest(): void
    {
        $packet = (object)[
            "type" => "PublicKeyRequest",
            "address" => "test@something.tld"
        ];
        self::assertEquals(
            PacketTypes::PUBLIC_KEY_REQUEST,
            PacketHandler::checkPacket($packet)
        );
    }


    public function testPublicKeyRequestInvalid(): void
    {
        $noType = (object)[
            "address" => "bob@bobnet.org"
        ];
        $wrongAddress = (object)[
            "type" => "PublicKeyRequest",
            "address" => "wrongAddress"
        ];
        $noAddress = (object)[
            "type" => "PublicKeyRequest"
        ];

        $checkNoType = PacketHandler::checkPacket($noType);
        $checkWrongAddress = PacketHandler::checkPacket($wrongAddress);
        $checkNoAddress = PacketHandler::checkPacket($noAddress);

        self::assertEquals(PacketTypes::ERROR, $checkNoType);
        self::assertEquals(PacketTypes::ERROR, $checkWrongAddress);
        self::assertEquals(PacketTypes::ERROR, $checkNoAddress);
    }


    public function testPublicKeyResponse(): void
    {
        $packet = (object)[
            "type" => "PublicKeyResponse",
            "address" => "alice@net.org",
            "publicKey" => "-----BEGIN PUBLIC KEY-----\nM...QAB\n-----END PUBLIC KEY-----\n"
        ];
        $packetType = PacketHandler::checkPacket($packet);
        self::assertEquals(PacketTypes::PUBLIC_KEY_RESPONSE, $packetType);
    }

    public function testPublicKeyResponseInvalid(): void
    {
        $noType = (object)[
            "address" => "bob@bobnet.org"
        ];
        $wrongAddress = (object)[
            "type" => "PublicKeyResponse",
            "address" => "wrongAddress",
            "publicKey" => "-----BEGIN PUBLIC KEY-----\nM...QAB\n-----END PUBLIC KEY-----\n"
        ];
        $missingField = (object)[
            "type" => "PublicKeyResponse"
        ];

        $checkNoType = PacketHandler::checkPacket($noType);
        $checkWrongAddress = PacketHandler::checkPacket($wrongAddress);
        $checkMissingField = PacketHandler::checkPacket($missingField);

        self::assertEquals(PacketTypes::ERROR, $checkNoType);
        self::assertEquals(PacketTypes::ERROR, $checkWrongAddress);
        self::assertEquals(PacketTypes::ERROR, $checkMissingField);
    }


    public function testNotification(): void
    {
        $packet = (object)[
            "type" => "Notification",
            "actor" => "alice@net.org",
            "to" => "bob@bobnet.org",
            "encryptedSecret" => "jtqgp5D2Z4...",
            "predata" => "RXh...Ho=",
            "interoperability" => [
                "protocol" => "ExampleNetA",
                "contentType" => "Posting"
            ]
        ];
        self::assertEquals(PacketTypes::NOTIFICATION, PacketHandler::checkPacket($packet));
    }

    public function testNotificationInvalid(): void
    {
        $noType = (object)[
            "actor" => "alice@alicenet.net",
            "to" => "bob@bobnet.org",
            "encryptedSecret" => "jtqgp5D2Z4...",
            "predata" => "RXh...Ho=",
            "interoperability" => [
                "protocol" => "ExampleNetA",
                "contentType" => "Posting"
            ]
        ];
        $wrongAddress1 = (object)[
            "type" => "Notification",
            "actor" => "wrongAddress",
            "to" => "bob@bobnet.org",
            "encryptedSecret" => "jtqgp5D2Z4...",
            "predata" => "RXh...Ho=",
            "interoperability" => [
                "protocol" => "ExampleNetA",
                "contentType" => "Posting"
            ]
        ];
        $wrongAddress2 = (object)[
            "type" => "Notification",
            "actor" => "alice@alicenet.net",
            "to" => "wrongAddress",
            "encryptedSecret" => "jtqgp5D2Z4...",
            "predata" => "RXh...Ho=",
            "interoperability" => [
                "protocol" => "ExampleNetA",
                "contentType" => "Posting"
            ]
        ];
        $missingField = (object)[
            "type" => "Notification",
            "actor" => "alice@alicenet.net",
            "to" => "bob@bobnet.org",
            "predata" => "RXh...Ho=",
            "interoperability" => [
                "protocol" => "ExampleNetA",
                "contentType" => "Posting"
            ]
        ];

        $checkNoType = PacketHandler::checkPacket($noType);
        $checkWrongAddress1 = PacketHandler::checkPacket($wrongAddress1);
        $checkWrongAddress2 = PacketHandler::checkPacket($wrongAddress2);
        $checkMissingField = PacketHandler::checkPacket($missingField);

        self::assertEquals(PacketTypes::ERROR, $checkNoType);
        self::assertEquals(PacketTypes::ERROR, $checkWrongAddress1);
        self::assertEquals(PacketTypes::ERROR, $checkWrongAddress2);
        self::assertEquals(PacketTypes::ERROR, $checkMissingField);
    }


    public function testContentRequest(): void
    {
        $packet = (object)[
            "type" => "ContentRequest",
            "actor" => "alice@alicenet.net",
            "id" => "92defee110..."
        ];
        $packetType = PacketHandler::checkPacket($packet);
        self::assertEquals(PacketTypes::CONTENT_REQUEST, $packetType);
    }

    public function testContentRequestInvalid(): void
    {
        $noType = (object)[
            "actor" => "alice@alicenet.net",
            "id" => "92defee110..."
        ];
        $missingField = (object)[
            "type" => "ContentRequest",
            "actor" => "alice@alicenet.net"
        ];

        $checkNoType = PacketHandler::checkPacket($noType);
        $checkMissingField = PacketHandler::checkPacket($missingField);

        self::assertEquals(PacketTypes::ERROR, $checkNoType);
        self::assertEquals(PacketTypes::ERROR, $checkMissingField);
    }


    public function testContentResponse(): void
    {
        $packet = (object)[
            "type" => "ContentResponse",
            "actor" => "alice@alicenet.net",
            "formatId" => "post-comments",
            "content" => [
                "ZXloOUp2Nn..."
            ]
        ];
        $packetType = PacketHandler::checkPacket($packet);
        self::assertEquals(PacketTypes::CONTENT_RESPONSE, $packetType);
    }

    public function testContentResponseInvalid(): void
    {
        $noType = (object)[
            "actor" => "alice@alicenet.net",
            "formatId" => "post-comments",
            "content" => [
                "ZXloOUp2Nn..."
            ]
        ];
        $wrongAddress = (object)[
            "type" => "ContentResponse",
            "actor" => "wrongAddress",
            "formatId" => "post-comments",
            "content" => [
                "ZXloOUp2Nn..."
            ]
        ];
        $missingField = (object)[
            "type" => "ContentResponse",
            "formatId" => "post-comments",
            "content" => [
                "ZXloOUp2Nn..."
            ]
        ];

        $checkNoType = PacketHandler::checkPacket($noType);
        $checkWrongAddress = PacketHandler::checkPacket($wrongAddress);
        $checkMissingField = PacketHandler::checkPacket($missingField);

        self::assertEquals(PacketTypes::ERROR, $checkNoType);
        self::assertEquals(PacketTypes::ERROR, $checkWrongAddress);
        self::assertEquals(PacketTypes::ERROR, $checkMissingField);
    }


    public function testFormatRequest(): void
    {
        $packet = (object)[
            "type" => "FormatRequest",
            "formatId" => "wrongFormatId"
        ];
        $packetType = PacketHandler::checkPacket($packet);
        self::assertEquals(PacketTypes::FORMAT_REQUEST, $packetType);
    }

    public function testFormatRequestInvalid(): void
    {
        $noType = (object)[
            "formatId" => "post-comments"
        ];

        $noId = (object)[
            "type" => "FormatRequest"
        ];

        $checkNoType = PacketHandler::checkPacket($noType);
        $checkNoId = PacketHandler::checkPacket($noId);

        self::assertEquals(PacketTypes::ERROR, $checkNoType);
        self::assertEquals(PacketTypes::ERROR, $checkNoId);
    }


    public function testFormatResponse(): void
    {
        $packet = (object)[
            "type" => "FormatResponse",
            "formatId" => "wrongFormatId",
            "format" => "<i>New Message by: ['sender'] <\/i>\n<p>['text']<\/p>\n<small>Sent: ['time']<\/small>"
        ];

        $checkRightPacket = PacketHandler::checkPacket($packet);
        self::assertEquals(PacketTypes::FORMAT_RESPONSE, $checkRightPacket);
    }


    public function testFormatResponseInvalid(): void
    {
        $noType = (object)[
            "formatId" => "post-comments"
        ];
        $noId = (object)[
            "type" => "FormatResponse"
        ];

        $checkNoType = PacketHandler::checkPacket($noType);
        $checkNoId = PacketHandler::checkPacket($noId);

        self::assertEquals(PacketTypes::ERROR, $checkNoType);
        self::assertEquals(PacketTypes::ERROR, $checkNoId);
    }


    public function testInteraction(): void
    {
        $packet = (object)[
            "type" => "Interaction",
            "actor" => "bob@bobnet.org",
            "to" => "alice@alicenet.net",
            "id" => "92defee110...",
            "interactionType" => "comment",
            "payload" => "bCsyRG5xRlF..."
        ];
        $packetType = PacketHandler::checkPacket($packet);
        self::assertEquals(PacketTypes::INTERACTION, $packetType);
    }

    public function testInteractionInvalid(): void
    {
        $noType = (object)[
            "actor" => "bob@bobnet.org",
            "to" => "alice@alicenet.net",
            "id" => "92defee110...",
            "interactionType" => "comment",
            "payload" => "bCsyRG5xRlF..."
        ];
        $wrongAddress = (object)[
            "type" => "Interaction",
            "actor" => "wrongAddress",
            "to" => "alice@alicenet.net",
            "id" => "92defee110...",
            "interactionType" => "comment",
            "payload" => "bCsyRG5xRlF..."
        ];
        $missingField = (object)[
            "type" => "Interaction",
            "actor" => "bob@bobnet.org",
            "to" => "alice@alicenet.net",
            "interactionType" => "comment",
            "payload" => "bCsyRG5xRlF..."
        ];

        $checkNoType = PacketHandler::checkPacket($noType);
        $checkWrongAddress = PacketHandler::checkPacket($wrongAddress);
        $checkMissingField = PacketHandler::checkPacket($missingField);

        self::assertEquals(PacketTypes::ERROR, $checkNoType);
        self::assertEquals(PacketTypes::ERROR, $checkWrongAddress);
        self::assertEquals(PacketTypes::ERROR, $checkMissingField);
    }


    public function testError(): void
    {
        $packet = (object)[
            'type' => 'error',
            'error' => "Error message."
        ];
        $packetType = PacketHandler::checkPacket($packet);

        self::assertEquals(PacketTypes::ERROR, $packetType);
    }
}
