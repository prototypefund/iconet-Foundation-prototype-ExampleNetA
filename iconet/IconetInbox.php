<?php
namespace Iconet;


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
        $encryptedPayload = $packet->encryptedPayload;
        $encryptedFormatId = $packet->encryptedFormatId;
        $privateKey = $this->user->privateKey;
        $secret = $this->crypto->decAsym($encryptedSecret, $privateKey);

        $payload = $this->crypto->decSym($encryptedPayload, $secret);
        $formatId = $this->crypto->decSym($encryptedFormatId, $secret);

        if(!$payload) {
            $payload = "Decryption Error.";
        }

        $this->database->addNotification($id, $this->user->username, $actor, $secret, $payload, $formatId);
        //todo check for errors
        return true;
    }
}