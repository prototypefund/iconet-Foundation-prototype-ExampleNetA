<?php

namespace Iconet;

class S2SReceiver
{

    public function receive(string $message): string
    {
        $packet = json_decode($message);

        if(!$packet) {
            return PacketBuilder::error("Invalid json");
        }
        var_dump($packet);
        $type = PacketHandler::checkPacket($packet);

        $response = self::processNotification($packet);
       
        return $response;
    }

   
    private function processNotification(object $packet): string
    {
        $user = User::fromAddress(new Address($packet->to));
        $iconetInbox = new IconetInbox($user);
        $success = $iconetInbox->saveNotification($packet);
        if($success) {
            return PacketBuilder::ack();
        } else {
            return PacketBuilder::error("Could not save notification!");
        }
    }

}