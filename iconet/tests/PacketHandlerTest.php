<?php


use Iconet\PacketHandler;
use PHPUnit\Framework\TestCase;

/**
 * Tests if the PacketHandler::checkPacket correctly recognizes
 * valid and invalid packets and their type.
 */
class PacketHandlerTest extends TestCase
{

    public function testNotification(): void
    {
        $packet = (object)[
            "@context" => "iconet Notification",
            "id" => "2",
            "actor" => "alice@net.org",
            "to" => "bob@bobnet.org",
            "encryptedSecret" => "jtqgp5D2Z4...",
            "encryptedPayload" => "RXh...Ho=",
            "encryptedFormatId" => "fs..W",
            "interoperability" => [
                "protocol" => "ExampleNetA",
                "contentType" => "Posting"
            ]
        ];
        self::assertEquals(true, PacketHandler::checkPacket($packet));
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

        self::assertEquals(false, $checkNoType);
        self::assertEquals(false, $checkWrongAddress1);
        self::assertEquals(false, $checkWrongAddress2);
        self::assertEquals(false, $checkMissingField);
    }
}
