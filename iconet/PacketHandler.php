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
    public static function checkPacket(mixed $packet): PacketTypes
    {
        if(!isset($packet->type)) {
            echo "Error - Must set field 'type' <br>";
            return PacketTypes::INVALID;
        }

        $type = PacketTypes::tryFrom($packet->type);
        if(!$type) {
            return PacketTypes::INVALID;
        }

        switch($type) {
            case PacketTypes::PUBLICKEY_REQUEST:
                //check if non-optional variables are set.
                if(isset($packet->address)) {
                    //check if non-optional variables are proper
                    if(Address::validate($packet->address)) {
                        //all conditions for publicKey request are met.
                        return PacketTypes::PUBLICKEY_REQUEST;
                    } else {
                        echo "Error - Faulty address in publicKey request <br>" . $packet->address . "<br>";
                        return PacketTypes::INVALID;
                    }
                } else {
                    echo "Error - Missing address in publicKey request <br>";
                    return PacketTypes::INVALID;
                }

            case PacketTypes::PUBLICKEY_RESPONSE:
                //check if non-optional variables are set.
                if(isset($packet->address) && isset($packet->publicKey)) {
                    //check if non-optional variables are proper
                    if(Address::validate($packet->address)) {
                        //all conditions for publicKey request are met.
                        return PacketTypes::PUBLICKEY_RESPONSE;
                    } else {
                        echo "Error - Faulty address in publicKey response <br>" . $packet->address . "<br>";
                        return PacketTypes::INVALID;
                    }
                } else {
                    echo "Error - Missing field in publicKey response ('address'/'publicKey') <br>";
                    return PacketTypes::INVALID;
                }

            case PacketTypes::NOTIFICATION:
                //check if non-optional variables are set.
                if(isset($packet->actor) && isset($packet->predata) && isset($packet->encryptedSecret) && isset($packet->to)) {
                    //check if non-optional variables are proper (can't check notification content, potentially encrypted)
                    if(Address::validate($packet->actor) && Address::validate($packet->to)) {
                        //all conditions for type send notification are met.
                        return PacketTypes::NOTIFICATION;
                    } else {
                        echo "Error - Faulty actor or recipient address <br>";
                        return PacketTypes::INVALID;
                    }
                } else {
                    echo "Error - Missing field in notification ('actor'/'predata'/'ecryptedSecret'/'to') <br>";
                    return PacketTypes::INVALID;
                }

            case PacketTypes::CONTENT_REQUEST:
                //check if non-optional variables are set.
                if(isset($packet->id) && isset($packet->actor)) {
                    return PacketTypes::CONTENT_REQUEST;
                } else {
                    echo "Error - Missing field in content request ('id'/'actor') <br>";
                    return PacketTypes::INVALID;
                }

            case PacketTypes::CONTENT_RESPONSE:
                //check if non-optional variables are set.
                if(isset($packet->actor) && isset($packet->formatId) && isset($packet->content)) {
                    if(Address::validate($packet->actor)) {
                        //all conditions for type send interaction are met.
                        return PacketTypes::CONTENT_RESPONSE;
                    } else {
                        echo "Error - Faulty actor address <br>";
                        return PacketTypes::INVALID;
                    }
                } else {
                    echo "Error - Missing field in content response ('actor'/'formatId'/'content')<br>";
                    return PacketTypes::INVALID;
                }

            case PacketTypes::FORMAT_REQUEST:
                //check if non-optional variables are set.
                if(isset($packet->formatId)) {
                    //check if non-optional variables are proper (can't check notification content, potentially encrypted)
                    if($packet->formatId) {  //TODO specify syntax for format ids
                        //all conditions for type request format are met.
                        return PacketTypes::FORMAT_REQUEST;
                    } else {
                        echo "Error - Faulty formatId, can only provide 'post-comments'<br>";
                        return PacketTypes::INVALID;
                    }
                } else {
                    echo "Error - Missing field 'formatId' in format request <br>";
                    return PacketTypes::INVALID;
                }

            case PacketTypes::FORMAT_RESPONSE:
                //check if non-optional variables are set.
                if(isset($packet->formatId) && isset($packet->format)) {
                    //check if non-optional variables are proper (can't check notification content, potentially encrypted)
                    if($packet->formatId) { //TODO specify syntax for format ids
                        //all conditions for type request format are met.
                        return PacketTypes::FORMAT_RESPONSE;
                    } else {
                        echo "Error - Faulty format formatId, can only provide 'post-comments' <br>";
                        return PacketTypes::INVALID;
                    }
                } else {
                    echo "Error - Missing field in format response ('formatId'/'format')<br>";
                    return PacketTypes::INVALID;
                }

            case PacketTypes::INTERACTION:
                //check if non-optional variables are set.
                if(isset($packet->id) && isset($packet->actor) && isset($packet->payload)) {
                    //check if non-optional variables are proper (can't check notification content, potentially encrypted)
                    if(Address::validate($packet->actor)) {
                        //all conditions for type send interaction are met.
                        return PacketTypes::INTERACTION;
                    } else {
                        echo "Error - Faulty actor address<br>";
                        return PacketTypes::INVALID;
                    }
                } else {
                    echo "Error - Missing field in interaction ('id'/'actor'/'payload')<br>";
                    return PacketTypes::INVALID;
                }

            default:
                echo "Unknown packet type: <br>" . $packet->type . "<br>";
                return PacketTypes::INVALID;
        }

    }

}