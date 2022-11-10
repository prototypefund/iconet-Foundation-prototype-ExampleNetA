<?php
namespace Iconet;
// I accept and handle incoming requests!

if (isset($sim_post)){
    // handle post requests
    include_once "./iconet/PackageHandler.php";
    include_once "./iconet/PackageBuilder.php";
    include_once "./iconet/Database.php";
    include_once "./iconet/Processor.php";


} else {
    // present personal inbox
    echo "<h3>Your open Inbox!</h3>";
}

//TODO this function is part of the current server 2 server internal workaround. Will be replaced by https requests.
function receive($message){
    // I know how to get things done!
    $package = json_decode($message, true);
    $ph = new PackageHandler();
    $type = $ph->checkPackage($package);
    $db = new Database();
    $pb = new PackageBuilder();

    switch ($type){

        case "PublicKey Request":
            $publicKey = $db->getPublickeyByAddress($package['address']);
            return $pb->publickey_response($package['address'], $publicKey);
            break;

        case "Notification":
            $user = $db->getUserByAddress($package['to']);
            $processor = new Processor($user['username']);
            $error = $processor->saveNotification($package);
            if ($error) return $pb->error($error);
            else return $pb->ack();

        break;

        case "Format Request":
            $format = file_get_contents("./iconet/formats/post-comments.fmfibs");
            return $pb->format_response($package['formatId'], $format);
            break;

        case "Interaction":
            $user = $db->getUserByAddress($package['to']);
            $processor = new Processor($user['username']);
            $error = $processor->processInteraction($package);
            if ($error) return $pb->error($error);
            else return $pb->ack();
        break;

        case "Content Request":
            $username = $db->getUserByAddress($package["actor"])['username'];
            $processor = new Processor($username);
            $content = $processor->readContent($package["id"]);
            return $pb ->content_response($content, "post-comments", $package["actor"]);
            break;

        default:
            //TODO provide Error Code on HTTP level.
            $response['type'] = "Error";
            $response['Error'] = $type;
            return json_encode($response);
    }

    echo "This section should never be reached. <br>";
    return false;
}
?>