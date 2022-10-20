<?php  
require_once("../../config/config.php");

$limit = 10; //Number of posts to be loaded per call

$posts = new Post($con, $_SESSION['username']);
echo $posts->getFeedPosts($_REQUEST['startAfter'] ?? null, $limit);
?>