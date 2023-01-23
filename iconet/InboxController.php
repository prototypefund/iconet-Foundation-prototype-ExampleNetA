<?php

namespace Iconet;

class InboxController
{

    private User $user;
    private IconetInbox $inbox;
    private Database $database;

    public function __construct(User $user)
    {
        $this->user = $user;
        $this->inbox = new IconetInbox($user);
        $this->database = new Database();
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
        return $this->database->getNotifications($this->user->username);
    }

}