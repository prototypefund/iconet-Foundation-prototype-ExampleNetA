<?php

h("Start Testing");

require_once 'config/config.php';


use Iconet\Address;
use Iconet\Crypto;
use Iconet\Database;
use Iconet\Processor;
use Iconet\S2STransmitter;
use Iconet\TemplateProcessor;
use Iconet\UserManager;

$testy = new tester();

$testy->test_encryption();
$testy->init_testdata();
$testy->test_processing();
//$testy->clean_test_data();


h("Done Testing.");


class Tester
{
    private Database $db;
    private Crypto $cryp;
    private Processor $proc;

    private \Iconet\User $alice;
    private \Iconet\User $bob;
    private \Iconet\User $claire;

    public function __construct()
    {
        $this->db = new Database();
        $this->po = new S2STransmitter();
        $this->cryp = new Crypto();
    }

    public function clean_test_data()
    {
        session_unset();
    }

    public function init_testdata()
    {
        h("Init Testdata");
        // create testusers alice, bob & claire
        $this->db->clearTables();
        $um = new UserManager();
        $this->alice = $um->addNewUser('alice');
        $this->bob = $um->addNewUser('bob');
        $this->claire = $um->addNewUser('claire');

        $this->alice->addContact($this->bob);
        $this->alice->addContact($this->claire);

        $this->proc = new Processor($this->alice);
    }

    public function test_processing()
    {
        h("Test Processing:");

        h("Request Public Key of Bob");
        $addressBob = $this->bob->address;
        $pubkey = $this->proc->getExternalPublicKey($addressBob);
        p($pubkey);

        h("Create Content Hello World");
        $this->proc->createPost("Hello World");

        h("Check Inbox of bob");
        $notifs = (new Processor($this->bob))->getNotifications();
        p("Notifs:");
        foreach($notifs as $n) {
            var_dump($n);
        }

        h("Request Content");
        $notif = $notifs[0]; // use bobs notif
        p($this->proc->displayContent($notif['content_id'], new Address($notif['sender']), $notif['secret']));

        h("Request Format");
        p("Request post-comments");
        $format = $this->proc->getFormat("post-comments@neta.localhost");
        p("Received Format:");
        echo htmlspecialchars($format);


        h("Send Interaction");
        p("Make Comment as Bob: Yeey");

        $response = $this->proc->postInteraction(
            "Yeey!",
            $notif['content_id'],
            "bob@bobnet.net",
            $notif['sender'],
            $notif['secret']
        );
        p("Response:");
        var_dump($response);

        h("Request Content, including interactions");
        p($this->proc->displayContent($notif['content_id'], new Address($notif['sender']), $notif['secret']));

        h("Merge Content & Format");
        $content['sender'] = "Alice";
        $content['time'] = "Saturday";
        $content['text'] = "Love you! <br>";
        p("Content:");
        var_dump($content);
        p("Format:");
        var_dump(htmlspecialchars($format));
        p("Merging");
        $result = TemplateProcessor::fillTemplate($format, $content);
        p("Merged:");

        echo $result;
    }


    public function test_encryption()
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