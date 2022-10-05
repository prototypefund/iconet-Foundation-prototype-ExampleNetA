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


function add_user($username, $address){
    global $icon;
    $query = mysqli_query($icon, "INSERT INTO users VALUES ('$username', '$address', 'pub123', 'priv123')");
}

function get_globaladdress($username){
    global $icon;
    $query = mysqli_query($icon, "SELECT address FROM users WHERE username='$username'");
    if (mysqli_num_rows($query) > 0){
        $row = mysqli_fetch_array($query);
        return $row['address'];
    } else {
        return "no_address";
    }
}


function get_contacts($user){
    global $icon;
    $result = mysqli_query($icon, "SELECT * FROM contacts WHERE username='$user'") or trigger_error(mysqli_error());
    return $result;
}

function delete_contact($user, $address){
    global  $icon;
    $delete_query = mysqli_query($icon, "DELETE FROM contacts WHERE username='$user' AND friend_address='$address'");
    if(mysqli_connect_errno())
    {
        echo "Failed to delete contact: " . mysqli_connect_errno();
        return false;
    } else return true;
}

function add_contact($user, $address, $pubkey){
    global $icon;
    $query = mysqli_query($icon, "INSERT INTO contacts VALUES ('$user', '$address', '$pubkey')");
    if(mysqli_connect_errno())
    {
        echo "Failed to add contact: " . mysqli_connect_errno();
        return false;
    }
    return true;
}
?>