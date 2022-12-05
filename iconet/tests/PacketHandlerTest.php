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
            "type" => "PublicKey Request",
            "address" => "test@something.tld"
        ];
        self::assertEquals(
            PacketTypes::PUBLICKEY_REQUEST,
            PacketHandler::checkPacket($packet)
        );
    }


    public function testPublicKeyRequestInvalid(): void
    {
        $noType = (object)[
            "address" => "bob@bobnet.org"
        ];
        $wrongAddress = (object)[
            "type" => "PublicKey Request",
            "address" => "wrongAddress"
        ];
        $noAddress = (object)[
            "type" => "PublicKey Request"
        ];

        $checkNoType = PacketHandler::checkPacket($noType);
        $checkWrongAddress = PacketHandler::checkPacket($wrongAddress);
        $checkNoAddress = PacketHandler::checkPacket($noAddress);

        self::assertEquals(PacketTypes::INVALID, $checkNoType);
        self::assertEquals(PacketTypes::INVALID, $checkWrongAddress);
        self::assertEquals(PacketTypes::INVALID, $checkNoAddress);
    }


    public function testPublicKeyResponse(): void
    {
        $packet = (object)[
            "type" => "PublicKey Response",
            "address" => "alice@net.org",
            "publicKey" => "-----BEGIN PUBLIC KEY-----\nM...QAB\n-----END PUBLIC KEY-----\n"
        ];
        $packetType = PacketHandler::checkPacket($packet);
        self::assertEquals(PacketTypes::PUBLICKEY_RESPONSE, $packetType);
    }

    public function testPublicKeyResponseInvalid(): void
    {
        $noType = (object)[
            "address" => "bob@bobnet.org"
        ];
        $wrongAddress = (object)[
            "type" => "PublicKey Response",
            "address" => "wrongAddress",
            "publicKey" => "-----BEGIN PUBLIC KEY-----\nM...QAB\n-----END PUBLIC KEY-----\n"
        ];
        $missingField = (object)[
            "type" => "PublicKeyResponse"
        ];

        $checkNoType = PacketHandler::checkPacket($noType);
        $checkWrongAddress = PacketHandler::checkPacket($wrongAddress);
        $checkMissingField = PacketHandler::checkPacket($missingField);

        self::assertEquals(PacketTypes::INVALID, $checkNoType);
        self::assertEquals(PacketTypes::INVALID, $checkWrongAddress);
        self::assertEquals(PacketTypes::INVALID, $checkMissingField);
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

        self::assertEquals(PacketTypes::INVALID, $checkNoType);
        self::assertEquals(PacketTypes::INVALID, $checkWrongAddress1);
        self::assertEquals(PacketTypes::INVALID, $checkWrongAddress2);
        self::assertEquals(PacketTypes::INVALID, $checkMissingField);
    }


    public function testContentRequest(): void
    {
        $packet = (object)[
            "type" => "Content Request",
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
            "type" => "Content Request",
            "actor" => "alice@alicenet.net"
        ];

        $checkNoType = PacketHandler::checkPacket($noType);
        $checkMissingField = PacketHandler::checkPacket($missingField);

        self::assertEquals(PacketTypes::INVALID, $checkNoType);
        self::assertEquals(PacketTypes::INVALID, $checkMissingField);
    }


    public function testContentResponse(): void
    {
        $packet = (object)[
            "type" => "Content Response",
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
            "type" => "Content Response",
            "actor" => "wrongAddress",
            "formatId" => "post-comments",
            "content" => [
                "ZXloOUp2Nn..."
            ]
        ];
        $missingField = (object)[
            "type" => "Content Response",
            "formatId" => "post-comments",
            "content" => [
                "ZXloOUp2Nn..."
            ]
        ];

        $checkNoType = PacketHandler::checkPacket($noType);
        $checkWrongAddress = PacketHandler::checkPacket($wrongAddress);
        $checkMissingField = PacketHandler::checkPacket($missingField);

        self::assertEquals(PacketTypes::INVALID, $checkNoType);
        self::assertEquals(PacketTypes::INVALID, $checkWrongAddress);
        self::assertEquals(PacketTypes::INVALID, $checkMissingField);
    }


    public function testFormatRequest(): void
    {
        $packet = (object)[
            "type" => "Format Request",
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
            "type" => "Format Request"
        ];

        $checkNoType = PacketHandler::checkPacket($noType);
        $checkNoId = PacketHandler::checkPacket($noId);

        self::assertEquals(PacketTypes::INVALID, $checkNoType);
        self::assertEquals(PacketTypes::INVALID, $checkNoId);
    }


    public function testFormatResponse(): void
    {
        $packet = (object)[
            "type" => "Format Response",
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
            "type" => "Format Response"
        ];

        $checkNoType = PacketHandler::checkPacket($noType);
        $checkNoId = PacketHandler::checkPacket($noId);

        self::assertEquals(PacketTypes::INVALID, $checkNoType);
        self::assertEquals(PacketTypes::INVALID, $checkNoId);
    }


    public function testInteraction(): void
    {
        $packet = (object)[
            "type" => "Interaction",
            "actor" => "bob@bobnet.org",
            "to" => "alice@alicenet.net",
            "id" => "92defee110...",
            "interactionType" => "comment",
            "interaction" => "bCsyRG5xRlF..."
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
            "interaction" => "bCsyRG5xRlF..."
        ];
        $wrongAddress = (object)[
            "type" => "Interaction",
            "actor" => "wrongAddress",
            "to" => "alice@alicenet.net",
            "id" => "92defee110...",
            "interactionType" => "comment",
            "interaction" => "bCsyRG5xRlF..."
        ];
        $missingField = (object)[
            "type" => "Interaction",
            "actor" => "bob@bobnet.org",
            "to" => "alice@alicenet.net",
            "interactionType" => "comment",
            "interaction" => "bCsyRG5xRlF..."
        ];

        $checkNoType = PacketHandler::checkPacket($noType);
        $checkWrongAddress = PacketHandler::checkPacket($wrongAddress);
        $checkMissingField = PacketHandler::checkPacket($missingField);

        self::assertEquals(PacketTypes::INVALID, $checkNoType);
        self::assertEquals(PacketTypes::INVALID, $checkWrongAddress);
        self::assertEquals(PacketTypes::INVALID, $checkMissingField);
    }


    public function testError(): void
    {
        $packet = (object)[
            'type' => 'error',
            'error' => "Error message."
        ];
        $packetType = PacketHandler::checkPacket($packet);

        self::assertEquals(PacketTypes::INVALID, $packetType);
    }
}
