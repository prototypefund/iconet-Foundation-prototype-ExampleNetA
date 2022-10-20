<?php
require_once("../../config/config.php");

$query = $_POST['query'];
$userLoggedIn = $_POST['userLoggedIn'];

$names = explode(" ", $query);

//If query contains an underscore, assume user is searching for usernames
if(strpos($query, '_') !== false) 
	$usersReturnedQuery = mysqli_query($con, "SELECT * FROM users WHERE username LIKE '$query%' AND NOT user_closed LIMIT 8");
//If there are two words, assume they are first and last names respectively
else if(count($names) == 2)
	$usersReturnedQuery = mysqli_query($con, "SELECT * FROM users WHERE (first_name LIKE '$names[0]%' AND last_name LIKE '$names[1]%') AND NOT user_closed LIMIT 8");
//If query has one word only, search first names or last names 
else 
	$usersReturnedQuery = mysqli_query($con, "SELECT * FROM users WHERE (first_name LIKE '$names[0]%' OR last_name LIKE '$names[0]%') AND NOT user_closed LIMIT 8");


if($query != ""){
    $user = new User($con, $userLoggedIn);

	while($row = mysqli_fetch_array($usersReturnedQuery)) {

		if($row['username'] != $userLoggedIn) {
            $other = new User($con, $row['username']);
            $mutual_friends = $user->getMutualFriendsCount($other) . " friends in common";
        }else {
            $mutual_friends = "";
        }

		echo "<div class='resultDisplay'>
				<a href=./profile.php?profile_username=" . $row['username'] . " style='color: #1485BD'>
					<div class='liveSearchProfilePic'>
						<img src='" . $row['profile_pic'] ."'>
					</div>

					<div class='liveSearchText'>
						" . $row['first_name'] . " " . $row['last_name'] . "
						<p>" . $row['username'] ."</p>
						<p class='grey'>" . $mutual_friends ."</p>
					</div>
				</a>
				</div>";

	}

}

?>