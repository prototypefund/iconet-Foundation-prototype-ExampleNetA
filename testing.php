<?php

h("Start Testing");

p("Includes:");

include_once "./iconet/cryptography.php";
include_once "./config/config.php";
include_once "./iconet/database.php";

init_testdata();
test_encryption();
//test_processing();
// test_encryption();




$privKey = openssl_pkey_new();
var_dump($privKey);
$pubKey_pem = openssl_pkey_get_details($privKey)['key'];
echo "pubKey as string:<br>" . $pubKey_pem . "<br>";
$pubKey = openssl_pkey_get_public($pubKey_pem);
echo "pubKey:<br>";
var_dump($pubKey);
echo "<br>privKey:<br>";
var_dump($privKey);
echo "<br>";

clean_test_data();

h("Done Testing.");

function clean_test_data(){
    session_unset();
}

function init_testdata(){
    global $userLoggedIn;
    $userLoggedIn = "alice_tester";

    // create testusers alice, bob & claire
    clear_tables();
        add_user("alice_tester", "alice@alicenet.net");
        add_user("bob_tester", "bob@bobnet.org");
        add_user("claire_tester", "claire@clairenet.de");
        $bobKey = genKeyPair();
        $claireKey = genkeyPair();
        add_contact("alice_tester", "bob@bobnet.org", $bobKey[0]);
        add_contact("alice_tester", "claire@clairenet.de", $claireKey[0]);
}

function test_processing(){
    h("Test Processing:");#
    p("Post String: 'Test Posting!'");
    var_dump( create_iconet_post("Test Posting!"));
}


function test_encryption()
{


    h("Encryption");

    $message = "Hello Bob, here are some funny symbols: äöü123{}ß. Love you!";
    $sender_address = "alicenet.net";
    $receiver_address = "bobnet.org";

    p($sender_address . ' to ' . $receiver_address . ':<br>' . $message);

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

    $raw = array('person', 'woman', 'man', 'camera', 'tv');
    $secret = array(genSymKey(), genSymKey(), genSymKey(), genSymKey(), genSymKey());
    $i = 0;
    foreach ($raw as $r) {
        $encrypted_array[$i] = encSym($r, $secret[$i]);
        $i++;
    }
    $i = 0;
    foreach ($encrypted_array as $e) {
        $decrypted_array[$i] = decSym($e, $secret[$i]);
        $i++;
    }

    if ($raw == $decrypted_array) {
        p("Successfull.");
    } else {
        p("Error");
    }

    p("Test repeated en- and decryption with same key");
    $secret = genSymKey();
    $raw2 = array('yellow', 'yellow', 'yellow', 'red');
    $i = 0;
    foreach ($raw2 as $r) {
        $encrypted[$i] = encSym($r, $secret);
        $i++;
    }
    $i = 0;
    foreach ($encrypted as $e) {
        $decrypted[$i] = decSym($e, $secret);
        $i++;
    }
    if ($raw2 == $decrypted) {
        p("Successfull.");
    } else {
        p("Error");
    }

// create testusers alice, bob & claire
    clear_tables();
    if (!get_globaladdress("alice_tester")) {
        p("Create new test entries");
        add_user("alice_tester", "alice@alicenet.net");
        add_user("bob_tester", "bob@bobnet.org");
        add_user("claire_tester", "claire@clairenet.de");
        $bobKey = genKeyPair();
        $claireKey = genkeyPair();
        add_contact("alice_tester", "bob@bobnet.org", $bobKey[0]);
        add_contact("alice_tester", "claire@clairenet.de", $claireKey[0]);

    }
    p("gen all ciphers for alice:");
    var_dump(genAllCiphers("alice_tester", genSymKey()));

}



function p($text){
    echo "<p>" . strval($text) . "</p>" ;
}

function h($text){
    echo "<h3>" . strval($text) . "</h3>" ;
}