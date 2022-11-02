<?php
// i accept and handle incoming requests!



if (isset($sim_post)){
    // handle post requests
    include_once "./iconet/package_handler.php";
    include_once "./iconet/package_builder.php";
    include_once "./iconet/database.php";
    include_once "./iconet/processor.php";

} else {
    // present personal inbox
    echo "<h3>Your open Inbox!</h3>";
}

//TODO this function is part of the current server 2 server internal workaround. Will be replaced by https requests.
function receive($msg){
    // I know how to get things done!
    $package = json_decode($msg, true);
    $ph = new package_handler();
    $type = $ph->check_package($package);
    $db = new database();
    $pb = new package_builder();
    switch ($type){

        case "Request Publickey":
            $pubKey = $db->get_pubkey_by_address($package['address']);
            return $pb->send_publickey($package['address'], $pubKey);
            break;

        case "Send Notification":
            $user = $db->get_user_by_address($package['to']);
            $proc = new processor($user['username']);
            $proc->save_notification($package);
            $response['type'] = "ACK Notification";
            return json_encode($response);
        break;

        case "Request Format":
            $format = file_get_contents("./iconet/formats/post-comments.fmfibs");
            return $pb->send_format($package['name'], $format);
            break;

        case "Send Interaction":
            //TODO decode content, verify signature, append to content
            $response['type'] = "Response Interaction";
            $response['msg'] = "Your request is great, but sadly I can't process it yet.";
            return json_encode($response);
        break;

        case "Request Content":
            //TODO decode content, verify signature, append to content
            $username = $db->get_user_by_address($package["address"])['username'];
            $proc = new processor($username);
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