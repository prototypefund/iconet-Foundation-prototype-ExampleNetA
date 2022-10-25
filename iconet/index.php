<?php
// i accept and handle incoming requests!

include_once("database.php");
include_once("formats.php");

//TODO this function is part of the current server 2 server internal workaround. Will be replaced by https requests.
function receive($msg){
    // I know how to get things done!
    echo "Received:".$msg;
    $package = json_decode($msg, true);
    $type = check_package($package);

    switch ($type){
        case "Request publicKey":
            $pubKey = get_pubkey_by_address($package['address']);
            if($pubKey){
                $response['type'] = "Response publicKey";
                $response['address'] = $package['address'];
                $response['publicKey'] = $pubKey;
                return json_encode($response);

            } else {
                //TODO provide Error Code on HTTP level.
                $response['type'] = "Response publicKey";
                $response['address'] = $package['address'];
                $response['publicKey'] = "Unknown";
                return json_encode($response);
            }
            break;

        case "Send notification":
            // TODO decode notification, verify signature, save in db
            $response['type'] = "Response notification";
            $response['msg'] = "Your request is great, but sadly I can't process it yet.";
            return json_encode($response);
        break;

        case "Request format":
            //TODO provide requested format
            $response['type'] = "Response format";
            $response['msg'] = "Your request is great, but sadly I can't process it yet.";
            return json_encode($response);
        break;

        case "Send interaction":
            //TODO decode content, verify signature, append to content
            $response['type'] = "Response interaction";
            $response['msg'] = "Your request is great, but sadly I can't process it yet.";
            return json_encode($response);
        break;

        case "Request content":
            //TODO decode content, verify signature, append to content
            $response['type'] = "Request content";
            $response['msg'] = "Your request is great, but sadly I can't process it yet.";
            return json_encode($response);
            break;

        default:
            //TODO provide Error Code on HTTP level.
            $response['type'] = "Error";
            $response['Error'] = $type;
            return json_encode($response);
    }

    echo "This section should never be reached. <br>";
    return false;
}
?>