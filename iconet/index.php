<?php
// i accept and handle incoming requests!

include_once("iconet/database.php");

//this function is part of the current server 2 server internal workarround. Will be replaced by https requests.
function receive($msg){
    // i know how to get things done!
    $package = json_decode($msg, true);
    if ($package['type'] == "Request PublicKey")
    {
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
    }
    else return "Error: Unknown Request";
}
?>