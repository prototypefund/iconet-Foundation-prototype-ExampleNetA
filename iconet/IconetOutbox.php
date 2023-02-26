<?php

namespace Iconet;

class IconetOutbox
{
    //logged in user
    private User $user;

    private Database $database;
    private S2STransmitter $transmitter;
    private Crypto $crypto;

    /**
     * @param User $user
     */
    public function __construct(User $user)
    {
        $this->transmitter = new S2STransmitter();
        $this->crypto = new Crypto();
        $this->user = $user;
    }
    // newPost (Id, username)
    /*//create secret, fetch target addresses, create tokens & encrypt
     * add post and data to database
     * create and send packet
     */
    // $content = {username,id}
    public function createPost(array $content, string $manifestUri): void
    {
        //generate secret
        $secret = $this->crypto->genSymKey();
        //save postdata for later
        $id = Database::singleton()->addPost(
            $this->user->username,
            $secret,
            $manifestUri,
            json_encode($content)
        );
        $content['id'] = $id;

        
        //encrypt notification & content
        $preparedPayload = PacketBuilder::preparePayload($manifestUri, $content);
        $packet['interpreterManifests'] = $preparedPayload['interpreterManifests'];
        $packet['content'] = $preparedPayload['content'];
        $encryptedPacket = $this->crypto->encSym(json_encode($packet), $secret);
        //save post in db
        //generate and send notifications
        $contacts = Database::singleton()->getContacts($this->user->username);
        if(!$contacts) {
            echo "<br>You have no iconet contacts<br>";
        }

        foreach($contacts as $contact) {
            $notifPacket = "";
            if($contact->publicKey == "") {
                $notifPacket = PacketBuilder::Notification(
                    $id,
                    $this->user->address,
                    $contact->address,
                    $preparedPayload
                );
            } else {
                $encryptedSecret = $this->crypto->encAsym($secret, $contact->publicKey);
                $notifPacket = PacketBuilder::EncryptedNotification(
                    $id,
                    $this->user->address,
                    $contact->address,
                    $encryptedSecret,
                    $encryptedPacket
                );
            }
            // TODO Check response
            $response = $this->transmitter->send($contact->address, $notifPacket);
        }
    }

}