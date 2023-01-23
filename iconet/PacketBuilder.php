<?php

namespace Iconet;


class PacketBuilder
{

    /**
     * @param string $actor
     * @param string $toAddress
     * @param string $encryptedSecret
     * @param string $predata
     * @return string
     */

    public static function notification(
        string $packetID,
        string $actorAddress,
        string $toAddress,
        string $encryptedSecret,
        string $encryptedPayload,
        string $encryptedFormatId
    ): string {
        $packet['@context'] = "iconet Notification"; //FIX should not be hardcoded, should be proper json ld
        $packet['id'] = $packetID;
        $packet['actor'] = $actorAddress;
        $packet['to'] = $toAddress;
        $packet['encryptedSecret'] = $encryptedSecret;
        $packet['encryptedPayload'] = $encryptedPayload;
        $packet['encryptedFormatId'] = $encryptedFormatId;


        //optional interoperability-header
        $interoperability['protocol'] = "ExampleNetA";
        $interoperability['contentType'] = "Posting";
        $packet['interoperability'] = $interoperability;

        return json_encode($packet);
    }

    public static function publicKeyRequest(string $address)
    {
        $packet['@context'] = "iconet PublicKey Request";
        $packet['address'] = $address;
        return json_encode($packet);
    }

    public static function ack(): string
    {
        return "ACK";
    }

    public static function error(string $error): string
    {
        return $error;
    }


}