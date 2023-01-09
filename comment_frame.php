<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title></title>
    <link rel="stylesheet" type="text/css" href="assets/css/style.css">
</head>
<body>

<style>
    * {
        font-size: 12px;
        font-family: Arial, Helvetica, sans-serif;
    }

</style>

<?php
require 'config/config.php';

if(isset($_SESSION['username'])) {
    $userLoggedIn = $_SESSION['username'];
    $user_details_query = mysqli_query($con, "SELECT * FROM users WHERE username='$userLoggedIn'");
    $user = mysqli_fetch_array($user_details_query);
} else {
    header("Location: register.php");
}

$post_id = $_GET['post_id'];
$user_query = mysqli_query($con, "SELECT added_by, user_to FROM posts WHERE id='$post_id'");
$row = mysqli_fetch_array($user_query);

$posted_to = $row['added_by'];
$user_to = $row['user_to'];

if(isset($_POST['postComment' . $post_id])) {
    $post_body = $_POST['post_body'];
    $post_body = mysqli_escape_string($con, $post_body);
    $date_time_now = date("Y-m-d H:i:s");

    Database::singleton()->createComment($post_id, $post_body, $userLoggedIn, $posted_to, $date_time_now, false);

    if($posted_to != $userLoggedIn) {
        $notification = new Notification($con, $userLoggedIn);
        $notification->insertNotification($post_id, $posted_to, "comment");
    }

    if($user_to != 'none' && $user_to != $userLoggedIn) {
        $notification = new Notification($con, $userLoggedIn);
        $notification->insertNotification($post_id, $user_to, "profile_comment");
    }


    $get_commenters = mysqli_query($con, "SELECT * FROM comments WHERE post_id='$post_id'");
    $notified_users = array();
    while($row = mysqli_fetch_array($get_commenters)) {
        if($row['posted_by'] != $posted_to && $row['posted_by'] != $user_to
            && $row['posted_by'] != $userLoggedIn && !in_array($row['posted_by'], $notified_users)) {
            $notification = new Notification($con, $userLoggedIn);
            $notification->insertNotification($post_id, $row['posted_by'], "comment_non_owner");

            array_push($notified_users, $row['posted_by']);
        }
    }


    echo "<p>Comment Posted! </p>";
}
?>
<form action="comment_frame.php?post_id=<?= $post_id ?>" id="comment_form" name="postComment<?= $post_id ?>"
      method="POST">
    <textarea name="post_body"></textarea>
    <input type="submit" name="postComment<?= $post_id ?>" value="Post">
</form>

<!-- Load comments -->
<?php
$get_comments = mysqli_query($con, "SELECT * FROM comments WHERE post_id='$post_id' ORDER BY id ASC");
$count = mysqli_num_rows($get_comments);

if($count != 0) {
    while($comment = mysqli_fetch_array($get_comments)) {
        $comment_body = $comment['post_body'];
        $posted_to = $comment['posted_to'];
        $posted_by = $comment['posted_by'];
        $date_added = $comment['date_added'];
        $removed = $comment['removed'];

        $time_message = date("Y-m-d H:i:s");

        $user_obj = new User($posted_by);


        ?>
        <div class="comment_section">
            <a href=./profile.php?profile_username=<?= $posted_by ?> target="_parent">
                <img src="<?= $user_obj->profilePicture ?>" title="<?= $posted_by ?>" style="float:left;" height="30">
            </a>
            <a href=./profile.php?profile_username=<?php
            echo $posted_by ?> target="_parent"> <b> <?= $user_obj->getFirstAndLastName() ?> </b></a>
            &nbsp;&nbsp;&nbsp;&nbsp;
            <?= $time_message ?>
            <br>
            <?= $comment_body ?>
            <hr>
        </div>
        <?php
    }
} else {
    echo "<br><br>No Comments to Show!";
}

?>


</body>
</html>