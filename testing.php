<?php

h("Start Testing");

p("Includes:");

include_once "./iconet/cryptography.php";
include_once "./config/config.php";
include_once "./iconet/database.php";

h("Encryption");

$message = "Hello Bob, here are some funny symbols: äöü123{}ß. Love you!";
$sender_address = "alicenet.net";
$receiver_address = "bobnet.org";

p( $sender_address . ' to ' . $receiver_address . ':<br>' . $message);

$aliceKeyPair = genKeyPair();
$bobKeyPair = genKeyPair();
$symkey = genSymKey();

p("Alice Keypair:");
var_dump($aliceKeyPair);
p("Bob Keypair:");
var_dump($bobKeyPair);
p("Symetric Secret:");
var_dump($symkey);


h("Start Encryption:");

$encrypted_content = encSym($message, $symkey);

p("encrypted_content");
var_dump($encrypted_content);

$encrypted_secret = encAsym($symkey, $bobKeyPair[0]);

p("encrypted_secret");
var_dump($encrypted_secret);

$decrypted_secret = decAsym($encrypted_secret, $bobKeyPair[1]);

p("decrypted_secret");
var_dump($decrypted_secret);

$decrypted_content = decSym($encrypted_content, $decrypted_secret);
p("decrypted_content");
var_dump($decrypted_content);


p("Test symetric en- and decryption with multiple keys");

$raw = array('person','woman','man', 'camera', 'tv' );
$secret = array(genSymKey(),genSymKey(),genSymKey(),genSymKey(),genSymKey());
$i = 0;
foreach ($raw as $r){
    $encrypted_array[$i] = encSym($r, $secret[$i]);
    $i++;
}
$i = 0;
foreach ($encrypted_array as $e){
    $decrypted_array[$i]= decSym($e, $secret[$i]);
    $i++;
}

if ($raw == $decrypted_array){
    p("Successfull.");
} else{
    p("Error");
}

p("Test repeated en- and decryption with same key");
$secret = genSymKey();
$raw2 = array('yellow', 'yellow', 'yellow', 'red');
$i = 0;
foreach ($raw2 as $r){
    $encrypted[$i] = encSym($r, $secret);
    $i++;
}
$i = 0;
foreach ($encrypted as $e){
    $decrypted[$i]= decSym($e, $secret);
    $i++;
}
if ($raw2 == $decrypted){
    p("Successfull.");
} else{
    p("Error");
}




h("Done Testing.");


function p($text){
    echo "<p>" . strval($text) . "</p>" ;
}

function h($text){
    echo "<h3>" . strval($text) . "</h3>" ;
}