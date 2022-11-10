<?php
namespace Iconet;


class Database
{
    protected \mysqli $database;

    //TODO use PDO as in the other Database class
    public function __construct()
    {
        $this->database = mysqli_connect(
            $_ENV['DB_ICONET_HOST'],
            $_ENV['DB_ICONET_USER'],
            $_ENV['DB_ICONET_PASSWORD'],
            $_ENV['DB_ICONET_DATABASE']
        );

        if(mysqli_connect_errno())
        {
            error_log((string)mysqli_connect_errno());
        }
    }

    function getPublickeyByAddress(string $address): string|bool
    {
        $query = mysqli_query($this->database, "SELECT publickey FROM users WHERE address='$address'");
        if (mysqli_num_rows($query) > 0){
            $row = (array) mysqli_fetch_array($query);
            return $row['publickey'];
        } else {
            return false;
        }
    }

    function getPrivateKeyByAddress(string $address): string|bool
    {
        $query = mysqli_query($this->database, "SELECT privatekey FROM users WHERE address='$address'");
        if (mysqli_num_rows($query) > 0){
            $row = (array) mysqli_fetch_array($query);
            return $row['privatekey'];
        } else {
            return false;
        }
    }

    function addPost(string $id, string $username, string $secret): bool
    {
        $query = mysqli_query($this->database, "INSERT INTO posts VALUES ('$id', '$username', '$secret')");
        if(mysqli_connect_errno())
        {
            echo "Failed to add post: " . mysqli_connect_errno();
            return false;
        }
        return true;
    }

    function getUserByName(string $name): array|bool
    {
        $query = mysqli_query($this->database, "SELECT * FROM users WHERE username='$name'");
        if (mysqli_num_rows($query) > 0){
            $user = mysqli_fetch_array($query);
            return $user;
        } else {
            return false;
        }
    }

    function getUserByAddress(string $address): array|bool
    {
        $query = mysqli_query($this->database, "SELECT * FROM users WHERE address='$address'");
        if (mysqli_num_rows($query) > 0){
            $user = (array) mysqli_fetch_array($query);
            return $user;
        } else {
            return false;
        }
    }

    function getPostById(string $ID): array|null
    {
        $query = mysqli_query($this->database, "SELECT * FROM posts WHERE id='$ID'");
        if (mysqli_num_rows($query) > 0){
            return mysqli_fetch_array($query);
        } else {
            return null;
        }
    }


    function addUser(string $username, string $address, string $pubkey, string $privkey): bool
    {
        $query = mysqli_query($this->database, "INSERT INTO users VALUES ('$username', '$address', '$pubkey', '$privkey')");
        if(mysqli_connect_errno())
        {
            echo "Failed to add user: " . mysqli_connect_errno();
            return false;
        }
        return true;
    }

    function getGlobaladdress(string $username): string|bool
    {
        $query = mysqli_query($this->database, "SELECT address FROM users WHERE username='$username'");
        if (mysqli_num_rows($query) > 0){
            $row = mysqli_fetch_array($query);
            return (string) $row['address'];
        } else {
            return false;
        }
    }

    function getContacts(string $user): ?array
    {
        $result = mysqli_query($this->database, "SELECT * FROM contacts WHERE username='$user'") or trigger_error(mysqli_error());
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

    function deleteContact(string $user, string $address): bool
    {
        $delete_query = mysqli_query($this->database, "DELETE FROM contacts WHERE username='$user' AND friend_address='$address'");
        if(mysqli_connect_errno())
        {
            echo "Failed to delete contact: " . mysqli_connect_errno();
            return false;
        } else return true;
    }

    function addContact(string $user, string $address, string $pubkey): bool
    {
        $query = mysqli_query($this->database, "INSERT INTO contacts VALUES ('$user', '$address', '$pubkey')");
        if(mysqli_connect_errno())
        {
            echo "Failed to add contact: " . mysqli_connect_errno();
            return false;
        }
        return true;
    }

    function addNotification(string $content_id, string $username, string $sender, string $secret, string $link, string $text): bool
    {
        $query = mysqli_query($this->database, "INSERT INTO notifications VALUES (null,'$content_id', '$username', '$sender', '$secret', '$link', '$text')");
        var_dump($query);
        if(mysqli_connect_errno())
        {
            echo "Failed to add notification: " . mysqli_connect_errno();
            return false;
        }
        return true;
    }

    function addInteraction(string $content_id, string $username, string $sender, string $type, string $enc_int): bool
    {
        $query = mysqli_query($this->database, "INSERT INTO interactions VALUES (null,'$content_id', '$username', '$sender', '$type', '$enc_int')");
        var_dump($query);
        if(mysqli_connect_errno())
        {
            echo "Failed to add interaction: " . mysqli_connect_errno();
            return false;
        }
        return true;
    }

    function clearTables(): void
    {
        mysqli_query($this->database, "DELETE FROM users");
        mysqli_query($this->database, "DELETE FROM contacts");
        mysqli_query($this->database, "DELETE FROM notifications");
        mysqli_query($this->database, "DELETE FROM posts");
        mysqli_query($this->database, "DELETE FROM interactions");

    }

    public function getNotifications(string $username): ?array
    {
        $query = mysqli_query($this->database, "SELECT * FROM notifications WHERE username='$username'");
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

    function getInteractionsByContentId(string $content_id): ?array
    {
        $query = mysqli_query($this->database, "SELECT * FROM interactions WHERE content_id='$content_id'");
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