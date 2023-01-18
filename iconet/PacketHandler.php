<?php
namespace Iconet;

class PacketHandler
{
    //TODO split this into getType and validation logic
    //TODO remove echos
    //TODO remove redundancy: Packets can be objects that have a property for required fields

    /**
     * @param mixed $packet
     * @return PacketTypes The determined type of the packet (includes INVALID)
     */
    public static function checkPacket(mixed $packet): bool
    {
        // Check if non-optional variables are set.
        var_dump($packet);
        if(!isset($packet->{'@context'})) {
            echo "Error - no context set in Json-LD";
            return false;
        }
        if(
            isset($packet->{'id'}) &&
            isset($packet->{'actor'}) &&
            isset($packet->{'to'}) &&
            isset($packet->{'encryptedSecret'}) &&
            isset($packet->{'encryptedPayload'}) &&
            isset($packet->{'encryptedFormatId'})
        ) {
            // Check if non-optional variables are proper (can't check notification content, potentially encrypted)
            if(Address::validate($packet->actor) && Address::validate($packet->to)) {
                // All conditions for type send notification are met.
                return true;
            } else {
                echo "Error - Faulty actor or recipient address <br>";
                return false;
            }
        } else {
            echo "Error - Missing field ('id'/'actor'/'to'/'ecryptedSecret'/'encryptedPayload'/'encryptedFormatId) <br>";
            return false;
        }
    }
}