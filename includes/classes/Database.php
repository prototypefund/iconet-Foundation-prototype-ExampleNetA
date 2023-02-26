<?php

class Database
{
    private static ?Database $singleton = null;
    private PDO $db;

    private function __construct()
    {
        $db_name = $_ENV['DB_DATABASE'];
        $db_user = $_ENV['DB_USER'];
        $db_pass = $_ENV['DB_PASSWORD'];
        $db_host = $_ENV['DB_HOST'];
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
            TRUNCATE TABLE friend_requests;
            TRUNCATE TABLE comments;
            TRUNCATE TABLE likes;
            TRUNCATE TABLE messages;
            TRUNCATE TABLE posts;
            TRUNCATE TABLE notifications;
            TRUNCATE TABLE is_friend;
            SET FOREIGN_KEY_CHECKS=1;"
        );
        $stmt->execute();
    }

    public function getUser(string $username)
    {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE username=:username");
        $stmt->execute(compact('username',));
        return $stmt->fetch();
    }

    public function getFriends($username)
    {
        $stmt = $this->db->prepare("SELECT friend FROM is_friend WHERE user=:username");
        $stmt->execute(compact('username',));
        return $stmt->fetchAll(PDO::FETCH_COLUMN, 0);
    }

    public function acceptFriendRequest(User $user, User $friend): void
    {
        $userName = $user->username;
        $friendName = $friend->username;

        $stmt = $this->db->prepare("INSERT INTO is_friend (user, friend) VALUES (:userName, :friendName)");
        $stmt->execute(compact('userName', 'friendName'));
        $stmt = $this->db->prepare("INSERT INTO is_friend (user, friend) VALUES (:friendName, :userName)");
        $stmt->execute(compact('userName', 'friendName'));

        $stmt = $this->db->prepare("DELETE FROM friend_requests WHERE user_to=:userName AND user_from=:friendName");
        $stmt->execute(compact('userName', 'friendName'));
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
        string $date_time_now,
        string $external_url = null
    ): int {
        $stmt = $this->db->prepare(
            "INSERT INTO comments (post_body, posted_by, posted_to, date_added, removed, post_id, external_url) 
            VALUES (:post_body, :postedByUser, :posted_to, :date_time_now, false, :post_id, :external_url)"
        );
        $stmt->execute(compact('post_id', 'post_body', 'postedByUser', 'posted_to', 'date_time_now', 'external_url'));

        return $this->db->lastInsertId();
    }

    public function friendRequestsCount(string $username)
    {
        $stmt = $this->db->prepare("SELECT COUNT(*) as count FROM friend_requests WHERE user_to=:username");
        $stmt->execute(compact('username'));
        return $stmt->fetch()['count'];
    }

    public function hasFriendRequestFrom(string $username, string $usernameFrom): bool
    {
        $stmt = $this->db->prepare(
            "SELECT * FROM friend_requests WHERE user_to=:username AND user_from=:usernameFrom"
        );
        $stmt->execute(compact('username', 'usernameFrom'));
        return $stmt->rowCount() > 0;
    }

    public function hasSentFriendRequestTo(string $username, string $usernameTo): bool
    {
        $stmt = $this->db->prepare(
            "SELECT * FROM friend_requests WHERE user_to=:usernameTo AND user_from=:username"
        );
        $stmt->execute(compact('username', 'usernameTo'));
        return $stmt->rowCount() > 0;
    }

    public function removeFriend(string $username, string $friend)
    {
        $stmt = $this->db->prepare(
            "DELETE FROM is_friend WHERE (user=:username AND friend=:friend 
                                  OR user=:friend AND friend=:username)"
        );
        $stmt->execute(compact('username', 'friend'));
    }

    public function createFriendRequest(string $username, string $userTo)
    {
        $stmt = $this->db->prepare(
            "INSERT INTO friend_requests (user_from, user_to) VALUES (:username, :userTo)"
        );
        $stmt->execute(compact('username', 'userTo'));
    }

    public function deleteUser(string $username)
    {
        $stmt = $this->db->prepare(
            "DELETE FROM users WHERE username=:username"
        );
        $stmt->execute(compact('username',));
    }

    public function getComments(string $post_id)
    {
        $stmt = $this->db->prepare("SELECT * FROM comments WHERE post_id=:post_id");
        $stmt->execute(compact('post_id'));
        return $stmt->fetchAll();
    }

    public function getPost(string $id, bool $return_comments = false)
    {
        $stmt = $this->db->prepare("SELECT * FROM posts WHERE id=:id");
        $stmt->execute(compact('id'));
        $post = $stmt->fetch(PDO::FETCH_ASSOC);
        if($return_comments) {
            return ['post' => $post, 'comments' => self::getComments($id)];
        } else {
            return $post;
        }
    }

    


}