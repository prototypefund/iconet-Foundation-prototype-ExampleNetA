<?php

namespace Iconet;

use RuntimeException;

class ArchivedProcessor
{
    //logged in user
    private User $user;

    private S2STransmitter $transmitter;
    private Crypto $crypto;
    private PacketHandler $packetHandler;

    /**
     * @param User $user
     */
    public function __construct(User $user)
    {
        $this->transmitter = new S2STransmitter();
        $this->crypto = new Crypto();
        $this->packetHandler = new PacketHandler();
        $this->user = $user;
    }


    public function getExternalPublicKey(Address $address): string
    {
        $message = PacketBuilder::publicKey_request($address);
        $response = $this->transmitter->send($address, $message);
        $packet = json_decode($response);
        if($this->packetHandler->checkPacket($packet) !== PacketTypes::PUBLIC_KEY_RESPONSE) {
            //TODO decide how and where to handle errors and unexpected input
            $error = json_encode($packet);
            throw new RuntimeException("Invalid response Packet. Expected: Public Key Response. Got $error");
        }
        return $packet->publicKey;
    }

    /**
     * @param string $content
     * @param string $formatId
     * @return void
     */
    public function createPost(string $content, string $formatId): void
    {
        //encrypt notification & content
        $secret = $this->crypto->genSymKey();
        $encryptedContent = $this->crypto->encSym($content, $secret);
        $encryptedFormatId = $this->crypto->encSym($formatId, $secret);

        //save post in db
        $id = Database::singleton()->addPost(
            $this->user->username,
            $secret,
            $encryptedFormatId,
            $encryptedContent
        );

        //generate notification
        $predata['id'] = $id;
        $predata['subject'] = "Example Subject"; // FIXME This should not be hard coded
        $encryptedNotif = $this->crypto->encSym(json_encode($predata), $secret);

        //generate and send notifications
        $contacts = Database::singleton()->getContacts($this->user->username);
        if(!$contacts) {
            echo "<br>You need contacts generate something for them! <br>";
        }
        $this->sendNotifications($contacts, $encryptedNotif, $secret);
    }

    /**
     * @param array<Contact> $contacts
     * @param string $encryptedNotif
     * @param string $secret
     * @return void
     */
    private function sendNotifications(array $contacts, string $encryptedNotif, string $secret): void
    {
        foreach($contacts as $contact) {
            $encryptedSecret = $this->crypto->encAsym($secret, $contact->publicKey);
            $notifPacket = PacketBuilder::notification(
                $this->user->address,
                $contact->address,
                $encryptedSecret,
                $encryptedNotif
            );
            // TODO Check response
            $response = $this->transmitter->send($contact->address, $notifPacket);
        }
    }

    /**
     * @return array<string>
     */
    public function getNotifications(): array
    {
        return Database::singleton()->getNotifications($this->user->username) ?? [];
    }


    /**
     * Request a content packet from an actor's outbox by id. The contentId is provided by received notifications.
     * @param string $contentId The content id
     * @param Address $actor The address from where content should be requested. This is the author of the content.
     * @return object The encrypted content response packet object
     */
    public function requestContent(string $contentId, Address $actor)
    {
        $message = PacketBuilder::content_request($contentId, $actor);
        $response = $this->transmitter->send($actor, $message);
        $packet = json_decode($response);
        if($this->packetHandler->checkPacket($packet) !== PacketTypes::CONTENT_RESPONSE) {
            echo "Error - invalid response Packet. Expected: Content Response";
            return "Error - invalid response Packet. Expected: Content Response";
        }
        return $packet;
    }


    /**
     * @param object $encPacket
     * @param string $secret
     * @return object A new packet with all its attributes decrypted.
     */
    public function decryptContentPacket($encPacket, $secret): object
    {
        $result = (object)[];
        $result->actor = $encPacket->actor;
        $result->content = $this->crypto->decSym($encPacket->content, $secret);
        $result->formatId = $this->crypto->decSym($encPacket->formatId, $secret);
        $result->interactions = $this->decryptInteractions($encPacket->interactions, $secret);
        return $result;
    }

    /**
     * @param array $encrypedInteractions
     * @param string $secret
     * @return array
     */
    public function decryptInteractions(array $encrypedinteractions, string $secret): array
    {
        $result = [];
        foreach($encrypedinteractions as $inter) {
            $result[] = (object)[
                'actor' => $inter->actor,
                'payload' => $this->crypto->decSym($inter->encPayload, $secret)
            ];
        }
        return $result;
    }


    /**
     * @param object $packet
     * @return bool true when successful
     */
    public function saveNotification(object $packet): bool
    {
        $username = $this->user->username;
        $actor = $packet->actor;

        $encryptedSecret = $packet->encryptedSecret;
        $encryptedPredata = $packet->predata;
        $privateKey = Database::singleton()->getPrivateKeyByAddress(
            $packet->to
        ); // todo check if user is logged in / privateKey may be accessed
        $secret = $this->crypto->decAsym($encryptedSecret, $privateKey);

        $predata = json_decode($this->crypto->decSym($encryptedPredata, $secret));

        $id = $predata->id;
        $subject = $predata->subject;

        Database::singleton()->addNotification($id, $username, $actor, $secret, $subject);
        return true;
    }

    /**
     * @param string $id
     * @return object
     */
    public function getEncryptedPostFromDB(string $id)
    {
        $post = Database::singleton()->getPostById($id);
        if(!$post) {
            echo "<br>Error - Unknown ID <br>";
            return "Error - Unknown ID";
        }
        if(!$post['username'] == $this->user->username) {
            // TODO Check this somewhere else
            echo "<br>Error - Wrong User<br>";
            return "Error - Wrong User";
        }

        return (object)$post;
    }

    public function getEncryptedInteractions(string $id): array
    {
        $interactions_db = $this->database->getInteractionsByContentId($id);
        $interactions = [];
        if($interactions_db != null) {
            foreach($interactions_db as $inter) {
                $interactions[] = (object)[
                    'actor' => $inter['sender'],
                    'encPayload' => $inter['payload'],
                ];
            }
        }
        return $interactions;
    }

    public function postInteraction(
        string $payload,
        string $contentId,
        string $actor,
        string $to,
        string $secret
    ): string {
        $encryptedPayload = $this->crypto->encSym($payload, $secret);

        $message = PacketBuilder::interaction($actor, $to, $contentId, $encryptedPayload);
        $response = $this->transmitter->send(new Address($to), $message);
        return $response;
    }

    /**
     * @param object $packet
     * @return string|null
     */
    public function processInteraction(object $packet): string|null
    {
        if(!($this->user->address == $packet->to)) {
            return "Error - Not owner of interacted content";
        }
        $resonse = $this->database->addInteraction(
            $packet->id,
            $this->user->username,
            $packet->actor,
            $packet->payload
        );
        return null;
    }

}