<?php
require '../config/config.php';
 
if($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit('Request method must be POST');
}

$body = file_get_contents('php://input');
if(!$body) {
    http_response_code(400);
    exit('Empty body');
}

$packet = json_decode($body, true);
if(!$packet) {
    http_response_code(400);
    exit('Invalid json');
}

if(!isset($packet['post_id'], $packet['commentator'], $packet['profile_url'], $packet['comment'])) {
    http_response_code(400);
    exit('"post_id", "profile_url", "comment" and "commentator" fields are required');
}

$post_id = $packet['post_id'];
$profile_url = $packet['profile_url'];
$commentator = $packet['commentator'];
$comment = $packet['comment'];


$user_query = mysqli_query($con, "SELECT added_by, user_to FROM posts WHERE id='$post_id'");
$row = mysqli_fetch_array($user_query);

$posted_to = $row['added_by'];
$user_to = $row['user_to'];

$date_time_now = date("Y-m-d H:i:s");
$post = Database::singleton()->createComment($post_id, $comment, $commentator, $posted_to, $date_time_now, $profile_url);

echo "Inserted Post in DB!";
echo json_encode($post);

?>
