<?php
h("Start Testing");

p("Includes:");

require_once "./iconet/Database.php";
require_once "./iconet/Crypto.php";
require_once "./iconet/Processor.php";
require_once "./iconet/PackageBuilder.php";
require_once "./iconet/PostOffice.php";
use Iconet\Database;
use Iconet\Crypto;
use Iconet\Processor;
use Iconet\PackageBuilder;
use Iconet\PostOffice;

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
        $this->db = new Database();
        $this->po = new PostOffice();
        $this->cryp = new Crypto();
        $this->pb = new PackageBuilder();
    }

    function clean_test_data(){
        session_unset();
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

        $this->proc = new Processor("alice_tester");

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
        $notif = $notifs[0]; // use bobs notif

        p($this->proc->display_content($notif['content_id'], $notif['sender'], $notif['secret']));
        h("Request Format");
        p("Request post-comments");
        $response = $this->proc->get_format("post-comments");
        p("Received Format:");
        echo htmlspecialchars($response);
        h("Send Interaction");
        p("Make Comment as Bob: Yeey");

        $response = $this->proc->post_interaction("Yeey!", $notif['content_id'], "bob@bobnet.net", $notif['sender'], "comment", $notif['secret'] );
        p("Response:");
        var_dump($response);

        h("Request Content, including interactions");
        p($this->proc->display_content($notif['content_id'], $notif['sender'], $notif['secret']));


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