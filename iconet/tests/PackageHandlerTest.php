<?php


use Iconet\PackageHandler;
use Iconet\PackageTypes;
use PHPUnit\Framework\TestCase;

/**
 * Tests if the PackageHandler::checkPackage correctly recognizes
 * valid and invalid packets and their type.
 */
class PackageHandlerTest extends TestCase
{


    public function testPublicKeyRequest(): void
    {
        $packet = (object)[
            "type" => "PublicKey Request",
            "address" => "test@something.tld"
        ];
        self::assertEquals(
            PackageTypes::PUBLICKEY_REQUEST,
            PackageHandler::checkPackage($packet)
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

        $checkNoType = PackageHandler::checkPackage($noType);
        $checkWrongAddress = PackageHandler::checkPackage($wrongAddress);
        $checkNoAddress = PackageHandler::checkPackage($noAddress);

        self::assertEquals(PackageTypes::INVALID, $checkNoType);
        self::assertEquals(PackageTypes::INVALID, $checkWrongAddress);
        self::assertEquals(PackageTypes::INVALID, $checkNoAddress);
    }


    public function testPublicKeyResponse(): void
    {
        $packet = (object)[
            "type" => "PublicKey Response",
            "address" => "alice@net.org",
            "publicKey" => "-----BEGIN PUBLIC KEY-----\nM...QAB\n-----END PUBLIC KEY-----\n"
        ];
        $packetType = PackageHandler::checkPackage($packet);
        self::assertEquals(PackageTypes::PUBLICKEY_RESPONSE, $packetType);
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

        $checkNoType = PackageHandler::checkPackage($noType);
        $checkWrongAddress = PackageHandler::checkPackage($wrongAddress);
        $checkMissingField = PackageHandler::checkPackage($missingField);

        self::assertEquals(PackageTypes::INVALID, $checkNoType);
        self::assertEquals(PackageTypes::INVALID, $checkWrongAddress);
        self::assertEquals(PackageTypes::INVALID, $checkMissingField);
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
        self::assertEquals(PackageTypes::NOTIFICATION, PackageHandler::checkPackage($packet));
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

        $checkNoType = PackageHandler::checkPackage($noType);
        $checkWrongAddress1 = PackageHandler::checkPackage($wrongAddress1);
        $checkWrongAddress2 = PackageHandler::checkPackage($wrongAddress2);
        $checkMissingField = PackageHandler::checkPackage($missingField);

        self::assertEquals(PackageTypes::INVALID, $checkNoType);
        self::assertEquals(PackageTypes::INVALID, $checkWrongAddress1);
        self::assertEquals(PackageTypes::INVALID, $checkWrongAddress2);
        self::assertEquals(PackageTypes::INVALID, $checkMissingField);
    }


    public function testContentRequest(): void
    {
        $packet = (object)[
            "type" => "Content Request",
            "actor" => "alice@alicenet.net",
            "id" => "92defee110..."
        ];
        $packetType = PackageHandler::checkPackage($packet);
        self::assertEquals(PackageTypes::CONTENT_REQUEST, $packetType);
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

        $checkNoType = PackageHandler::checkPackage($noType);
        $checkMissingField = PackageHandler::checkPackage($missingField);

        self::assertEquals(PackageTypes::INVALID, $checkNoType);
        self::assertEquals(PackageTypes::INVALID, $checkMissingField);
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
        $packageType = PackageHandler::checkPackage($packet);
        self::assertEquals(PackageTypes::CONTENT_RESPONSE, $packageType);
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

        $checkNoType = PackageHandler::checkPackage($noType);
        $checkWrongAddress = PackageHandler::checkPackage($wrongAddress);
        $checkMissingField = PackageHandler::checkPackage($missingField);

        self::assertEquals(PackageTypes::INVALID, $checkNoType);
        self::assertEquals(PackageTypes::INVALID, $checkWrongAddress);
        self::assertEquals(PackageTypes::INVALID, $checkMissingField);
    }


    public function testFormatRequest(): void
    {
        $packet = (object)[
            "type" => "Format Request",
            "formatId" => "wrongFormatId"
        ];
        $packageType = PackageHandler::checkPackage($packet);
        self::assertEquals(PackageTypes::FORMAT_REQUEST, $packageType);
    }

    public function testFormatRequestInvalid(): void
    {
        $noType = (object)[
            "formatId" => "post-comments"
        ];

        $noId = (object)[
            "type" => "Format Request"
        ];

        $checkNoType = PackageHandler::checkPackage($noType);
        $checkNoId = PackageHandler::checkPackage($noId);

        self::assertEquals(PackageTypes::INVALID, $checkNoType);
        self::assertEquals(PackageTypes::INVALID, $checkNoId);
    }


    public function testFormatResponse(): void
    {
        $packet = (object)[
            "type" => "Format Response",
            "formatId" => "wrongFormatId",
            "format" => "<i>New Message by: ['sender'] <\/i>\n<p>['text']<\/p>\n<small>Sent: ['time']<\/small>"
        ];

        $checkRightPackage = PackageHandler::checkPackage($packet);
        self::assertEquals(PackageTypes::FORMAT_RESPONSE, $checkRightPackage);
    }


    public function testFormatResponseInvalid(): void
    {
        $noType = (object)[
            "formatId" => "post-comments"
        ];
        $noId = (object)[
            "type" => "Format Response"
        ];

        $checkNoType = PackageHandler::checkPackage($noType);
        $checkNoId = PackageHandler::checkPackage($noId);

        self::assertEquals(PackageTypes::INVALID, $checkNoType);
        self::assertEquals(PackageTypes::INVALID, $checkNoId);
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
        $packageType = PackageHandler::checkPackage($packet);
        self::assertEquals(PackageTypes::INTERACTION, $packageType);
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

        $checkNoType = PackageHandler::checkPackage($noType);
        $checkWrongAddress = PackageHandler::checkPackage($wrongAddress);
        $checkMissingField = PackageHandler::checkPackage($missingField);

        self::assertEquals(PackageTypes::INVALID, $checkNoType);
        self::assertEquals(PackageTypes::INVALID, $checkWrongAddress);
        self::assertEquals(PackageTypes::INVALID, $checkMissingField);
    }


    public function testError(): void
    {
        $packet = (object)[
            'type' => 'error',
            'error' => "Error message."
        ];
        $packetType = PackageHandler::checkPackage($packet);

        self::assertEquals(PackageTypes::INVALID, $packetType);
    }
}
