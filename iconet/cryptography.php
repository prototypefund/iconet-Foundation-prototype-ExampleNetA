<?php
include_once './iconet/libs/AES.php';

//symetric encrytion:
$AES = null;
$blockSize = 256;

function genKeyPair(){
    // simulated genKey
    $pubKey='pubKey';
    $privKey='priKey';
    return [$pubKey,$privKey];
}

function genSymKey(){
    $size = 128;
    $secret = openssl_random_pseudo_bytes($size);
    return $secret;
}

function encSym($data,$key){
    global $AES;
    global $blockSize;
    $AES = new AES($data, $key, $blockSize);
    return $AES ->encrypt();
}

function decSym($encrypted,$key){
    global $AES;
    global $blockSize;
    $AES = new AES($encrypted, $key, $blockSize);
    return $AES ->decrypt();
}

function genAllCiphers($userLoggedIn,$secret){
    $contacts = get_contacts($userLoggedIn);

    $i = 0;
    foreach ($contacts as $c){
        $cipher['address'] = $c['address'];
        $cipher['cipher'] = encAsym($secret, $c['pubkey'] );
        $ciphers[$i] = $cipher;
        $i++;
    }

    return $ciphers;
}

function encAsym($data,$pubKey){

    return $data.$pubKey;
}

function decAsym($encrypted,$privKey){
    $len = strlen($privKey);
    $data=substr($encrypted,0,-$len);
    return $data;
}

function openCipher($cipher){
    $privkey = get_privkey_by_address($cipher['address']);
    return decAsym($cipher['cipher'], $privkey);
}

?>



