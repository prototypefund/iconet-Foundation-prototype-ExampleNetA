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
        $this->database = new Database();
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
    public function createPost(array $payload, string $formatId): void
    {
        //encrypt notification & content
        $secret = $this->crypto->genSymKey();
        $encryptedPayload = $this->crypto->encSym(json_encode($payload), $secret);
        $encryptedFormatId = $this->crypto->encSym($formatId, $secret);

        //save post in db
        $id = $this->database->addPost(
            $this->user->username,
            $secret,
            $encryptedFormatId,
            $encryptedPayload
        );
        //generate and send notifications
        $contacts = $this->database->getContacts($this->user->username);
        if(!$contacts) {
            echo "<br>You need contacts generate something for them! <br>";
        }

        foreach($contacts as $contact) {
            $encryptedSecret = $this->crypto->encAsym($secret, $contact->publicKey);
            $notifPacket = PacketBuilder::notification(
                $id,
                $this->user->address,
                $contact->address,
                $encryptedSecret,
                $encryptedPayload,
                $encryptedFormatId
            );
            // TODO Check response
            $response = $this->transmitter->send($contact->address, $notifPacket);
        }
    }
}