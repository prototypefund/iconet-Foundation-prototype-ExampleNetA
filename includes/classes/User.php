<?php

class User
{
    private $friends;
    private $con;
    private string $usernameReference;

    public string $firstname;
    public string $lastname;
    public string $username;
    public string $email;
    public string $profilePicture;
    public int $likesCount;
    public int $postsCount;
    public bool $isClosed;

    public function __construct($con, $username)
    {
        $user_details_query = mysqli_query($con, "SELECT * FROM users WHERE username='$username'");
        $userData = mysqli_fetch_array($user_details_query);
        $friends_query = mysqli_query($con, "SELECT friend FROM is_friend WHERE user='$userData[username]'");

        $this->con = $con;
        $this->init($userData);
        $f = $friends_query->fetch_all(MYSQLI_ASSOC);
        $this->friends = array_column($f, 'friend') ?? [];
    }

    private function init($userData)
    {
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
        $username = $this->username;
        $query = mysqli_query($this->con, "SELECT * FROM friend_requests WHERE user_to='$username'");
        return mysqli_num_rows($query);
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

    public function didReceiveRequest($user_from)
    {
        $user_to = $this->username;
        $check_request_query = mysqli_query(
            $this->con,
            "SELECT * FROM friend_requests WHERE user_to='$user_to' AND user_from='$user_from'"
        );
        if(mysqli_num_rows($check_request_query) > 0) {
            return true;
        } else {
            return false;
        }
    }

    public function didSendRequest($user_to)
    {
        $user_from = $this->username;
        $check_request_query = mysqli_query(
            $this->con,
            "SELECT * FROM friend_requests WHERE user_to='$user_to' AND user_from='$user_from'"
        );
        if(mysqli_num_rows($check_request_query) > 0) {
            return true;
        } else {
            return false;
        }
    }

    public function removeFriend($friend)
    {
        $username = $this->username;

        mysqli_query(
            $this->con,
            "DELETE  FROM is_friend WHERE (user='$username' AND friend='$friend' OR user='$friend' AND friend='$username')"
        );
    }

    public function sendRequest($user_to)
    {
        $user_from = $this->username;
        $query = mysqli_query($this->con, "INSERT INTO friend_requests VALUES(0, '$user_to', '$user_from')");
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