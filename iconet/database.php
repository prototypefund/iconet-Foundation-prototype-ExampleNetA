<?php
require './config/config.php';

function get_pubkey_by_address($address){
    global $icon;
    $query = mysqli_query($icon, "SELECT publickey FROM users WHERE address='$address'");
    if (mysqli_num_rows($query) > 0){
        $row = mysqli_fetch_array($query);
        return $row['publickey'];
    } else {
        return false;
    }
}

function get_privkey_by_address($address){
    global $icon;
    $query = mysqli_query($icon, "SELECT privatekey FROM users WHERE address='$address'");
    if (mysqli_num_rows($query) > 0){
        $row = mysqli_fetch_array($query);
        return $row['privatekey'];
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
        return false;
    }
}

function get_contacts($user){
    global $icon;
    $result = mysqli_query($icon, "SELECT * FROM contacts WHERE username='$user'") or trigger_error(mysqli_error());
    if(mysqli_num_rows($result) == 0)
        return null;
    else {
        $i = 0;
        while ($row = mysqli_fetch_array($result)) {
            $contact['address']  = $row['friend_address'];
            $contact['pubkey']  = $row['friend_pubkey'];
            $contacts[$i]  = $contact;
            $i++;
            }
            return $contacts;
        }
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

function clear_tables(){
    global $icon;
    mysqli_query($icon, "DELETE FROM users");
    mysqli_query($icon, "DELETE FROM contacts");
    mysqli_query($icon, "DELETE FROM notifications");
    mysqli_query($icon, "DELETE FROM posts");

}

?>