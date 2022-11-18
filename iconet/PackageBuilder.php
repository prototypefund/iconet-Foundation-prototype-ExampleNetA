<?php
namespace Iconet;


use InvalidArgumentException;

class PackageBuilder
{

    public static function publicKey_request(string $address): string
    {
        $package['type'] = "PublicKey Request";
        $package['address'] = $address;

        return self::jsonOrThrow($package);
    }

    public static function publicKey_response(string $address, string $publicKey): string
    {
        $package['type'] = "PublicKey Response";
        $package['address'] = $address;
        $package['publicKey'] = $publicKey;

        return self::jsonOrThrow($package);
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
        string $predata
    ): string {
        $package['type'] = "Notification";
        $package['actor'] = $actor;
        $package['to'] = $toAddress;
        $package['encryptedSecret'] = $encryptedSecret;

        $package['predata'] = $predata;

        //optional interoperability-header
        $interoperability['protocol'] = "ExampleNetA";
        $interoperability['contentType'] = "Posting";
        $package['interoperability'] = $interoperability;

        return self::jsonOrThrow($package);
    }

    public static function content_request(string $id, string $actor): string
    {
        $package['type'] = "Content Request";
        $package['actor'] = $actor;
        $package['id'] = $id;

        return self::jsonOrThrow($package);
    }

    public static function content_response(mixed $content, string $formatId, string $actor): string
    {
        $package['type'] = "Content Response";
        $package['actor'] = $actor;
        $package['formatId'] = $formatId;
        $package['content'] = $content;

        return self::jsonOrThrow($package);
    }

    public static function format_request(string $formatId): string
    {
        $package['type'] = "Format Request";
        $package['formatId'] = $formatId;

        return self::jsonOrThrow($package);
    }

    public static function format_response(string $formatId, string $format): string
    {
        $package['type'] = "Format Response";
        $package['formatId'] = $formatId;
        $package['format'] = $format;

        return self::jsonOrThrow($package);
    }

    public static function interaction(
        string $actor,
        string $to,
        string $id,
        string $interactionType,
        string $interaction
    ): string {
        $package['type'] = "Interaction";
        $package['actor'] = $actor;
        $package['to'] = $to;
        $package['id'] = $id;
        $package['interactionType'] = $interactionType;
        $package['interaction'] = $interaction;

        return self::jsonOrThrow($package);
    }

    public static function ack(): string
    {
        $response['type'] = "ACK";

        return self::jsonOrThrow($response);
    }

    public static function error(string $error): string
    {
        $package['type'] = "Error";
        $package['error'] = $error;

        return self::jsonOrThrow($package);
    }

    /**
     * @param array<mixed> $package
     * @return string json-encoded package
     */
    private static function jsonOrThrow(array $package): string
    {
        $result = json_encode($package);
        if(!$result) {
            throw new InvalidArgumentException("Could not convert package to json");
        }
        return $result;
    }

}