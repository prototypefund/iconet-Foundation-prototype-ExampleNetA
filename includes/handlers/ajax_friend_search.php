<?php

require_once("../../config/config.php");

$query = $_POST['query'];
$userLoggedIn = $_POST['userLoggedIn'];

$names = explode(" ", $query);

if(strpos($query, "_") !== false) {
    $usersReturned = mysqli_query(
        $con,
        "SELECT * FROM users WHERE username LIKE '$query%' AND NOT user_closed LIMIT 8"
    );
} else {
    if(count($names) == 2) {
        $usersReturned = mysqli_query(
            $con,
            "SELECT * FROM users WHERE (first_name LIKE '%$names[0]%' AND last_name LIKE '%$names[1]%') AND NOT user_closed LIMIT 8"
        );
    } else {
        $usersReturned = mysqli_query(
            $con,
            "SELECT * FROM users WHERE (first_name LIKE '%$names[0]%' OR last_name LIKE '%$names[0]%') AND NOT user_closed LIMIT 8"
        );
    }
}
if($query != "") {
    while($row = mysqli_fetch_array($usersReturned)) {
        $user = new User($con, $userLoggedIn);

        if($row['username'] != $userLoggedIn) {
            $other = new User($con, $row['username']);
            $mutual_friends = $user->getMutualFriendsCount($other) . " friends in common";
        } else {
            $mutual_friends = "";
        }

        if($user->isFriendByUsername($row['id'])) {
            echo "<div class='resultDisplay'>
					<a href='profile.php?profile_username=" . $row['username'] . "' style='color: #000'>
						<div class='liveSearchProfilePic'>
							<img src='" . $row['profile_pic'] . "'>
						</div>

						<div class='liveSearchText'>
							" . $row['first_name'] . " " . $row['last_name'] . "
							<p style='margin: 0;'>" . $row['username'] . "</p>
							<p class='grey'>" . $mutual_friends . "</p>
						</div>
					</a>
				</div>";
        }
    }
}

?>