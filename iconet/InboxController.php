<?php

namespace Iconet;

class InboxController
{

    private ArchivedProcessor $proc;

    public function __construct(User $user)
    {
        $this->proc = new ArchivedProcessor($user);
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
        return array_map(fn($notif) => $this->prepareContentDataForClient(
            $notif['content_id'],
            new Address($notif['sender']),
            $notif['secret']
        ),
            $this->proc->getNotifications());
    }

    /**
     * For a given notification's contentId, fetch the notifications content from the sender and decrypt it.
     * This is a helper function, since our client can't do decryption.
     * @param string $contentId
     * @param Address $actor
     * @param string $secret
     * @return object The decrypted content packet (with secret and content_id appended)
     */
    public function prepareContentDataForClient(string $contentId, Address $actor, string $secret): object
    {
        $encPacket = $this->proc->requestContent($contentId, $actor);
        $contentData = $this->proc->decryptContentPacket($encPacket, $secret);
        $contentData->secret = $secret;
        $contentData->contentId = $contentId; // TODO Would be nice if the content response packet already had this.
        return $contentData;
    }
}