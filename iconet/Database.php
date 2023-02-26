<?php

namespace Iconet;

use PDO;

class Database
{

    private static ?Database $singleton = null;
    private PDO $db;

    private function __construct()
    {
        
        $db_name = $_ENV['DB_ICONET_DATABASE'];
        $db_user = $_ENV['DB_ICONET_USER'];
        $db_pass = $_ENV['DB_ICONET_PASSWORD'];
        $db_host = $_ENV['DB_ICONET_HOST'];

        $this->db = new PDO(
            "mysql:dbname=$db_name;host=$db_host;charset=latin1",
            $db_user, $db_pass
        );

        $this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }

    public static function singleton(): Database
    {
        if(self::$singleton) {
            return self::$singleton;
        }

        self::$singleton = new Database();
        return self::$singleton;
    }


    public function clearTables(): void
    {
        $stmt = $this->db->prepare(
            "SET FOREIGN_KEY_CHECKS=0;
            TRUNCATE TABLE users;
            TRUNCATE TABLE contacts;
            TRUNCATE TABLE notifications;
            TRUNCATE TABLE posts;
            SET FOREIGN_KEY_CHECKS=1;"
        );
        $stmt->execute();
    }

    public function getPublickeyByAddress(string $address): string|bool
    {
        $stmt = $this->db->prepare("SELECT publickey FROM users WHERE address=:address");
        $stmt->execute(compact('address',));
        return $stmt->fetchColumn();
    }

    public function getPrivateKeyByAddress(string $address): string|bool
    {
        $stmt = $this->db->prepare("SELECT privatekey FROM users WHERE address=:address");
        $stmt->execute(compact('address',));
        return $stmt->fetch();
    }

    public function addPost(string $username, string $secret, string $formatId, string $content): ?int
    {
        $stmt = $this->db->prepare(
            "INSERT INTO posts (username, secret, formatId, content) VALUES (:username, :secret, :formatId, :content)"
        );
        $stmt->execute(compact('username', 'secret', 'formatId', 'content',));

        return $this->db->lastInsertId();
    }

    public function getUserByName(string $username): array|bool
    {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE username=:username");
        $stmt->execute(compact('username',));
        return $stmt->fetch();
    }

    public function getUserByAddress(string $address): array|bool
    {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE address=:address");
        $stmt->execute(compact('address',));
        return $stmt->fetch();
    }

    public function getPostById(string $id): array|null
    {
        $stmt = $this->db->prepare("SELECT * FROM posts WHERE id=:id");
        $stmt->execute(compact('id',));
        return $stmt->fetch();
    }


    public function addUser(string $username, string $address, string $pubkey, string $privkey): string
    {
        $stmt = $this->db->prepare(
            "INSERT INTO users (username, address, publickey, privatekey) VALUES(:username, :address, :pubkey, :privkey)"
        );
        $stmt->execute(compact('username', 'address', 'pubkey', 'privkey',));
        return $this->db->lastInsertId();
    }

    public function getGlobaladdress(string $username): string|bool
    {
        $stmt = $this->db->prepare("SELECT address FROM users WHERE username=:username");
        $stmt->execute(compact('username'));
        return $stmt->fetchColumn();
    }

    /**
     * @param string $user
     * @return array<Contact>
     */
    public function getContacts(string $username): array
    {
        $stmt = $this->db->prepare("SELECT * FROM contacts WHERE username=:username");
        $stmt->execute(compact('username'));
        $result = $stmt->fetchAll();
        $contacts = [];
        foreach($result as $row) {
            $contacts[] = new Contact(
                new Address($row['friend_address']),
                $row['friend_pubkey']
            );
        }
        return $contacts;
    }

    public function deleteContact(string $username, string $address): bool
    {
        $stmt = $this->db->prepare(
            "DELETE FROM contacts WHERE username=:username AND friend_address=:address"
        );
        $stmt->execute(compact('username', 'address',));
        return $this->db->lastInsertId();
    }

    public function addContact(string $username, string $address, string $publickey): bool
    {
        $stmt = $this->db->prepare(
            "INSERT INTO contacts (username, friend_address, friend_pubkey) VALUES (:username, :address, :publickey)"
        );
        return $stmt->execute(compact('username', 'address', 'publickey'));
    }

    public function addNotification(
        string $content_id,
        string $username,
        string $sender,
        ?string $secret,
        string $payload,
        string $formatId
    ): bool {
        $stmt = $this->db->prepare(
            "INSERT INTO notifications (content_id, username, sender, secret, payload, formatId) VALUES (:content_id, :username, :sender, :secret, :payload, :formatId)"
        );
        $stmt->execute(compact('content_id', 'username', 'sender', 'secret', 'payload', 'formatId'));
        return $this->db->lastInsertId();
    }


    public function getNotifications(string $username): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM notifications WHERE username=:username");
        $stmt->execute(compact('username',));
        return $stmt->fetchAll();
    }



}