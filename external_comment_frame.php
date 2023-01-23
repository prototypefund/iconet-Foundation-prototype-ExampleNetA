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
 
$post_id = $_GET['post_id'];
$commentingUsername = $_GET['commentingUsername'];
$commentingUserURL = $_GET['commentingUserURL'];
$commentingMsg = $_GET['msg'];


$user_query = mysqli_query($con, "SELECT added_by, user_to FROM posts WHERE id='$post_id'");
$row = mysqli_fetch_array($user_query);

$posted_to = $row['added_by'];
$user_to = $row['user_to'];

if(isset($post_id, $commentingUsername, $commentingUserURL, $commentingMsg)) {
    $date_time_now = date("Y-m-d H:i:s");
    Database::singleton()->createComment($post_id, $commentingMsg, $commentingUsername, $posted_to, $date_time_now, $commentingUserURL);

    echo "<p>Inserted Post in DB! </p>";
}


include "print_comments.php"
?>

</body>
</html>