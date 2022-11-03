<?php
namespace Iconet;
// i accept and handle incoming requests!

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
function receive($msg){
    // I know how to get things done!
    $package = json_decode($msg, true);
    $ph = new PackageHandler();
    $type = $ph->check_package($package);
    $db = new Database();
    $pb = new PackageBuilder();
    switch ($type){
        case "Request Publickey":

            $pubKey = $db->get_pubkey_by_address($package['address']);
            return $pb->send_publickey($package['address'], $pubKey);
            break;

        case "Send Notification":
            $user = $db->get_user_by_address($package['to']);
            $proc = new Processor($user['username']);
            $error = $proc->save_notification($package);
            if ($error) return $pb->send_error($error);
            else return $pb->ack();

        break;

        case "Request Format":
            $format = file_get_contents("./iconet/formats/post-comments.fmfibs");
            return $pb->send_format($package['name'], $format);
            break;

        case "Send Interaction":
            $user = $db->get_user_by_address($package['to']);
            $proc = new Processor($user['username']);
            $error = $proc->process_interaction($package);
            if ($error) return $pb->send_error($error);
            else return $pb->ack();
        break;

        case "Request Content":
            $username = $db->get_user_by_address($package["address"])['username'];
            $proc = new Processor($username);
            $content = $proc->read_content($package["id"]);
            return $pb ->send_content($content, "post-comments", $package["address"]);
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