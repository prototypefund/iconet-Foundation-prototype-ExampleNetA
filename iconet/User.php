<?php

namespace Iconet;

class User extends Contact
{
    public readonly string $username;
    public readonly ?string $privateKey;

    public function __construct(
        string $username,
        Address $address,
        string $publicKey,
        string $privateKey = null
    ) {
        parent::__construct($address, $publicKey);

        $this->username = $username;
        $this->privateKey = $privateKey;
    }

    public static function fromAddress(Address $address): ?User
    {
        return self::fromUsername($address->local);
    }

    public static function fromUsername(string $username): ?User
    {
        global $iconetDB;
        $userData = $iconetDB->getUserByName($username);
        return $userData ? self::fromUserData($userData) : null;
    }

    public function addContact(Contact $contact): bool
    {
        global $iconetDB;
        return $iconetDB->addContact($this->username, $contact->address, $contact->publicKey);
    }

    /**
     * @param array<string> $userData
     * @return User|null
     */
    private static function fromUserData(array $userData): ?User
    {
        if(!$userData) {
            return null;
        } else {
            return new User(
                $userData['username'],
                new Address($userData['address']),
                $userData['publickey'],
                $userData['privatekey']
            );
        }
    }


}