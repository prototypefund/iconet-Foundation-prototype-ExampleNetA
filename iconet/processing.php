<?php
include_once 'request_builder.php';
include_once 'cryptography.php';


function create_iconet_post($content){
    global $userLoggedIn;
    $user = get_user_by_name($userLoggedIn);
    var_dump($user);
    //generate random ID
    $done = false;
    while(!$done){
        $ID = md5(rand()); // generate random ID
        if(!get_post_by_ID($ID)) $done = true; //Repeat if ID already in use (unlikely but possible)
    }
    //generate notification
    $notification = $content . "notif"; //for testing content is only string

    //encrypt notification & content
    $secret = genSymKey();
    $enc_not = encSym($notification,$secret);
    $enc_cont = encSym($content,$secret);

    //save content
    $file = fopen("./iconet/posts/". $ID.".txt", "w") or die("Cannot open file.");
    // Write data to the file
    fwrite($file, $enc_cont);
    // Close the file
    fclose($file);

    //save post in db
    add_post($ID, $userLoggedIn, $secret);

    //generate and send notifications
    $ciphers= genAllCiphers($userLoggedIn, $secret);
    $notif_responses = send_notifications($user, $ciphers, $enc_not);
    return $notif_responses;
}



?>
