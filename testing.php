<?php

h("Start Testing");

p("Includes:");

require_once "./iconet/database.php";
require_once "./iconet/cryptograph.php";
require_once "./iconet/processor.php";
require_once "./iconet/package_builder.php";
require_once "./iconet/post_office.php";

$testy = new tester();

//$testy->test_encryption();

$testy->init_testdata();

$testy->test_processing();

//$testy->clean_test_data();

h("Done Testing.");


class tester{
protected $db;
protected $cryp;
protected $po;
protected $pb;
protected $proc;

    public function __construct()
    {
        $this->db = new database();
        $this->po = new post_office();
        $this->cryp = new cryptograph();
        $this->pb = new package_builder();
    }

    function clean_test_data(){
    }

    function init_testdata()
    {
        h("Init Testdata");
        // create testusers alice, bob & claire
        $aliceKey = $this->cryp->genKeyPair();
        $bobKey = $this->cryp->genKeyPair();
        $claireKey = $this->cryp->genkeyPair();
        $this->db->clear_tables();
        $this->db->add_user("alice_tester", "alice@alicenet.net", $aliceKey[0],$aliceKey[1]);
        $this->db->add_user("bob_tester", "bob@bobnet.org", $bobKey[0], $bobKey[1]);
        $this->db->add_user("claire_tester", "claire@clairenet.de", $claireKey[0], $claireKey[1]);
        $this->db->add_contact("alice_tester", "bob@bobnet.org", $bobKey[0]);
        $this->db->add_contact("alice_tester", "claire@clairenet.de", $claireKey[0]);

        $this->proc = new processor("alice_tester");

    }

    function test_processing(){
        h("Test Processing:");

        h("Request Pubkey of Bob");
        $this->proc->get_external_pubkey("bob@bobnet.org");

        h("Create Content Hello World");
        $this->proc->create_iconet_post("Hello World");

        h("Check Inbox of bob");
        $notifs = $this->proc->check_inbox("bob_tester");
        p("Notifs:");
        foreach ($notifs as $n){
            var_dump($n);
        }

        h("Request Content");

        h("Request Format");
        p("Request post-comment");
        $response = $this->proc->get_format("post-comments");

        h("Send Interaction");
    }


    function test_encryption()
    {


        h("Encryption");

        $message = "Hello Bob, here are some funny symbols: äöü123{}ß. Love you!";
        $sender_address = "alicenet.net";
        $receiver_address = "bobnet.org";

        p($sender_address . ' to ' . $receiver_address . ':<br>' . $message);

        $aliceKeyPair = $this->cryp->genKeyPair();
        $bobKeyPair = $this->cryp->genKeyPair();
        $symkey = $this->cryp->genSymKey();

        p("Alice Keypair:");
        var_dump($aliceKeyPair);
        p("Bob Keypair:");
        var_dump($bobKeyPair);
        p("Symetric Secret:");
        var_dump($symkey);

        h("Start Encryption:");

        $encrypted_content = $this->cryp->encSym($message, $symkey);

        p("encrypted_content");
        var_dump($encrypted_content);

        $encrypted_secret = $this->cryp->encAsym($symkey, $bobKeyPair[0]);

        p("encrypted_secret");
        var_dump($encrypted_secret);

        $decrypted_secret = $this->cryp->decAsym($encrypted_secret, $bobKeyPair[1]);

        p("decrypted_secret");
        var_dump($decrypted_secret);

        $decrypted_content = $this->cryp->decSym($encrypted_content, $decrypted_secret);
        p("decrypted_content");
        var_dump($decrypted_content);

    }


}



function p($text){
    echo "<p>" . strval($text) . "</p>" ;
}

function h($text){
    echo "<h3>" . strval($text) . "</h3>" ;
}