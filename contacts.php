<?php
include("includes/header.php"); //Header 
?>

<div class="main_column column" id="main_column">

    <h2>Your Contacts</h2>
    <?php
    // read friend array
    $friend_query = mysqli_query($con, "SELECT friend_array FROM Users WHERE username='$userLoggedIn'");
    $friendslist = $friend_query->fetch_array();
    // ExampleNetA holds users friendslist in a string in each users cell: ,usera,userb,userc,
    // explode the friend_array-string from the first (and only cell) of querry to an actual iterable array
    $friend_array = explode(',',$friendslist[0]);
    foreach ($friend_array as $friend)  {
        $user_querry = mysqli_query($con, "SELECT * FROM users WHERE username = '$friend' AND user_closed='no'");
        //display a resultDisplay div for each friend.
        while($row = mysqli_fetch_array($user_querry)) {
            echo "<div class='resultDisplay'>
					<a href='./profile.php?profile_username=" . $row['username'] . "' style='color: #000'>
						<div class='liveSearchProfilePic'>
							<img src='". $row['profile_pic'] . "'>
						</div>

						<div class='liveSearchText'>
							".$row['first_name'] . " " . $row['last_name']. "
							<p style='margin: 0;'>". $row['username'] . "</p>
						</div>
					</a>
				</div>";
        }

    }
    ?>

    <h4>Pending friend requests</h4>

	<?php  

	$query = mysqli_query($con, "SELECT * FROM friend_requests WHERE user_to='$userLoggedIn'");
	if(mysqli_num_rows($query) == 0)
		echo "You have no friend requests at this time!";
	else {
		while($row = mysqli_fetch_array($query)) {
			$user_from = $row['user_from'];
			$user_from_obj = new User($con, $user_from);

			echo $user_from_obj->getFirstAndLastName() . " sent you a friend request!";

			$user_from_friend_array = $user_from_obj->getFriendArray();

			if(isset($_POST['accept_request' . $user_from ])) {
				$add_friend_query = mysqli_query($con, "UPDATE users SET friend_array=CONCAT(friend_array, '$user_from,') WHERE username='$userLoggedIn'");
				$add_friend_query = mysqli_query($con, "UPDATE users SET friend_array=CONCAT(friend_array, '$userLoggedIn,') WHERE username='$user_from'");

				$delete_query = mysqli_query($con, "DELETE FROM friend_requests WHERE user_to='$userLoggedIn' AND user_from='$user_from'");
				echo "You are now friends!";
				header("Location: contacts.php");
			}

			if(isset($_POST['ignore_request' . $user_from ])) {
				$delete_query = mysqli_query($con, "DELETE FROM friend_requests WHERE user_to='$userLoggedIn' AND user_from='$user_from'");
				echo "Request ignored!";
				header("Location: contacts.php");
			}

			?>
			<form action="contacts.php" method="POST">
				<input type="submit" name="accept_request<?php echo $user_from; ?>" id="accept_button" value="Accept">
				<input type="submit" name="ignore_request<?php echo $user_from; ?>" id="ignore_button" value="Ignore">
			</form>
			<?php


		}

	}

	?>


</div>