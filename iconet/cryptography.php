<?php
include_once './iconet/libs/AES.php';


//symmetric encryption:

$AES = null;
$blockSize = 256;

function genKeyPair(){
    $privKey = openssl_pkey_new();
    $pubKey_pem = openssl_pkey_get_details($privKey)['key'];
    echo "pubKey as string:<br>" . $pubKey_pem . "<br>";
    $pubKey = openssl_pkey_get_public($pubKey_pem);
    return [$pubKey,$privKey];
}

function verSignature($message,$privKey){
    openssl_sign($message, $signature, $privKey, OPENSSL_ALGO_SHA1);
    return $signature;
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
    if ($contacts == null) return null; //you need contacts generate something for them
    $i = 0;
    foreach ($contacts as $c){
        $cipher['address'] = $c['address'];
        $cipher['cipher'] = encAsym($secret, $c['pubkey'] );
        $ciphers[$i] = $cipher;
        $i++;
    }
    return $ciphers;
}

function encAsym($symKey,$pubKey){
    openssl_public_encrypt($symKey, $encSymKey, $pubKey, OPENSSL_PKCS1_PADDING);
    //OPENSSL_PKCS1_PADDING is the default but setting explicitly because that's what we expect on the server
    return $encSymKey;
}

function decAsym($encSecret,$privKey){
    openssl_private_decrypt($encSecret, $decSecret, $privKey, OPENSSL_PKCS1_PADDING);
    return $decSecret;
}

function openCipher($cipher){
    $privKey = get_privkey_by_address($cipher['address']);
    return decAsym($cipher['cipher'], $privKey);
}

?>

