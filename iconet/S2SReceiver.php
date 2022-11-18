<?php

namespace Iconet;

class S2SReceiver
{

    private Database $db;

    public function receive(string $message): string
    {
        $package = json_decode($message);
        if(!$package) {
            return PackageBuilder::error("Invalid json");
        }
        $type = PackageHandler::checkPackage($package);
        $this->db = new Database();

        switch($type) {
            case PackageTypes::PUBLICKEY_REQUEST:
                $response = self::processPublickeyRequest($package);
                break;
            case PackageTypes::NOTIFICATION:
                $response = self::processNotification($package);
                break;
            case PackageTypes::FORMAT_REQUEST:
                $response = self::processFormatRequest($package);
                break;
            case PackageTypes::INTERACTION:
                $response = self::processInteraction($package);
                break;
            case PackageTypes::CONTENT_REQUEST:
                $response = self::processContentRequest($package);
                break;
            default:
                $response = PackageBuilder::error("Can not process this package");
        }

        return $response;
    }

    private function processPublickeyRequest(object $package): string
    {
        $publicKey = $this->db->getPublickeyByAddress($package->address);
        if(!$publicKey) {
            return PackageBuilder::error("No public key found for $package->address");
        }
        return PackageBuilder::publickey_response($package->address, $publicKey);
    }

    private function processNotification(object $package): string
    {
        $user = User::fromAddress(new Address($package->to));
        $processor = new Processor($user);
        $success = $processor->saveNotification($package);
        if($success) {
            return PackageBuilder::error("Could not save notification");
        } else {
            return PackageBuilder::ack();
        }
    }

    private function processFormatRequest(object $package): string
    {
        $format = file_get_contents("./iconet/formats/post-comments.fmfibs");
        return PackageBuilder::format_response($package->formatId, $format);
    }

    private function processInteraction(object $package): string
    {
        $user = User::fromAddress(new Address($package->to));
        $processor = new Processor($user);
        $error = $processor->processInteraction($package);
        if($error) {
            return PackageBuilder::error($error);
        } else {
            return PackageBuilder::ack();
        }
    }

    private function processContentRequest(object $package): string
    {
        $user = User::fromAddress(new Address($package->actor));
        $processor = new Processor($user);
        $content = $processor->readContent($package->id);
        return PackageBuilder::content_response($content, "post-comments", $package->actor);
    }

}