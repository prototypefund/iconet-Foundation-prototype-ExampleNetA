<?php

namespace Iconet;


use mysqli;
use mysqli_sql_exception;

class Database
{
    protected mysqli $database;

    //TODO use PDO as in the other Database class
    public function __construct()
    {
        $this->database = mysqli_connect(
            $_ENV['DB_ICONET_HOST'],
            $_ENV['DB_ICONET_USER'],
            $_ENV['DB_ICONET_PASSWORD'],
            $_ENV['DB_ICONET_DATABASE']
        );

        if(mysqli_connect_errno()) {
            error_log((string)mysqli_connect_errno());
        }
    }

    public function getPublickeyByAddress(string $address): string|bool
    {
        $query = mysqli_query($this->database, "SELECT publickey FROM users WHERE address='$address'");
        if(mysqli_num_rows($query) > 0) {
            $row = (array)mysqli_fetch_array($query);
            return $row['publickey'];
        } else {
            return false;
        }
    }

    public function getPrivateKeyByAddress(string $address): string|bool
    {
        $query = mysqli_query($this->database, "SELECT privatekey FROM users WHERE address='$address'");
        if(mysqli_num_rows($query) > 0) {
            $row = (array)mysqli_fetch_array($query);
            return $row['privatekey'];
        } else {
            return false;
        }
    }

    public function addPost(string $username, string $secret, string $formatId, string $content): ?int
    {
        $stmt = $this->database->prepare(
            "INSERT INTO posts (username, secret, formatId, content) VALUES (?, ?, ?, ?)"
        );
        $stmt->bind_param("ssss", $username, $secret, $formatId, $content);
        $stmt->execute();

        if(mysqli_connect_errno()) {
            echo "Failed to add post: " . mysqli_connect_errno();
            return null;
        }
        return $this->database->insert_id;
    }

    public function getUserByName(string $name): array|bool
    {
        $query = mysqli_query($this->database, "SELECT * FROM users WHERE username='$name'");
        if(mysqli_num_rows($query) > 0) {
            $user = mysqli_fetch_array($query);
            return $user;
        } else {
            return false;
        }
    }

    public function getUserByAddress(string $address): array|bool
    {
        $query = mysqli_query($this->database, "SELECT * FROM users WHERE address='$address'");
        if(mysqli_num_rows($query) > 0) {
            $user = (array)mysqli_fetch_array($query);
            return $user;
        } else {
            return false;
        }
    }

    public function getPostById(string $id): array|null
    {
        $query = mysqli_query($this->database, "SELECT * FROM posts WHERE id='$id'");
        if(mysqli_num_rows($query) > 0) {
            return mysqli_fetch_array($query);
        } else {
            return null;
        }
    }


    public function addUser(string $username, string $address, string $pubkey, string $privkey): bool
    {
        try {
            $query = mysqli_query(
                $this->database,
                "INSERT INTO users VALUES ('$username', '$address', '$pubkey', '$privkey')"
            );
        } catch(mysqli_sql_exception $ex) {
            return false;
        }
        if(mysqli_connect_errno()) {
            echo "Failed to add user: " . mysqli_connect_errno();
            return false;
        }
        return true;
    }

    public function getGlobaladdress(string $username): string|bool
    {
        $query = mysqli_query($this->database, "SELECT address FROM users WHERE username='$username'");
        if(mysqli_num_rows($query) > 0) {
            $row = mysqli_fetch_array($query);
            return (string)$row['address'];
        } else {
            return false;
        }
    }

    /**
     * @param string $user
     * @return array<Contact>
     */
    public function getContacts(string $user): array
    {
        $result = mysqli_query($this->database, "SELECT * FROM contacts WHERE username='$user'") or trigger_error(
            mysqli_error()
        );
        $contacts = [];
        while($row = mysqli_fetch_array($result)) {
            $contacts[] = new Contact(
                new Address($row['friend_address']),
                $row['friend_pubkey']
            );
        }
        return $contacts;
    }

    public function deleteContact(string $user, string $address): bool
    {
        $delete_query = mysqli_query(
            $this->database,
            "DELETE FROM contacts WHERE username='$user' AND friend_address='$address'"
        );
        if(mysqli_connect_errno()) {
            echo "Failed to delete contact: " . mysqli_connect_errno();
            return false;
        } else {
            return true;
        }
    }

    public function addContact(string $user, string $address, string $pubkey): bool
    {
        $query = mysqli_query($this->database, "INSERT INTO contacts VALUES ('$user', '$address', '$pubkey')");
        if(mysqli_connect_errno()) {
            echo "Failed to add contact: " . mysqli_connect_errno();
            return false;
        }
        return true;
    }

    public function addNotification(
        string $content_id,
        string $username,
        string $sender,
        string $secret,
        string $payload,
        string $formatId
    ): bool {
        $query = mysqli_query(
            $this->database,
            "INSERT INTO notifications VALUES (null,'$content_id', '$username', '$sender', '$secret', '$payload', '$formatId')"
        );
        if(mysqli_connect_errno()) {
            echo "Failed to add notification: " . mysqli_connect_errno();
            return false;
        }
        return true;
    }


    public function clearTables(): void
    {
        mysqli_query($this->database, "DELETE FROM users");
        mysqli_query($this->database, "DELETE FROM contacts");
        mysqli_query($this->database, "DELETE FROM notifications");
        mysqli_query($this->database, "DELETE FROM posts");
    }

    public function getNotifications(string $username): ?array
    {
        $query = mysqli_query($this->database, "SELECT * FROM notifications WHERE username='$username'");
        if(mysqli_num_rows($query) == 0) {
            return null;
        } else {
            $i = 0;
            while($row = mysqli_fetch_array($query)) {
                $rows[$i] = $row;
                $i++;
            }
            return $rows;
        }
    }



}