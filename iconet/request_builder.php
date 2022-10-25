<?php
include_once 'formats.php';
include_once 'api_outwards.php';

//TODO sent http requests. (So far .send() of receiving structure is called.)

// provides public key from $address by requesting it from external server
function request_pubkey($address) {
    if(!check_address($address)) return false;
    $url = get_url($address);
    $package['type'] = "Request PublicKey";
    $package['address'] = $address;
    //create a proper json request:
    $msg= Json_encode($package);
    $response = Json_decode(send($url, $msg),true); //function of api_outwards
    return $response['publickey'];
}

function send_notifications($user, $ciphers, $enc_not){
    $package['type'] = "Send Notification";
    $package['sender'] = $user['address'];
    $interop['tech'] = "ExampleNetA";
    $interop['version'] = "0.8";
    $interop['post-type'] = "Posting";
    $interop['interaction'] = "Comments";
    $package['interop'] = $interop;
    $package['notification'] = $enc_not;
    $response = "";
    foreach ($ciphers as $c){
        $address = $c['address'];
        $url = get_url($address);
        $package['to'] = $address;
        $package['cipher'] = $c['cipher'];

        $msg= Json_encode($package);
        $response .= Json_decode(send($url, $msg),true); //function of api_outwards
    }
    return $response;
}

function get_content($id, $from){
    if(!check_address($from)) return false;
    $url = get_url($from);
    $package['type'] = "Request Content";
    $package['address'] = $from;
    //create a proper json request:
    $msg= Json_encode($package);
    $response = Json_decode(send($url, $msg),true); //function of api_outwards
    if ($response['type'] = "Send Content"){
        return $response['content'];
    } else if ($response['type'] = "Request Authentication"){
        return send_authentication($response['challenge']);
    } else{
        var_dump($package);
        echo "<br> Error, invalid response. <br>";
        return false;
    }



}


?>
