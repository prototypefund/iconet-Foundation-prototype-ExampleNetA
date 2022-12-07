<?php

namespace Iconet;


use InvalidArgumentException;

class PacketBuilder
{

    public static function publicKey_request(string $address): string
    {
        $packet['type'] = "PublicKey Request";
        $packet['address'] = $address;

        return self::jsonOrThrow($packet);
    }

    public static function publicKey_response(string $address, string $publicKey): string
    {
        $packet['type'] = "PublicKey Response";
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
        string $actor,
        string $toAddress,
        string $encryptedSecret,
        string $encryptedPredata
    ): string {
        $packet['type'] = "Notification";
        $packet['actor'] = $actor;
        $packet['to'] = $toAddress;
        $packet['encryptedSecret'] = $encryptedSecret;

        $packet['predata'] = $encryptedPredata;

        //optional interoperability-header
        $interoperability['protocol'] = "ExampleNetA";
        $interoperability['contentType'] = "Posting";
        $packet['interoperability'] = $interoperability;

        return self::jsonOrThrow($packet);
    }

    public static function content_request(string $id, string $actor): string
    {
        $packet['type'] = "Content Request";
        $packet['actor'] = $actor;
        $packet['id'] = $id;

        return self::jsonOrThrow($packet);
    }

    public static function content_response(mixed $content, string $formatId, string $actor): string
    {
        $packet['type'] = "Content Response";
        $packet['actor'] = $actor;
        $packet['formatId'] = $formatId;
        $packet['content'] = $content;

        return self::jsonOrThrow($packet);
    }

    public static function format_request(string $formatId): string
    {
        $packet['type'] = "Format Request";
        $packet['formatId'] = $formatId;

        return self::jsonOrThrow($packet);
    }

    public static function format_response(string $formatId, string $format): string
    {
        $packet['type'] = "Format Response";
        $packet['formatId'] = $formatId;
        $packet['format'] = $format;

        return self::jsonOrThrow($packet);
    }

    public static function interaction(
        string $actor,
        string $to,
        string $id,
        string $interaction
    ): string {
        $packet['type'] = "Interaction";
        $packet['actor'] = $actor;
        $packet['to'] = $to;
        $packet['id'] = $id;
        $packet['interaction'] = $interaction;

        return self::jsonOrThrow($packet);
    }

    public static function ack(): string
    {
        $response['type'] = "ACK";

        return self::jsonOrThrow($response);
    }

    public static function error(string $error): string
    {
        $packet['type'] = "Error";
        $packet['error'] = $error;

        return self::jsonOrThrow($packet);
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