<?php
namespace Iconet;


class Database
{
    protected $db;

    //TODO use PDO as in the other Database class
    public function __construct()
    {
        $this->db = mysqli_connect(
            $_ENV['DB_ICONET_HOST'],
            $_ENV['DB_ICONET_USER'],
            $_ENV['DB_ICONET_PASSWORD'],
            $_ENV['DB_ICONET_DATABASE']
        );

        if(mysqli_connect_errno())
        {
            error_log(mysqli_connect_errno());
        }
    }

    function get_pubkey_by_address($address){
        $query = mysqli_query($this->db, "SELECT publickey FROM users WHERE address='$address'");
        if (mysqli_num_rows($query) > 0){
            $row = mysqli_fetch_array($query);
            return $row['publickey'];
        } else {
            return false;
        }
    }

    function get_privkey_by_address($address){
        $query = mysqli_query($this->db, "SELECT privatekey FROM users WHERE address='$address'");
        if (mysqli_num_rows($query) > 0){
            $row = mysqli_fetch_array($query);
            return $row['privatekey'];
        } else {
            return false;
        }
    }

    function add_post($id, $username, $secret): bool
    {
        $query = mysqli_query($this->db, "INSERT INTO posts VALUES ('$id', '$username', '$secret')");
        if(mysqli_connect_errno())
        {
            echo "Failed to add post: " . mysqli_connect_errno();
            return false;
        }
        return true;
    }

    function get_user_by_name($name){
        $query = mysqli_query($this->db, "SELECT * FROM users WHERE username='$name'");
        if (mysqli_num_rows($query) > 0){
            $user = mysqli_fetch_array($query);
            return $user;
        } else {
            return false;
        }
    }

    function get_user_by_address($address){
        $query = mysqli_query($this->db, "SELECT * FROM users WHERE address='$address'");
        if (mysqli_num_rows($query) > 0){
            $user = mysqli_fetch_array($query);
            return $user;
        } else {
            return false;
        }
    }

    function get_post_by_id($ID)
    {
        $query = mysqli_query($this->db, "SELECT * FROM posts WHERE id='$ID'");
        if (mysqli_num_rows($query) > 0){
            return mysqli_fetch_array($query);
        } else {
            return null;
        }
    }


    function add_user($username, $address, $pubkey, $privkey): bool
    {
        $query = mysqli_query($this->db, "INSERT INTO users VALUES ('$username', '$address', '$pubkey', '$privkey')");
        if(mysqli_connect_errno())
        {
            echo "Failed to add user: " . mysqli_connect_errno();
            return false;
        }
        return true;
    }

    function get_globaladdress($username){
        $query = mysqli_query($this->db, "SELECT address FROM users WHERE username='$username'");
        if (mysqli_num_rows($query) > 0){
            $row = mysqli_fetch_array($query);
            return $row['address'];
        } else {
            return false;
        }
    }

    function get_contacts($user): ?array
    {
        $result = mysqli_query($this->db, "SELECT * FROM contacts WHERE username='$user'") or trigger_error(mysqli_error());
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

    function delete_contact($user, $address): bool
    {
        $delete_query = mysqli_query($this->db, "DELETE FROM contacts WHERE username='$user' AND friend_address='$address'");
        if(mysqli_connect_errno())
        {
            echo "Failed to delete contact: " . mysqli_connect_errno();
            return false;
        } else return true;
    }

    function add_contact($user, $address, $pubkey): bool
    {
        $query = mysqli_query($this->db, "INSERT INTO contacts VALUES ('$user', '$address', '$pubkey')");
        if(mysqli_connect_errno())
        {
            echo "Failed to add contact: " . mysqli_connect_errno();
            return false;
        }
        return true;
    }

    function add_notification($content_id, $username, $sender, $secret, $link, $text): bool
    {
        $query = mysqli_query($this->db, "INSERT INTO notifications VALUES (null,'$content_id', '$username', '$sender', '$secret', '$link', '$text')");
        var_dump($query);
        if(mysqli_connect_errno())
        {
            echo "Failed to add notification: " . mysqli_connect_errno();
            return false;
        }
        return true;
    }

    function add_interaction($content_id, $username, $sender, $type, $enc_int): bool
    {
        $query = mysqli_query($this->db, "INSERT INTO interactions VALUES (null,'$content_id', '$username', '$sender', '$type', '$enc_int')");
        var_dump($query);
        if(mysqli_connect_errno())
        {
            echo "Failed to add interaction: " . mysqli_connect_errno();
            return false;
        }
        return true;
    }

    function clear_tables(): void
    {
        mysqli_query($this->db, "DELETE FROM users");
        mysqli_query($this->db, "DELETE FROM contacts");
        mysqli_query($this->db, "DELETE FROM notifications");
        mysqli_query($this->db, "DELETE FROM posts");
        mysqli_query($this->db, "DELETE FROM interactions");

    }

    public function get_notifications($username): ?array
    {
        $query = mysqli_query($this->db, "SELECT * FROM notifications WHERE username='$username'");
        if(mysqli_num_rows($query) == 0)
            return null;
        else {
            $i = 0;
            while ($row = mysqli_fetch_array($query)) {
                $rows[$i]  = $row;
                $i++;
            }
            return $rows;
        }
    }

    function get_interactions_by_contentid($content_id)
    {
        $query = mysqli_query($this->db, "SELECT * FROM interactions WHERE content_id='$content_id'");
        if(mysqli_num_rows($query) == 0)
            return null;
        else {
            $i = 0;
            while ($row = mysqli_fetch_array($query)) {
                $rows[$i]  = $row;
                $i++;
            }
            return $rows;
        }
    }


}