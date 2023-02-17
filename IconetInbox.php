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
        $this->crypto = new Crypto();
        $this->user = $user;
    }

    public function saveNotification(object $packet): bool
    {
        $id = $packet->id;
        $actor = $packet->actor;
        $encryptedSecret = $packet->encryptedSecret;
        $encryptedPayload = $packet->encryptedPayload;
        $encryptedFormatId = $packet->encryptedFormatId;
        $privateKey = $this->user->privateKey;
        $secret = $this->crypto->decAsym($encryptedSecret, $privateKey);

        $payload = json_decode($this->crypto->decSym($encryptedPayload, $secret));
        $formatId = json_decode($this->crypto->decSym($encryptedFormatId, $secret));

        Database::singleton()->addNotification($id, $this->user->username, $actor, $secret, $payload, $formatId);
        //todo check for errors
        return true;
    }
}