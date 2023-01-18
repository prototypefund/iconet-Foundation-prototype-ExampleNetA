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





    //todo
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

    //todo
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

    //todo
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
