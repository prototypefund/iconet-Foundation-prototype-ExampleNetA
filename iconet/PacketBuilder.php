<?php

namespace Iconet;


class PacketBuilder
{

    const CONTEXT = "https://ns.iconet-foundation.org#";

    public static function EncryptedNotification(
        string $packetID,
        string $actorAddress,
        string $toAddress,
        string $encryptedSecret,
        string $encryptedPayload
    ): string {
        $packet['@context'] = self::CONTEXT;
        $packet['@type'] = "EncryptedPacket";
        $packet['@id'] = $packetID;
        $packet['actor'] = $actorAddress;
        $packet['to'] = $toAddress;
        $packet['encryptedSecret'] = $encryptedSecret;
        $packet['encryptedPayload'] = $encryptedPayload;

        return json_encode($packet);
    }

    public static function Notification(
        string $packetID,
        string $actorAddress,
        string $toAddress,
        array $payload
    ): string {
        $packet['@context'] = self::CONTEXT;
        $packet['@type'] = "Packet";
        $packet['@id'] = $packetID;
        $packet['actor'] = $actorAddress;
        $packet['to'] = $toAddress;
        $packet['interpreterManifests'] = $payload['interpreterManifests'];
        $packet['content'] = $payload['content'];

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

    // prepares Payload to be processable by foreign client
    public static function preparePayload(string $manifestUri, array $payload): array
    {
        $plainType = "text/plain";
        $inputType = "application/neta+json";
        $targetType = "application/iconet+html";
        $manifestSha512Hash = "<sha-512 hash of the manifest document linked> (TODO)";
        //fill in ManifestData
        $interpreterManifests['manifestUri'] = $manifestUri;
        $interpreterManifests['inputTypes'] = array($inputType, $plainType);
        $interpreterManifests['targetTypes'] = array($targetType);
        $interpreterManifests['sha-512'] = $manifestSha512Hash;

        $packet['interpreterManifests'] = array($interpreterManifests);

        //fill in ContentData
        $content1["packetType"] = $inputType;
        $content1["payload"] = $payload; //formated to fit into netA iframe

        $content2["packetType"] = $plainType;
        $content2["payload"] = json_encode($payload); //payload2String, TODO $payload['preview']
        $content = array($content1, $content2);

        $packet['content'] = $content;

        return $packet;
    }


}



