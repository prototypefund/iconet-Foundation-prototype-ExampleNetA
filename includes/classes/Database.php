<?php

class Database
{
    private static ?Database $singleton = null;
    private PDO $db;

    private function __construct()
    {
        global $config;
        $db_name = $config['db_database'];
        $db_user = $config['db_user'];
        $db_pass = $config['db_password'];
        $db_host = $config['db_host'];
        $this->db = new PDO(
            "mysql:dbname=$db_name;host=$db_host;charset=latin1",
            $db_user, $db_pass
        );

        $this->db->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
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

    public function acceptFriendRequest(User $user, User $friend): void
    {
        $username = $user->username;
        $friendName = $friend->username;

        $stmt = $this->db->prepare("INSERT INTO is_friend (user, friend) VALUES (:userName, :friendName)");
        $stmt->execute(compact('username', 'friendName'));
        $stmt = $this->db->prepare("INSERT INTO is_friend (user, friend) VALUES (:friendName, :userName)");
        $stmt->execute(compact('username', 'friendName'));

        $stmt = $this->db->prepare("DELETE FROM friend_requests WHERE user_to=:userName AND user_from=:friendName");
        $stmt->execute(compact('username', 'friendName'));
    }

    public function createPost(
        string $body,
        User $added_by,
        string|null $user_to,
        string $date_added,
        string|null $imageName
    ): int {
        $added_by = $added_by->username;

        $stmt = $this->db->prepare(
            "INSERT INTO posts (body, added_by, user_to, date_added, user_closed, deleted, likes, image) VALUES(:body, :added_by, :user_to, :date_added, false, false, 0, :imageName)"
        );
        $stmt->execute(compact('body', 'added_by', 'user_to', 'date_added', 'imageName'));

        return $this->db->lastInsertId();
    }

    public function registerUser(
        string $firstname,
        string $lastname,
        string $username,
        string $email,
        string $password,
        string $date,
        string $profile_pic
    ): void {
        $stmt = $this->db->prepare(
            "INSERT INTO users (first_name, last_name, username, email, password, signup_date, profile_pic, num_posts, num_likes, user_closed) VALUES (:firstname, :lastname, :username, :email, :password, :date, :profile_pic, 0, 0, false)"
        );
        $stmt->execute(compact('firstname', 'lastname', 'username', 'email', 'password', 'date', 'profile_pic'));
    }

    public function updateUser(User $user): void
    {
        $stmt = $this->db->prepare(
            "UPDATE users SET first_name=:firstname, last_name=:lastname, username=:username, email=:email, profile_pic=:profilePicture, num_posts=:postsCount, num_likes=:likesCount, user_closed=:isClosed WHERE username=:usernameReference"
        );
        $stmt->execute([
            'firstname' => $user->firstname,
            'lastname' => $user->lastname,
            'username' => $user->username,
            'email' => $user->email,
            'profilePicture' => $user->profilePicture,
            'postsCount' => $user->postsCount,
            'likesCount' => $user->likesCount,
            'usernameReference' => $user->getUsernameReference(),
            'isClosed' => $user->isClosed ? 1 : 0
        ]);
    }

    public function existsUser(string $username): bool
    {
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM users WHERE username=:username");
        $stmt->execute(compact('username'));
        return $stmt->fetchColumn();
    }

    public function createComment(
        int $post_id,
        string $post_body,
        string $postedByUser,
        string $posted_to,
        string $date_time_now
    ): int {
        $stmt = $this->db->prepare(
            "INSERT INTO comments (post_body, posted_by, posted_to, date_added, removed, post_id) VALUES (:post_body, :postedByUser, :posted_to, :date_time_now, false, :post_id)"
        );
        $stmt->execute(compact('post_id', 'post_body', 'postedByUser', 'posted_to', 'date_time_now'));

        return $this->db->lastInsertId();
    }
}