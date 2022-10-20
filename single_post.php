<?php

require_once("includes/header.php");

if(isset($_GET['id'])) {
    $id = $_GET['id'];
} else {
    $id = 0;
}
$post = new Post($con, $userLoggedIn);
$content = $post->getSinglePost($id);


echo "<div class='user_details column'>
		<a href='profile.php?profile_username=$userLoggedIn'>  <img src='$user->profilePicture'> </a>

		<div class='user_details_left_right'>
			<a href='profile.php?profile_username=$userLoggedIn'>$user->firstname $user->lastname</a>
			<br>
			Posts: $user->postsCount <br>
			Likes: $user->likesCount
		</div>
	</div>

	<div class='main_column column' id='main_column'>

		<div class='posts_area'>
            $content
		</div>

	</div>";

?>