<?php

namespace Iconet;

/**
 * Interface for the native plattform to manage iconet objects
 */
class UserManager
//todo
{

    /**
     * Create a new iconet user. The address to the inbox will contain the username.
     * @param string $username Must only contain valid characters for the local part of an address
     * @return User|null The new user or null, if the user could not be created.
     */
    public static function addNewUser(string $username): ?User
    {
        $address = Address::fromUsername($username);
        if(!$address) {
            return null;
        }

        [$publicKey, $privateKey] = (new Crypto())->genkeyPair();
        $success = Database::singleton()->addUser($username, $address, $publicKey, $privateKey);
        if($success === false) {
            return null;
        }
        return new User($username, $address, $publicKey, $privateKey);
    }


    /**
     * Request the public key from an address and add it to the list of trusted contacts of the user.
     * TODO only add unique contacts, should be function on User and handle errors
     * TODO should be split into only create creating a contact from an address
     * TODO the second step is adding the contact AFTER the user verified the key.
     * @param User $to The user whose contacts are updated
     * @param Address $address The address of the new contact
     * @return bool True on success
     */
    public static function addContact(User $to, Address $address): bool
    {

        $success = $to->addContact(new Contact($address));
        if(!$success) {
            echo "Failed to add " . $address;
            return false;
        } else {
            return true;
        }
    }

}