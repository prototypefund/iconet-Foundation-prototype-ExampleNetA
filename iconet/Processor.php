<?php
namespace Iconet;

use function PHPUnit\Framework\assertFileIsWritable;

require_once 'config/config.php';

class Processor
{
    //logged in user
    protected $user;

    //helper classes
    protected Database $database; //database
    protected PostOffice $postOffice; //postoffice
    protected PackageBuilder $packageBuilder; //package_builder
    protected Crypto $crypto; //cryptograph
    protected PackageHandler $packageHandler;
    protected string $pathPostings;

    /**
     * @param string $userLoggedIn
     */
    public function __construct(string $userLoggedIn)
    {
        $this->database = new Database();
        $this->postOffice = new PostOffice();
        $this->crypto = new Crypto();
        $this->packageBuilder = new PackageBuilder();
        $this->packageHandler = new PackageHandler();
        $this->pathPostings = $_ENV['STORAGE'];

        $user = $this->database->getUserByName($userLoggedIn);
        $this->setUser($user);
    }


    /**
     * @param array<string> $user
     */public function setUser(array $user): void
{
    $this->user = $user;
}
    function getExternalPublicKey(string $address) :string
    {
        $message = $this->packageBuilder->publicKey_request($address);
        $response = $this->postOffice->send("url", $message);
        $package = (array) Json_decode($response,true);
        if (0!=strcmp($this->packageHandler->checkPackage($package),"PublicKey Response")){
            echo "Error - invalid response Package. Expected: PublicKey Response";
            return "Error - invalid response Package. Expected: PublicKey Response";
        }
        return $package['publicKey'];
    }

    function createIconetPost(string $content) : void
    {
        //generate random ID
        $done = false;
        $id ="";
        while(!$done){
            $id = md5((string)rand()); // generate random ID
            if(!$this->database->getPostById($id)) $done = true; //Repeat if ID already in use (unlikely but possible)
        }
        //generate notification
        $subject = $content . "- notification text"; //for testing content is only string

        $predata['id'] = $id;
        $predata['subject'] = $subject;

        //encrypt notification & content
        $secret = $this->crypto->genSymKey();
        $encryptedNotif = $this->crypto->encSym(Json_encode($predata),$secret);
        $encryptedContent = $this->crypto->encSym($content,$secret);

        //save content
        $file = fopen($this->pathPostings.$id.".txt", "w") or die("Cannot open file.");
        // Write data to the file
        fwrite($file, $encryptedContent);
        // Close the file
        fclose($file);

        //save post in db
        $this->database->addPost($id, $this->user['username'], $secret);

        //generate and send notifications

        $contacts = $this->database->getContacts($this->user['username']);
        if ($contacts == null) {
            echo "<br>You need contacts generate something for them! <br>";
        }

        $encryptedSecret= $this->crypto->genAllCiphers($contacts, $secret);
        foreach ($encryptedSecret as $c){
            $notifPackage = $this->packageBuilder->notification($this->user['address'], $c, $encryptedNotif);
            $response = $this->postOffice->send("url", $notifPackage);
        }
    }

    /**
     * @param string $username
     * @return array<string>|null
     */
    function checkInbox(string $username): array|null
    {
         return $this->database->getNotifications($username);
    }

    /**
     * @param string $id
     * @param string $actor
     * @param string $secret
     * @return string
     */
    function displayContent(string $id, string $actor, string $secret) : string
    {
         $message = $this->packageBuilder->content_request($id, $actor);
         $response = $this->postOffice->send("url", $message);
         $package = (array) Json_decode($response,true);
        if (0!=strcmp($this->packageHandler->checkPackage($package),"Content Response")){
            echo "Error - invalid response Package. Expected: Content Response";
            return "Error - invalid response Package. Expected: Content Response";
        }
        $content = (array) $package['content'];
        $mainContent = $this->crypto->decSym($content['content'], $secret);
        if (isset($content['interactions'])){
            foreach ( (array) $content['interactions'] as $i){
                $interaction = $this->crypto->decSym($i['enc_int'], $secret);
                $mainContent .= "<br>Comment from: ". $i['sender'] . "<br>".$interaction;
            }
        }
        return $mainContent;
    }

    /**
     * @param array<string> $package
     * @return void
     */
    public function saveNotification(array $package) : void
    {
        $username = $this->user['username'];
        $link = "";
        $actor = $package['actor'];

        $encryptedSecret = $package['encryptedSecret'];
        $encryptedPredata = $package['predata'];
        $privateKey = $this->database->getPrivateKeyByAddress($package['to']); // todo check if user is logged in / privateKey may be accessed
        $secret = $this->crypto->decAsym($package['encryptedSecret'], $privateKey);

        $predata = (array) Json_decode($this->crypto->decSym($encryptedPredata, $secret) , true);

        $id = $predata['id'];
        $subject = $predata['subject'];

        $this->database->addNotification($id, $username, $actor, $secret, $link, $subject);
    }

    function getFormat(string $formatID): string|bool
    {
         $message = $this->packageBuilder->format_request($formatID);
         $response = $this->postOffice->send("url", $message);
         $package = (array) json_decode($response, true);
         if (0==strcmp($this->packageHandler->checkPackage($package),"Format Response"))
             return $package['format'];
         else return false;
    }

    /**
     * @param string $id
     * @return array<string>|string
     */
    function readContent(string $id): array|string
    {
        $post = $this->database->getPostById($id);
        if(!$post){
            echo "<br>Error - Unknown ID <br>";
            return "Error - Unknown ID";
        }else {
            if (!$post['username'] == $this->user['username']){
                echo "<br>Error - Wrong User<br>";
                return "Error - Wrong User";
            } else {
                $fileName = $this->pathPostings. $id. ".txt";
                $myFile = fopen($fileName, "r") or die("Error - Unable to open file!");
                $content['content'] =  fread($myFile,filesize($fileName));
                fclose($myFile);

                $interactions_db = $this->database->getInteractionsByContentId($id);
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

    function postInteraction(string $interaction, string $id, string $actor, string $to, string $interactionType, string $secret) : string
    {
         $encryptedInteraction = $this->crypto->encSym($interaction, $secret);

         $message = $this->packageBuilder->interaction($actor, $to, $id, $interactionType, $encryptedInteraction);
         $response = $this->postOffice->send("url", "$message");
         return $response;
     }

    /**
     * @param array<string> $package
     * @return string|null
     */
    function processInteraction(array $package) :string|null
    {
        if (!($this->user['address'] ==  $package['to'])){
            return "Error - Not owner of interacted content";
        }
        $username = $this->user['username'];
        $resonse= $this->database->addInteraction($package['id'], $username, $package['actor'], $package['interactionType'], $package['interaction']);
        return null;
    }

}