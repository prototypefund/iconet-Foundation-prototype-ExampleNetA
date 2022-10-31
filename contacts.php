<?php
include("includes/header.php"); //Header
include_once("iconet/formats.php");
include_once("iconet/request_builder.php");
include_once("iconet/database_old.php");
?>

<div class="main_column column" id="main_column">

    <h2>Your Contacts</h2>
    <?php
    // read friend array
    $friend_query = mysqli_query($con, "SELECT friend_array FROM users WHERE username='$userLoggedIn'");
    $friendslist = $friend_query->fetch_array();
    // ExampleNetA holds users friendslist in a string in each users cell: ,usera,userb,userc,
    // explode the friend_array-string from the first (and only cell) of querry to an actual iterable array
    $friend_array = explode(',',$friendslist[0]);
    $nofriends = "<p> You currently have no friends in this network.";
    foreach ($friend_array as $friend)  {
        $user_querry = mysqli_query($con, "SELECT * FROM users WHERE username = '$friend' AND user_closed='no'");
        //display a resultDisplay div for each friend with the given name (0 or 1 expected.)
        while($row = mysqli_fetch_array($user_querry)) {
            $nofriends = ""; // if there are friends, do not warn about no friends.
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
    echo $nofriends;
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
    <h3>External Contacts</h3>
    <div>
        <?php
        $contacts = get_contacts($userLoggedIn);
        if($contacts == null)
        echo "<p>You have no external contacts stored at this time!</p>";
        else {
            echo "<form action='contacts.php' method='GET'>";

            echo "<p>These are your external contacts. Your postings are also delivered to them via iconet.</p>";
            foreach ($contacts as $c) {
                echo $c['address'] . " with the pubkey: " . $c['pubkey'];
                echo "   <button type='submit' name='delete_address' value =". $c['address'] . ">X</button>";
                echo "<br><br>";
                }
                echo "</form>";
            }
        ?>

        <?php //handle deleted address
        if(isset($_GET['delete_address'])) {
            $address = strip_tags($_GET['delete_address']);
            if (delete_contact($userLoggedIn, $address))
                header("Location: contacts.php");
            else echo "Error: Could not delete ". $address;
        }
        ?>
    </div>


    <div>
        <h4>Add external contacts</h4>
        <p>Into the following textbox you can enter iconet-addresses of external contacts. <br>
            We'll make sure, you're postings are also delivered to them!</p>
        <form action="contacts.php" method="GET">
            <input type="text" name="add_address"><br> <br>
            <input type="submit" value="Enter">
        </form>

        <?php
        if(isset($_GET['add_address'])) {
            $address = strip_tags($_GET['add_address']);

            if (!check_address($address)){
                echo "Invalid Address.";
                exit;
            }
            $pubkey = request_pubkey($address);

            if (add_contact($userLoggedIn,$address,$pubkey))
                header("Location: contacts.php");
            else echo "Failed to add ".$address;
        }
        ?>

    </div>


</div>