<?php
namespace Iconet;

class PacketHandler
{
    //TODO split this into getType and validation logic
    //TODO remove echos
    //TODO remove redundancy: Packets can be objects that have a property for required fields

    const JSON_LD = ['@context', '@type', '@id'];
    const PACKETS = [
        'Packet' => ['actor', 'to', 'interpreterManifests', 'content'],
        'EncryptedPacket' => ['actor', 'to', 'encryptedSecret', 'encryptedPayload']
    ];

    /**
     * @param object $packet Packet to verify
     * @param array<string> $fieldNames Expected field names of the packet object
     * @return bool True, when all required fields are set.
     */
    private static function hasFields(object $packet, array $fieldNames): bool
    {
        foreach($fieldNames as $name) {
            if(!isset($packet->$name)) {
                echo "Error - Field $name is not set.";
                return false;
            }
        }
        return true;
    }


    /**
     * @param mixed $packet
     * @return PacketTypes The determined type of the packet (includes INVALID)
     */
    public static function checkPacket(mixed $packet): bool
    {
        if(!self::hasFields($packet, self::JSON_LD)) {
            return false;
        }

        $type = $packet->{'@type'};
        if(!array_key_exists($type, self::PACKETS)) {
            echo "Invalid packet type '$type'";
            return false;
        }

        if(!self::hasFields($packet, self::PACKETS[$type])) {
            return false;
        }

        // Check non-optional unencrypted variables
        if(Address::validate($packet->actor) && Address::validate($packet->to)) {
            return $packet->{'@type'};
        } else {
            echo "Error - Faulty actor or recipient address <br>";
            return false;
        }
    }

    public static function tryToDecode(string $packet): object|false
    {
        $packet = json_decode($packet);

        if(!$packet) {
            return false;
        }
        if(!PacketHandler::checkPacket((object)$packet)) {
            return false;
        }

        return (object)$packet;
    }
}