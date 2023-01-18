<?php

use Iconet\Crypto;
use Iconet\Database;
use Iconet\User;

class IconetInbox
{
    private User $user;

    private Database $database;
    private Crypto $crypto;


    /**
     * @param User $user
     */
    public function __construct(User $user)
    {
        $this->database = new Database();
        $this->crypto = new Crypto();
        $this->user = $user;
    }

    public function saveNotification(object $packet): bool
    {
        $id = $packet->id;
        $actor = $packet->actor;
        $encryptedSecret = $packet->encryptedSecret;
        $encryptedContent = $packet->encryptedContent;
        $encryptedFormatId = $packet->encryptedFormatId;
        $privateKey = $this->user->privateKey;
        $secret = $this->crypto->decAsym($encryptedSecret, $privateKey);

        $content = json_decode($this->crypto->decSym($encryptedContent, $secret));
        $formatId = json_decode($this->crypto->decSym($encryptedFormatId, $secret));

        $this->database->addNotification($id, $this->user->username, $actor, $secret, $content);
        //todo check for errors
        return true;
    }
}