<?php
namespace Iconet;

use RuntimeException;

require_once 'config/config.php';

class Processor
{
    //logged in user
    private User $user;

    private Database $database;
    private S2STransmitter $transmitter;
    private Crypto $crypto;
    private PackageHandler $packageHandler;
    private string $pathPostings;

    /**
     * @param User $user
     */
    public function __construct(User $user)
    {
        $this->database = new Database();
        $this->transmitter = new S2STransmitter();
        $this->crypto = new Crypto();
        $this->packageHandler = new PackageHandler();
        $this->pathPostings = $_ENV['STORAGE'];

        $this->user = $user;
    }


    public function getExternalPublicKey(Address $address): string
    {
        $message = PackageBuilder::publicKey_request($address);
        $response = $this->transmitter->send($address, $message);
        $package = json_decode($response);
        if($this->packageHandler->checkPackage($package) !== PackageTypes::PUBLICKEY_RESPONSE) {
            //TODO decide how and where to handle errors and unexpected input
            $error = json_encode($package);
            throw new RuntimeException("Invalid response Package. Expected: PublicKey Response. Got $error");
        }
        return $package->publicKey;
    }

    public function createPost(string $content): void
    {
        do {
            $id = md5((string)rand()); // generate random ID
            //Repeat if ID already in use (unlikely but possible)
        } while($this->database->getPostById($id));

        //generate notification
        $subject = $content . "- notification text"; //for testing content is only string

        $predata['id'] = $id;
        $predata['subject'] = $subject;

        //encrypt notification & content
        $secret = $this->crypto->genSymKey();
        $encryptedNotif = $this->crypto->encSym(json_encode($predata), $secret);
        $encryptedContent = $this->crypto->encSym($content, $secret);

        //save content
        $file = fopen($this->pathPostings . $id . ".txt", "w") or die("Cannot open file.");
        // Write data to the file
        fwrite($file, $encryptedContent);
        // Close the file
        fclose($file);

        //save post in db
        $this->database->addPost($id, $this->user->username, $secret);

        //generate and send notifications

        $contacts = $this->database->getContacts($this->user->username);
        if(!$contacts) {
            echo "<br>You need contacts generate something for them! <br>";
        }

        $this->sendNotifications($contacts, $encryptedNotif, $secret);
    }

    /**
     * @param array<Contact> $contacts
     * @param string $encryptedNotif
     * @param string $secret
     * @return void
     */
    private function sendNotifications(array $contacts, string $encryptedNotif, string $secret): void
    {
        foreach($contacts as $contact) {
            $encryptedSecret = $this->crypto->encAsym($secret, $contact->publicKey);
            $notifPackage = PackageBuilder::notification(
                $this->user->address,
                $contact->address,
                $encryptedSecret,
                $encryptedNotif
            );
            // TODO Check response
            $response = $this->transmitter->send($contact->address, $notifPackage);
        }
    }

    /**
     * @return array<string>
     */
    public function getNotifications(): array
    {
        return $this->database->getNotifications($this->user->username) ?? [];
    }

    /**
     * @param string $id
     * @param Address $actor
     * @param string $secret
     * @return string
     */
    public function displayContent(string $id, Address $actor, string $secret): string
    {
        $message = PackageBuilder::content_request($id, $actor);
        $response = $this->transmitter->send($actor, $message);
        $package = json_decode($response);
        if($this->packageHandler->checkPackage($package) !== PackageTypes::CONTENT_RESPONSE) {
            echo "Error - invalid response Package. Expected: Content Response";
            return "Error - invalid response Package. Expected: Content Response";
        }
        $content = $package->content;
        $mainContent = $this->crypto->decSym($content->content, $secret);
        if(isset($content->interactions)) {
            foreach($content->interactions as $i) {
                $interaction = $this->crypto->decSym($i->enc_int, $secret);
                $mainContent .= "<br>Comment from: " . $i->sender . "<br>" . $interaction;
            }
        }
        return $mainContent;
    }


    /**
     * @param object $package
     * @return bool true when successful
     */
    public function saveNotification(object $package): bool
    {
        $username = $this->user->username;
        $link = "";//TODO
        $actor = $package->actor;

        $encryptedSecret = $package->encryptedSecret;
        $encryptedPredata = $package->predata;
        $privateKey = $this->database->getPrivateKeyByAddress(
            $package->to
        ); // todo check if user is logged in / privateKey may be accessed
        $secret = $this->crypto->decAsym($encryptedSecret, $privateKey);

        $predata = json_decode($this->crypto->decSym($encryptedPredata, $secret));

        $id = $predata->id;
        $subject = $predata->subject;

        $this->database->addNotification($id, $username, $actor, $secret, $link, $subject);
        return true;
    }

    //TODO there needs to be an url/address for the format server
    public function getFormat(string $formatID): string|bool
    {
        $message = PackageBuilder::format_request($formatID);
        $response = $this->transmitter->send(new Address($formatID), $message);
        $package = json_decode($response);
        if($this->packageHandler->checkPackage($package) !== PackageTypes::FORMAT_RESPONSE) {
            return false;
        }
        return $package->format;
    }

    /**
     * @param string $id
     * @return array<string>|string
     */
    public function readContent(string $id): array|string
    {
        $post = $this->database->getPostById($id);
        if(!$post) {
            echo "<br>Error - Unknown ID <br>";
            return "Error - Unknown ID";
        } else {
            if(!$post['username'] == $this->user->username) {
                echo "<br>Error - Wrong User<br>";
                return "Error - Wrong User";
            } else {
                $fileName = $this->pathPostings . $id . ".txt";
                $myFile = fopen($fileName, "r") or die("Error - Unable to open file!");
                $content['content'] = fread($myFile, filesize($fileName));
                fclose($myFile);

                $interactions_db = $this->database->getInteractionsByContentId($id);
                $interactions = array();
                $i = 0;
                if($interactions_db != null) {
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

    public function postInteraction(
        string $interaction,
        string $id,
        string $actor,
        string $to,
        string $interactionType,
        string $secret
    ): string {
        $encryptedInteraction = $this->crypto->encSym($interaction, $secret);

        $message = PackageBuilder::interaction($actor, $to, $id, $interactionType, $encryptedInteraction);
        $response = $this->transmitter->send(new Address($to), $message);
        return $response;
    }

    /**
     * @param object $package
     * @return string|null
     */
    public function processInteraction(object $package): string|null
    {
        if(!($this->user->address == $package->to)) {
            return "Error - Not owner of interacted content";
        }
        $username = $this->user->username;
        $resonse = $this->database->addInteraction(
            $package->id,
            $username,
            $package->actor,
            $package->interactionType,
            $package->interaction
        );
        return null;
    }

}