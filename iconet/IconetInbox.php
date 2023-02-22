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

    /**
     * Mutates the packet to include the decrypted content and interpreterManifests,
     * as well as the decrypted secret.
     * @param object $packet
     * @return string Returns the secret used for decryption.
     */
    public function decryptNotification(object $packet): bool
    {
        $secret = $this->crypto->decAsym($packet->encryptedSecret, $this->user->privateKey);
        $decrypted = $this->crypto->decSym($packet->encryptedPayload, $secret);
        $decrypted = json_decode($decrypted);

        if(!$decrypted) {
            echo "Decryption Error.";
            return false;
        }

        $packet->content = $decrypted->content;
        $packet->interpreterManifests = $decrypted->interpreterManifests;

        $packet->secret = $secret;
        return true;
    }

    public function saveNotification(object $packet): bool
    {
        $manifestUri = $packet->interpreterManifests[0]->manifestUri; //Todo Expect multiple manifests and pick application/iconet+html

        // TODO Why is the secret even needed? Then the unsets could be done in decryptNotification()
        $secret = $packet->secret ?? null;

        unset($packet->encryptedSecret);
        unset($packet->encryptedPayload);
        unset($packet->secret);

        return Database::singleton()->addNotification(
            $packet->{'@id'},
            $this->user->username,
            $packet->actor,
            $secret,
            json_encode($packet), // Save everything?
            $manifestUri
        );
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