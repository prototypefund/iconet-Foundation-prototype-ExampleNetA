<?php

namespace Iconet;

class IconetSender
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
    public function createPost(string $content, string $formatId): void
    {
        //encrypt notification & content
        $secret = $this->crypto->genSymKey();
        $encryptedContent = $this->crypto->encSym($content, $secret);
        $encryptedFormatId = $this->crypto->encSym($formatId, $secret);

        //save post in db
        $id = $this->database->addPost(
            $this->user->username,
            $secret,
            $encryptedFormatId,
            $encryptedContent
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
                $encryptedContent,
                $encryptedFormatId
            );
            // TODO Check response
            $response = $this->transmitter->send($contact->address, $notifPacket);
        }
    }
}