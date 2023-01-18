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
        $noContext = (object)[
            "actor" => "alice@alicenet.net",
            "to" => "bob@bobnet.org",
            "id" => "42",
            "encryptedSecret" => "jtqgp5D2Z4...",
            "predata" => "RXh...Ho=",
            "interoperability" => [
                "protocol" => "ExampleNetA",
                "contentType" => "Posting"
            ]
        ];
        $wrongAddress1 = (object)[
            "@context" => "iconet Notification",
            "actor" => "wrongAddress",
            "to" => "bob@bobnet.org",
            "id" => "42",
            "encryptedSecret" => "jtqgp5D2Z4...",
            "predata" => "RXh...Ho=",
            "interoperability" => [
                "protocol" => "ExampleNetA",
                "contentType" => "Posting"
            ]
        ];
        $wrongAddress2 = (object)[
            "@context" => "iconet Notification",
            "actor" => "alice@alicenet.net",
            "to" => "wrongAddress",
            "id" => "42",
            "encryptedSecret" => "jtqgp5D2Z4...",
            "predata" => "RXh...Ho=",
            "interoperability" => [
                "protocol" => "ExampleNetA",
                "contentType" => "Posting"
            ]
        ];
        $missingId = (object)[
            "@context" => "iconet Notification",
            "actor" => "alice@alicenet.net",
            "to" => "bob@bobnet.org",
            "predata" => "RXh...Ho=",
            "interoperability" => [
                "protocol" => "ExampleNetA",
                "contentType" => "Posting"
            ]
        ];

        $checkNoContext = PacketHandler::checkPacket($noContext);
        $checkWrongAddress1 = PacketHandler::checkPacket($wrongAddress1);
        $checkWrongAddress2 = PacketHandler::checkPacket($wrongAddress2);
        $checkMissingId = PacketHandler::checkPacket($missingId);

        self::assertEquals(false, $checkNoContext);
        self::assertEquals(false, $checkWrongAddress1);
        self::assertEquals(false, $checkWrongAddress2);
        self::assertEquals(false, $checkMissingId);
    }
}
