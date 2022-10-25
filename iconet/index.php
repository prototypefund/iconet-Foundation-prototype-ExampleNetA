<?php
// i accept and handle incoming requests!

include_once("database.php");
include_once("formats.php");

//TODO this function is part of the current server 2 server internal workarround. Will be replaced by https requests.
function receive($msg){
    // i know how to get things done!
    $package = json_decode($msg, true);
    $type = check_package($package);

    switch ($type){
        case "Request PublicKey":
            $pubkey = get_pubkey_by_address($package['address']);
            if($pubkey){
                $response['type'] = "Response PublicKey";
                $response['address'] = $package['address'];
                $response['publickey'] = $pubkey;
                return json_encode($response);

            } else {
                //TODO provide Error Code on HTTP level.
                $response['type'] = "Response PublicKey";
                $response['address'] = $package['address'];
                $response['publickey'] = "Unknown";
                return json_encode($response);
            }
            break;

        case "Send Notification":
            // TODO decode notification, verifiy signature, save in db
            $response['type'] = "Response Notification";
            $response['msg'] = "Your Request is great, but sadly I can't process it yet.";
            return json_encode($response);
       break;

        case "Request Format":
            //TODO provide requested format
            $response['type'] = "Response Format";
            $response['msg'] = "Your Request is great, but sadly I can't process it yet.";
            return json_encode($response);
        break;

        case "Send Interaction":
            //TODO decode content, verify signature, append to content
            $response['type'] = "Response Interaction";
            $response['msg'] = "Your Request is great, but sadly I can't process it yet.";
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