<?php

namespace Iconet;

class S2SReceiver
{

    private Database $db;

    public function receive(string $message): string
    {
        $packet = json_decode($message);
        if(!$packet) {
            return PacketBuilder::error("Invalid json");
        }
        $type = PacketHandler::checkPacket($packet);
        $this->db = new Database();

        switch($type) {
            case PacketTypes::PUBLICKEY_REQUEST:
                $response = self::processPublickeyRequest($packet);
                break;
            case PacketTypes::NOTIFICATION:
                $response = self::processNotification($packet);
                break;
            case PacketTypes::FORMAT_REQUEST:
                $response = self::processFormatRequest($packet);
                break;
            case PacketTypes::INTERACTION:
                $response = self::processInteraction($packet);
                break;
            case PacketTypes::CONTENT_REQUEST:
                $response = self::processContentRequest($packet);
                break;
            default:
                $response = PacketBuilder::error("Can not process this packet");
        }

        return $response;
    }

    private function processPublickeyRequest(object $packet): string
    {
        $publicKey = $this->db->getPublickeyByAddress($packet->address);
        if(!$publicKey) {
            return PacketBuilder::error("No public key found for $packet->address");
        }
        return PacketBuilder::publickey_response($packet->address, $publicKey);
    }

    private function processNotification(object $packet): string
    {
        $user = User::fromAddress(new Address($packet->to));
        $processor = new Processor($user);
        $success = $processor->saveNotification($packet);
        if($success) {
            return PacketBuilder::error("Could not save notification");
        } else {
            return PacketBuilder::ack();
        }
    }

    private function processFormatRequest(object $packet): string
    {
        $format = file_get_contents("./iconet/formats/post-comments.fmfibs");
        return PacketBuilder::format_response($packet->formatId, $format);
    }

    private function processInteraction(object $packet): string
    {
        $user = User::fromAddress(new Address($packet->to));
        $processor = new Processor($user);
        $error = $processor->processInteraction($packet);
        if($error) {
            return PacketBuilder::error($error);
        } else {
            return PacketBuilder::ack();
        }
    }

    private function processContentRequest(object $packet): string
    {
        $user = User::fromAddress(new Address($packet->actor));
        $processor = new Processor($user);
        $content = $processor->readContent($packet->id);
        return PacketBuilder::content_response($content, "post-comments", $packet->actor);
    }

}