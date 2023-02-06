<?php


class Post
{
    private User $user_obj;

    public function __construct($user)
    {
        $this->user_obj = new User($user);
    }

    /**
     * @param string $body text content of the post
     * @param ?string $user_to the username of the user on whose profile the post should be created.
     *              Can be null to post on the own profile.
     * @param ?string $imageName
     * @return ?int id of the new post or null, if failed
     */
    public function submitPost($body, string|null $user_to = null, string|null $imageName = null): ?int
    {
        $body = strip_tags($body); //removes html tags

        $date_added = date("Y-m-d H:i:s");
        $added_by = $this->user_obj->username;

        //If post is on own profile, user_to is null
        if($user_to == $added_by) {
            $user_to = null;
        }

        $returned_id = Database::singleton()->createPost($body, $this->user_obj, $user_to, $date_added, $imageName);

        //Insert notification
        if($user_to != null) {
            $notification = new Notification($this->con, $added_by);
            $notification->insertNotification($returned_id, $user_to, "profile_post");
        }

        //Update post count for user
        $num_posts = $this->user_obj->postsCount++;
        $this->user_obj->store();

        return $returned_id;
    }


    /**
     * @param ?int $startAfter show only posts older than this post
     * @param int $limit number of posts to fetch
     * @return void One page of posts for the users feed (self-posts or posts from friends)
     */
    public function getFeedPosts(?int $startAfter = null, int $limit = 10)
    {
        $username = $this->user_obj->username;
        $startAfterCondition = "";
        if($startAfter) {
            $startAfterCondition = "AND post.id<$startAfter";
        }

        $data_query = "SELECT 
                post.id as post_id, body, date_added, post.user_closed as poster_user_closed, deleted, likes, image, comment_count,
                poster.first_name as poster_first_name, poster.last_name as poster_last_name, poster.username as poster_username, poster.profile_pic as poster_profile_pic, poster.num_posts as poster_num_posts, poster.num_likes as poster_num_likes, poster.user_closed as poster_user_closed,
                user_to.first_name as to_first_name, user_to.last_name as to_last_name, user_to.username as to_username, user_to.profile_pic as to_profile_pic, user_to.num_posts as to_num_posts, user_to.num_likes as to_num_likes, user_to.user_closed as to_user_closed
            FROM (SELECT username
               FROM (SELECT username
                     from users
                     where username = '$username') as me
               UNION
                   (SELECT friend
                     FROM is_friend
                     WHERE user = '$username')
               ) as meAndFriends
            JOIN posts post ON meAndFriends.username=post.added_by
            JOIN users poster ON poster.username = post.added_by
            LEFT JOIN  users user_to on user_to.username = post.user_to
            LEFT JOIN (
                SELECT COUNT(id) as comment_count, post_id as cpid from comments group by cpid
            ) c on post.id = c.cpid
            WHERE NOT deleted AND NOT post.user_closed AND NOT poster.user_closed $startAfterCondition
            ORDER BY date_added DESC 
            LIMIT $limit;";

        return $this->display($data_query, $limit)['output'];
    }

    /**
     * @param string profile of this user will be shown
     * @param int|null $startAfter show only posts older than this post
     * @param int $limit number of posts to fetch
     * @return void One page of posts on the users profile (self-posts or posts addressed to the user)
     */
    public function getProfilePosts(string $username, int $startAfter = null, int $limit = 10): string
    {
        $startAfterCondition = "";
        if($startAfter) {
            $startAfterCondition = "AND post.id<$startAfter";
        }

        $data_query = "SELECT 
                post.id as post_id, body, date_added, post.user_closed as poster_user_closed, deleted, likes, image, comment_count,
                poster.first_name as poster_first_name, poster.last_name as poster_last_name, poster.username as poster_username, poster.profile_pic as poster_profile_pic, poster.num_posts as poster_num_posts, poster.num_likes as poster_num_likes, poster.user_closed as poster_user_closed,
                user_to.first_name as to_first_name, user_to.last_name as to_last_name, user_to.username as to_username, user_to.profile_pic as to_profile_pic, user_to.num_posts as to_num_posts, user_to.num_likes as to_num_likes, user_to.user_closed as to_user_closed
            FROM posts post
            JOIN users poster ON poster.username = post.added_by
            LEFT JOIN  users user_to on user_to.username = post.user_to
            LEFT JOIN (
                SELECT COUNT(id) as comment_count, post_id as cpid from comments group by cpid
            ) c on post.id = c.cpid
            WHERE (user_to.username='$username' OR user_to.username IS NULL AND poster.username='$username')
              AND NOT deleted AND NOT post.user_closed AND NOT poster.user_closed $startAfterCondition
            ORDER BY date_added DESC
                LIMIT $limit;";

        return $this->display($data_query, $limit)['output'];
    }


    public function getSinglePost($post_id, $html=True)
    {
        $userLoggedIn = $this->user_obj->username;

        //TODO notification not here
        $opened_query = mysqli_query(
            $this->con,
            "UPDATE notifications SET opened='yes' WHERE user_to='$userLoggedIn' AND link LIKE '%=$post_id'"
        );

        $data_query = "SELECT
                post.id as post_id, body, date_added, post.user_closed as poster_user_closed, deleted, likes, image, comment_count,
                poster.first_name as poster_first_name, poster.last_name as poster_last_name, poster.username as poster_username, poster.profile_pic as poster_profile_pic, poster.num_posts as poster_num_posts, poster.num_likes as poster_num_likes, poster.user_closed as poster_user_closed,
                user_to.first_name as to_first_name, user_to.last_name as to_last_name, user_to.username as to_username, user_to.profile_pic as to_profile_pic, user_to.num_posts as to_num_posts, user_to.num_likes as to_num_likes, user_to.user_closed as to_user_closed
            FROM posts post
            JOIN users poster ON poster.username = post.added_by
            LEFT JOIN  users user_to on user_to.username = post.user_to
            LEFT JOIN (
                SELECT COUNT(id) as comment_count, post_id as cpid from comments group by cpid
            ) c on post.id = c.cpid
            WHERE post.id='$post_id' AND NOT post.user_closed AND NOT poster.user_closed
                LIMIT 1;";
        
        $result = $this->display($data_query, 1);
        if(!$result['count']) {
            return "<p>This post does not exist, or you are not friends with the author.</p>";
        } else {
            if($html) {
                return $result['output'];
            } else {
                $query_result = mysqli_query($this->con, $data_query);
                return mysqli_fetch_array($query_result);
            }
        }
    }

    private function getUserProfileLink($row)
    {
        if($row['to_username'] == null) {
            return "";
        }
        $name = "${row['to_first_name']} ${row['to_last_name']}";
        $username = $row['to_username'];
        return "to <a href='profile.php?profile_username=$username'>$name</a>";
    }

    /**
     * @param $limit
     * @param string $query
     * @return array number of displayed posts
     */
    private function display(string $query, int $limit = 10): array
    {
        $count = 0;
        $last = 10e99;
        $output = "";

        $data_query = mysqli_query($this->con, $query);

        while($row = mysqli_fetch_array($data_query)) {
            $count++;
            $last = min($last, $row['post_id']);
            $output .= $this->assemblePost($row);
        }

        $more = $count == $limit;

        $output .= "<input type='hidden' class='last' value='$last'>
                    <input type='hidden' class='more' value='$more'>";

        if(!$more) {
            $output .= "<p class='noMorePostsText'> No more posts to show! </p>";
        }

        return compact('count', 'output');
    }

    private function assemblePost($row)
    {
        $id = $row['post_id'];
        $body = $row['body'];
        $added_by = $row['poster_username'];
        $date_time = $row['date_added'];
        $imagePath = $row['image'];
        $first_name = $row['poster_first_name'];
        $last_name = $row['poster_last_name'];
        $profile_pic = $row['poster_profile_pic'];
        $commets_count = $row['comment_count'] ?? 0;
        $delete_button = "";
        $imageDiv = "";

        $user_to_link = $this->getUserProfileLink($row);

        if($this->user_obj->username == $added_by) {
            $delete_button = "<button class='delete_button btn-danger' onclick='deletePost($id)'>
                                        <i class='fa-trash fa'></i>
                                  </button>";
        }
        if($imagePath != "") {
            $imageDiv = "<div class='postedImage'>
                                <img src='$imagePath'>
                            </div>";
        }

        return "<div class='status_post'>
                        <div class='post_profile_pic'>
                            <img src='$profile_pic' width='50'>
                        </div>

                        <div class='posted_by' style='color:#ACACAC;'>
                            <a href=./profile.php?profile_username=$added_by> $first_name $last_name </a> $user_to_link &nbsp;&nbsp;&nbsp;&nbsp;$date_time
                            $delete_button
                        </div>
                        <div id='post_body'>
                            $body
                            <br>
                            $imageDiv
                            <br>
                            <br>
                        </div>

                        <div class='newsfeedPostOptions'>
                            <span onclick='toggleComment($id)'>Comments($commets_count)</span>&nbsp;&nbsp;&nbsp;
                            <iframe src='like.php?post_id=$id'></iframe>
                        </div>

                    </div>
                    <div class='post_comment' id='toggleComment$id' style='display:none;'>
                        <iframe src='comment_frame.php?post_id=$id' id='comment_iframe'></iframe>
                    </div>
                    <hr>";
    }

}

?>