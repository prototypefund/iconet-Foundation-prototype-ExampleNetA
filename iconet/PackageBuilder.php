<?php
namespace Iconet;


//TODO user objects or make methods static
class PackageBuilder
{

    function publicKey_request(string $address): bool|string
    {
        $package['type'] = "PublicKey Request";
        $package['address'] = $address;

        return Json_encode($package);
    }

    function publicKey_response(string $address, string $publicKey): bool|string
    {
        $package['type'] = "PublicKey Response";
        $package['address'] = $address;
        $package['publicKey'] = $publicKey;

        return Json_encode($package);
    }

    /**
     * @param string $actor
     * @param array<string> $encryptedSecret
     * @param string $predata
     * @return bool|string
     */
    function notification(string $actor, array $encryptedSecret, string $predata): bool|string
    {
        $package['type'] = "Notification";
        $package['actor'] = $actor;
        $package['to'] = $encryptedSecret['address'];
        $package['encryptedSecret'] = $encryptedSecret['encryptedSecret'];

        $package['predata'] = $predata;

        //optional interoperability-header
        $interoperability['protocol'] = "ExampleNetA";
        $interoperability['contentType'] = "Posting";
        $package['interoperability'] = $interoperability;

        return Json_encode($package);
    }

    function content_request(string $id, string $actor): bool|string
    {
        $package['type'] = "Content Request";
        $package['actor'] = $actor;
        $package['id'] = $id;

        return Json_encode($package);
    }

    function content_response($content, $formatId, $actor): bool|string
    {
        $package['type'] = "Content Response";
        $package['actor'] = $actor;
        $package['formatId'] = $formatId;
        $package['content'] = $content;

        return Json_encode($package);
    }

    function format_request(string $formatId): bool|string
    {
        $package['type'] = "Format Request";
        $package['formatId'] = $formatId;

        return Json_encode($package);
    }

    function format_response(string $formatId, string $format): bool|string
    {
        $package['type'] = "Format Response";
        $package['formatId'] = $formatId;
        $package['format'] = $format;

        return Json_encode($package);
    }

    function interaction(string $actor, string $to, string $id, string $interactionType, string $interaction): bool|string
    {
        $package['type'] = "Interaction";
        $package['actor'] = $actor;
        $package['to'] = $to;
        $package['id'] = $id;
        $package['interactionType'] = $interactionType;
        $package['interaction'] = $interaction;

        return Json_encode($package);
    }

    function ack() : bool|string
    {
        $response['type'] = "ACK";

        return json_encode($response);
    }

    function error(string $error): bool|string
    {
        $package['type'] = "Error";
        $package['error'] = $error;

        return Json_encode($package);
    }

}