<?php
//test
$key = genSymKey();
$test = "hello";
$enc = encSym($test, $key);
echo $enc . "<br>";
$dec = decSym($enc,$key);
echo $dec;

function genKeyPair(){
    // simulated genKey
    $pubKey='pubKey';
    $privKey='privKey';
    return [$pubKey,$privKey];
} // :keypair['public','private']

function genSymKey(){
    //simulated genSymKey
    $symKey='symKey';
    return $symKey;
} // :$symKey

function encSym($data,$symKey){
    //simulated encSym
    $encrypted=$data.$symKey;
    return $encrypted;
} // :$encrypted

function decSym($encrypted,$symKey){
    //simulated decSym
    $len = strlen($symKey);
    $data=substr($encrypted,0,-$len);
    return $data;
} //:$data

?>