<?php

require_once 'format_handlers.php';
require_once 'api_outwards.php';
// provides public key from $address by requesting it from external server
function request_pubkey($address)
{
    if(!check_address($address)) {
        return false;
    }
    $url = get_url($address);
    $package['type'] = "Request PublicKey";
    $package['address'] = $address;
    //create a proper json request:
    $msg = Json_encode($package);
    $response = Json_decode(send($url, $msg), true); //function of api_outwards
    return $response['publickey'];
}

?>
