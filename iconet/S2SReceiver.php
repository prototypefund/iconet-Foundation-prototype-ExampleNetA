<?php

namespace Iconet;

class S2SReceiver
{

    private User $to;
    private IconetInbox $inbox;


    /**
     * @param mixed $packet
     * @return bool True on success
     */
    private function initialize(mixed $packet): bool
    {
        $user = User::fromAddress(new Address($packet->to));
        if(!$user) {
            return false;
        }
        $this->to = $user;
        $this->inbox = new IconetInbox($this->to);
        return true;
    }

    public function receive(string $message): string|bool
    {
        $packet = PacketHandler::tryToDecode($message);
        if(!$packet) {
            return false;
        }
        if(!$this->initialize($packet)) {
            return false;
        }

        switch($packet->{'@type'}) {
            case 'EncryptedPacket':
                $this->inbox->decryptNotification($packet);
            // Fallthrough
            case "Packet":
                $response = self::processNotification($packet);
                break;
            default:
                $response = PacketBuilder::error("Unexpected packet type");
        }

        return $response;
    }


    private function processNotification(object $packet): string
    {
        $success = $this->inbox->saveNotification($packet);

        return $success ?
            PacketBuilder::ack() :
            PacketBuilder::error("Could not save notification!");
    }

}