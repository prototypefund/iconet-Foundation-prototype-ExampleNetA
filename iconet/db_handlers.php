<?php
include_once 'config/config.php';

function get_userpubkey_by_address($address){
    global $icon;
    $query = mysqli_query($icon, "SELECT publickey FROM users WHERE address='$address'");
    if (mysqli_num_rows($query) > 0){
        $row = mysqli_fetch_array($query);
        return $row['pubkey'];
    } else {
        return false;
    }
}
?>