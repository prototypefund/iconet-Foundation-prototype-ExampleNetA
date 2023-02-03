<?php
namespace Iconet;


class IconetInbox
{
    private User $user;
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
        $id = $packet->{'@id'};
        $actor = $packet->actor;
        $encryptedSecret = $packet->encryptedSecret;
        $encryptedPayload = $packet->encryptedPayload;
        $privateKey = $this->user->privateKey;
        $secret = $this->crypto->decAsym($encryptedSecret, $privateKey);

        $payload = $this->crypto->decSym($encryptedPayload, $secret);

        if(!$payload) {
            echo "Decryption Error.";
            return false;
        }
        $manifestUri = json_decode(
            $payload
        )->interpreterManifests[0]->manifestUri; //Todo Expect multiple manifests and pick the right one;

        Database::singleton()->addNotification($id, $this->user->username, $actor, $secret, $payload, $manifestUri);
        //todo check for errors
        return true;
    }


    public function renderInbox(): void
    {
        foreach($this->inboxContents() as $contentData) {
            echo (new EmbeddedExperience($contentData))->render();
        }
    }

    /**
     * Fetches the content for every notification in the inbox and prepares it for the client
     * @return array<object> An array of decrypted content data.
     */
    public function inboxContents(): array
    {
        $notifications = Database::singleton()->getNotifications($this->user->username);
        return $this->prepareContentDataForClient($notifications);
    }

    public function prepareContentDataForClient(array $notifications): array
    {
        $contentDatastack = array();
        $i = 0;
        foreach($notifications as $n) {
            $contentData = json_decode($n['payload']);
            $contentDatastack[$i] = $contentData;
            $i++;
        }

        return $contentDatastack;
    }


}