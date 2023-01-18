<?php

namespace Iconet;


use InvalidArgumentException;

class PacketBuilder
{

    public static function publicKey_request(string $address): string
    {
        $packet['type'] = PacketTypes::PUBLIC_KEY_REQUEST;
        $packet['address'] = $address;

        return self::jsonOrThrow($packet);
    }

    public static function publicKey_response(string $address, string $publicKey): string
    {
        $packet['type'] = PacketTypes::PUBLIC_KEY_RESPONSE;
        $packet['address'] = $address;
        $packet['publicKey'] = $publicKey;

        return self::jsonOrThrow($packet);
    }

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
        string $encryptedContent,
        string $encryptedFormatId
    ): string {
        $packet['@context'] = "iconet Notification"; //FIX should not be hardcoded, should be proper json ld
        $packet['id'] = $packetID;
        $packet['actor'] = $actorAddress;
        $packet['to'] = $toAddress;
        $packet['encryptedSecret'] = $encryptedSecret;
        $packet['encryptedContent'] = $encryptedContent;
        $packet['encryptedFormatId'] = $encryptedFormatId;

        //optional interoperability-header
        $interoperability['protocol'] = "ExampleNetA";
        $interoperability['contentType'] = "Posting";
        $packet['interoperability'] = $interoperability;

        return self::jsonOrThrow($packet);
    }

    public static function content_request(string $id, string $actor): string
    {
        $packet['type'] = PacketTypes::CONTENT_REQUEST;
        $packet['actor'] = $actor;
        $packet['id'] = $id;

        return self::jsonOrThrow($packet);
    }

    public static function content_response(
        string $content,
        string $formatId,
        mixed $interactions,
        string $actor
    ): string {
        $packet['type'] = PacketTypes::CONTENT_RESPONSE;
        $packet['actor'] = $actor;
        $packet['formatId'] = $formatId;
        $packet['content'] = $content;
        $packet['interactions'] = $interactions;

        return self::jsonOrThrow($packet);
    }

    public static function format_request(string $formatId): string
    {
        $packet['type'] = PacketTypes::FORMAT_REQUEST;
        $packet['formatId'] = $formatId;

        return self::jsonOrThrow($packet);
    }

    public static function format_response(string $formatId, string $format): string
    {
        $packet['type'] = PacketTypes::FORMAT_RESPONSE;
        $packet['formatId'] = $formatId;
        $packet['format'] = $format;

        return self::jsonOrThrow($packet);
    }

    public static function interaction(
        string $actor,
        string $to,
        string $id,
        string $payload
    ): string {
        $packet['type'] = PacketTypes::INTERACTION;
        $packet['actor'] = $actor;
        $packet['to'] = $to;
        $packet['id'] = $id;
        $packet['payload'] = $payload;

        return self::jsonOrThrow($packet);
    }

    public static function ack(): string
    {
        return "ACK";
    }

    public static function error(string $error): string
    {
        return $error;
    }

    /**
     * @param array<mixed> $packet
     * @return string json-encoded packet
     */
    private static function jsonOrThrow(array $packet): string
    {
        $result = json_encode($packet);
        if(!$result) {
            throw new InvalidArgumentException("Could not convert packet to json");
        }
        return $result;
    }

}