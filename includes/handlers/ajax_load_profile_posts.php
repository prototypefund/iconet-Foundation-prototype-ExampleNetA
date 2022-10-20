<?php  
require_once("../../config/config.php");

$limit = 10; //Number of posts to be loaded per call

$posts = new Post($con, $_SESSION['username']);
echo $posts->getProfilePosts($_REQUEST['profile'],$_REQUEST['startAfter'] ?? null,  $limit);
?>