<?php

require_once("includes/header.php");

if(isset($_GET['q'])) {
    $query = $_GET['q'];
} else {
    $query = "";
}

if(isset($_GET['type'])) {
    $type = $_GET['type'];
} else {
    $type = "name";
}
?>

<div class="main_column column" id="main_column">

    <?php
    if($query == "") {
        echo "You must enter something in the search box.";
    } else {
        //If query contains an underscore, assume user is searching for usernames
        if($type == "username") {
            $usersReturnedQuery = mysqli_query(
                $con,
                "SELECT * FROM users WHERE username LIKE '$query%' AND NOT user_closed LIMIT 8"
            );
        } //If there are two words, assume they are first and last names respectively
        else {
            $names = explode(" ", $query);

            if(count($names) == 3) {
                $usersReturnedQuery = mysqli_query(
                    $con,
                    "SELECT * FROM users WHERE (first_name LIKE '$names[0]%' AND last_name LIKE '$names[2]%') AND NOT user_closed"
                );
            } //If query has one word only, search first names or last names
            else {
                if(count($names) == 2) {
                    $usersReturnedQuery = mysqli_query(
                        $con,
                        "SELECT * FROM users WHERE (first_name LIKE '$names[0]%' AND last_name LIKE '$names[1]%') AND NOT user_closed"
                    );
                } else {
                    $usersReturnedQuery = mysqli_query(
                        $con,
                        "SELECT * FROM users WHERE (first_name LIKE '$names[0]%' OR last_name LIKE '$names[0]%') AND NOT user_closed"
                    );
                }
            }
        }

        //Check if results were found
        if(mysqli_num_rows($usersReturnedQuery) == 0) {
            echo "We can't find anyone with a " . $type . " like: " . $query;
        } else {
            echo mysqli_num_rows($usersReturnedQuery) . " results found: <br> <br>";
        }


        echo "<p class='grey'>Try searching for:</p>";
        echo "<a href='search.php?q=" . $query . "&type=name'>Names</a>, <a href='search.php?q=" . $query . "&type=username'>Usernames</a><br><br><hr id='search_hr'>";

        while($row = mysqli_fetch_array($usersReturnedQuery)) {
            $user_obj = new User($user->username);

            $button = "";
            $mutual_friends = "";

            if($user->username != $row['username']) {
                //Generate button depending on friendship status
                if($user_obj->isFriendByUsername($row['username'])) {
                    $button = "<input type='submit' name='" . $row['username'] . "' class='danger' value='Remove Friend'>";
                } else {
                    if($user_obj->didReceiveRequest($row['username'])) {
                        $button = "<input type='submit' name='" . $row['username'] . "' class='warning' value='Respond to request'>";
                    } else {
                        if($user_obj->didSendFriendRequest($row['username'])) {
                            $button = "<input type='submit' class='default' value='Request Sent'>";
                        } else {
                            $button = "<input type='submit' name='" . $row['username'] . "' class='success' value='Add Friend'>";
                        }
                    }
                }

                $mutual_friends = $user_obj->getMutualFriendsCount($user_obj) . " friends in common";


                //Button forms
                if(isset($_POST[$row['username']])) {
                    $other = new User($row['username']);
                    if($user_obj->isFriendByUsername($row['username'])) {
                        $user_obj->removeFriend($other);
                        header("Location: http://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]");
                    } elseif($user_obj->didReceiveRequest($row['username'])) {
                        header("Location: contacts.php");
                        exit();
                    } elseif(!$user_obj->didSendFriendRequest($row['username'])) {
                        $user_obj->sendFriendRequest($row['username']);
                        header("Location: http://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]");
                    }
                }
            }

            echo "<div class='search_result'>
					<div class='searchPageFriendButtons'>
						<form action='' method='POST'>
							" . $button . "
							<br>
						</form>
					</div>


					<div class='result_profile_pic'>
						<a href=./profile.php?profile_username=" . $row['username'] . "><img src='" . $row['profile_pic'] . "' style='height: 100px;'></a>
					</div>

						<a href=./profile.php?profile_username=" . $row['username'] . "> " . $row['first_name'] . " " . $row['last_name'] . "
						<p class='grey'> " . $row['username'] . "</p>
						</a>
						<br>
						" . $mutual_friends . "<br>
				</div>
				<hr id='search_hr'>";
        } //End while
    }


    ?>


</div>