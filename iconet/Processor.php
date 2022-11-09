<?php
namespace Iconet;

use function PHPUnit\Framework\assertFileIsWritable;

require_once 'config/config.php';

class Processor
{
    //logged in user
    protected mixed $user;

    //helper classes
    protected Database $db; //database
    protected PostOffice $po; //postoffice
    protected PackageBuilder $pb; //package_builder
    protected Crypto $cryp; //cryptograph
    protected PackageHandler $ph;
    protected string $path_postings;

    public function __construct($userLoggedIn)
    {
        $this->db = new Database();
        $this->po = new PostOffice();
        $this->cryp = new Crypto();
        $this->pb = new PackageBuilder();
        $this->ph = new PackageHandler();
        $this->path_postings = $_ENV['STORAGE'];

        $user = $this->db->get_user_by_name($userLoggedIn);
        $this->setUser($user);
    }


    /**
     * @param mixed $user
     */public function setUser(mixed $user): void
{
    $this->user = $user;
}
    function get_external_publicKey($address){
        $message = $this->pb->publicKey_request($address);
        $response = $this->po->send("url", $message);
    }

    function create_iconet_post($content){
        //generate random ID
        $done = false;
        $id ="";
        while(!$done){
            $id = md5(rand()); // generate random ID
            if(!$this->db->get_post_by_ID($id)) $done = true; //Repeat if ID already in use (unlikely but possible)
        }
        //generate notification
        $subject = $content . "- notification text"; //for testing content is only string

        $predata['id'] = $id;
        $predata['subject'] = $subject;

        //encrypt notification & content
        $secret = $this->cryp->genSymKey();
        $encryptedNotif = $this->cryp->encSym(Json_encode($predata),$secret);
        $encryptedContent = $this->cryp->encSym($content,$secret);

        //save content
        $file = fopen($this->path_postings.$id.".txt", "w") or die("Cannot open file.");
        // Write data to the file
        fwrite($file, $encryptedContent);
        // Close the file
        fclose($file);

        //save post in db
        $this->db->add_post($id, $this->user['username'], $secret);

        //generate and send notifications

        $contacts = $this->db->get_contacts($this->user['username']);
        if ($contacts == null) {
            echo "<br>You need contacts generate something for them! <br>";
            return null;
        }

        $encryptedSecret= $this->cryp->genAllCiphers($contacts, $secret);
        foreach ($encryptedSecret as $c){
            $notifPackage = $this->pb->notification($this->user['address'], $c, $encryptedNotif);
            $response = $this->po->send("url", $notifPackage);
        }
    }

    function check_inbox($username){
         return $this->db->get_notifications($username);
    }

    function display_content($id, $actor, $secret){
         $message = $this->pb->content_request($id, $actor);
         $response = $this->po->send("url", $message);
         $package = Json_decode($response,true);
        if (0!=strcmp($this->ph->check_package($package),"Content Response")){
            echo "Error - invalid response Package. Expected: Content Response";
            return "Error - invalid response Package. Expected: Content Response";
        }
        $content = $package['content'];
        $mainContent = $this->cryp->decSym($content['content'], $secret);
        if (isset($content['interactions'])){
            foreach ($content['interactions'] as $i){
                $interaction = $this->cryp->decSym($i['enc_int'], $secret);
                $mainContent .= "<br>Comment from: ". $i['sender'] . "<br>".$interaction;
            }
        }
        return $mainContent;
    }

    //decrypts and saves incoming notifications, returns potential errors
    public function save_notification(mixed $package)
    {
        $error = false;
        $username = $this->user['username'];
        $link = "";
        $actor = $package['actor'];

        $encryptedSecret = $package['encryptedSecret'];
        $encryptedPredata = $package['predata'];
        $privateKey = $this->db->get_privkey_by_address($package['to']); // todo check if user is logged in / privateKey may be accessed
        $secret = $this->cryp->decAsym($package['encryptedSecret'], $privateKey);

        $predata = Json_decode($this->cryp->decSym($encryptedPredata, $secret) , true);

        $id = $predata['id'];
        $subject = $predata['subject'];

        $this->db->add_notification($id, $username, $actor, $secret, $link, $subject);
        if (!$error) return false; else return $error;
    }

    function get_format($format){
         $message = $this->pb->format_request($format);
         $response = $this->po->send("url", $message);
         $package = json_decode($response, true);
         if (0==strcmp($this->ph->check_package($package),"Format Response"))
             return $package['format'];
         else return false;
    }

    function read_content($id)
    {
        $post = $this->db->get_post_by_id($id);
        if(!$post){
            echo "<br>Error - Unknown ID <br>";
            return "Error - Unknown ID";
        }else {
            if (!$post['username'] == $this->user['username']){
                echo "<br>Error - Wrong User<br>";
                return "Error - Wrong User";
            } else {
                $fileName = $this->path_postings. $id. ".txt";
                $myFile = fopen($fileName, "r") or die("Error - Unable to open file!");
                $content['content'] =  fread($myFile,filesize($fileName));
                fclose($myFile);

                $interactions_db = $this->db->get_interactions_by_contentid($id);
                $interactions= array();
                $i=0;
                if ($interactions_db != null) {
                    foreach($interactions_db as $in) {
                        $interaction['sender'] = $in['sender'];
                        $interaction['enc_int'] = $in['enc_int'];
                        $interactions[$i] = $interaction;
                    }
                    $content['interactions'] = $interactions;
                }
                return $content;
            }
        }
    }

    function post_interaction($interaction, $id, $actor, $to, $interactionType, $secret){
         $encryptedInteraction = $this->cryp->encSym($interaction, $secret);

         $message = $this->pb->interaction($actor, $to, $id, $interactionType, $encryptedInteraction);
         $response = $this->po->send("url", "$message");
         return $response;
     }

    function process_interaction(mixed $package)
    {
        $error = false;
        if (!($this->user['address'] ==  $package['to'])){
            return "Error - Not owner of interacted content";
        }
        $username = $this->user['username'];
        $this->db->add_interaction($package['id'], $username, $package['actor'], $package['interactionType'], $package['interaction']);
        if (!$error) return false; else return $error;
    }

}