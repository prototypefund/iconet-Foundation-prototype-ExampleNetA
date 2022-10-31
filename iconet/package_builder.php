<?php

class package_builder
{

    //active packages

    function request_pubkey($address): bool|string
    {
        $package['type'] = "Request Publickey";
        $package['address'] = $address;

        return Json_encode($package);
    }

    function send_notification($sender, $cipher, $enc_not): bool|string
    {
        $package['type'] = "Send Notification";
        $package['sender'] = $sender;
        $package['to'] = $cipher['address'];
        $package['cipher'] = $cipher['cipher'];

        $package['predata'] = $enc_not;

        //optional interop-header
        $interop['tech'] = "ExampleNetA";
        $interop['version'] = "0.8";
        $interop['post-type'] = "Posting";
        $interop['interaction'] = "Comments";
        $package['interop'] = $interop;
        return Json_encode($package);
    }

    function request_content($id, $from): bool|string
    {
        $package['type'] = "Request Content";
        $package['address'] = $from;
        $package['id'] = $id;

        return Json_encode($package);
    }

    function request_format($format): bool|string
    {
        $package['type'] = "Request Format";
        $package['format'] = $format;

        return Json_encode($package);
    }

    function send_interaction($sender, $to, $id, $int_type, $enc_int ): bool|string
    {
        $package['type'] = "Send Interaction";
        $package['$sender'] = $sender;
        $package['$to'] = $to;
        $package['$id'] = $id;
        $package['int_type'] = $int_type;
        $package['enc_int'] = $enc_int;

        return Json_encode($package);
    }

    // response packages

    function send_publickey($address, $pubkey): bool|string
    {
        $package['type'] = "Send Publickey";
        $package['address'] = $address;
        $package['publickey'] = $pubkey;

        return Json_encode($package);
    }

    function send_format($name, $format): bool|string
    {
        $package['type'] = "Send Format";
        $package['name'] = $name;
        $package['format'] = $format;

        return Json_encode($package);
    }

    function send_error($error): bool|string
    {
        $package['type'] = "Error";
        $package['error'] = $error;

        return Json_encode($package);
    }




}