<?php

class User
{
    private array $friends;
    private string $usernameReference;

    public string $firstname;
    public string $lastname;
    public string $username;
    public string $email;
    public string $profilePicture;
    public int $likesCount;
    public int $postsCount;
    public bool $isClosed;

    public function __construct($username)
    {
        $userData = Database::singleton()->getUser($username);
        $friends = Database::singleton()->getFriends($username);
        
        $this->friends = $friends;
        $this->firstname = $userData['first_name'];
        $this->lastname = $userData['last_name'];
        $this->username = $userData['username'];
        $this->email = $userData['email'];
        $this->profilePicture = $userData['profile_pic'];
        $this->likesCount = $userData['num_likes'];
        $this->postsCount = $userData['num_posts'];
        $this->isClosed = $userData['user_closed'];

        $this->usernameReference = $this->username;
    }

    public function store()
    {
        Database::singleton()->updateUser($this);
    }

    public function __toString(): string
    {
        return $this->username;
    }


    public function getFirstAndLastName()
    {
        return $this->firstname . " " . $this->lastname;
    }

    /**
     * @return array Usernames of the friends
     */
    public function getFriends()
    {
        return $this->friends;
    }

    /**
     * @return string Username wich references the user in the database, can be different to the current username.
     */
    public function getUsernameReference(): string
    {
        return $this->usernameReference;
    }

    public function getNumberOfFriendRequests()
    {
        return Database::singleton()->friendRequestsCount($this->username);
    }

    /**
     * @param User $candidate
     * @return bool True for the user themselves and their friends
     */
    public function isFriend(User $candidate)
    {
        return $this->isFriendByUsername($candidate->username);
    }

    public function isFriendByUsername(string $username)
    {
        if($this->username == $username) {
            return true;
        }
        return in_array($username, $this->friends);
    }

    public function didReceiveRequest(string $userFrom) : bool
    {
        return Database::singleton()->hasFriendRequestFrom($this->username, $userFrom);
    }

    public function didSendFriendRequest(string $userTo) : bool
    {
        return Database::singleton()->hasSentFriendRequestTo($this->username, $userTo);
    }

    public function removeFriend(User $friend)
    {
        Database::singleton()->removeFriend($this->username, $friend->username);
        $this->friends = array_diff($this->friends, [$friend->username]);
        $friend->friends = array_diff($friend->friends, [$this->username]);
    }

    public function sendFriendRequest(string $userTo)
    {
        Database::singleton()->createFriendRequest($this->username, $userTo);
    }

    public function acceptFriendRequest(User $userFrom)
    {
        Database::singleton()->acceptFriendRequest($this, $userFrom);
        $this->friends[] = $userFrom->username;
        $userFrom->friends[] = $this->username;
    }

    public function getMutualFriendsCount(User $user)
    {
        return count(array_intersect($this->friends, $user->friends));
    }

    public static function exists(string $username): bool
    {
        return Database::singleton()->existsUser($username);
    }


}

?>