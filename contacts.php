<?php
require_once("includes/header.php");
require_once("iconet/format_handlers.php");
require_once("iconet/request_builder.php");
require_once("iconet/db_handlers.php");
?>

<div class="main_column column" id="main_column">

    <h2>Your Contacts</h2>
    <?php
    // read friend array
    $friends = mysqli_query($con, "SELECT * FROM is_friend JOIN users u on u.username = is_friend.friend where user='$userLoggedIn' AND NOT user_closed");

    $result = "<p> You currently have no friends in this network.";
    while ($friend = mysqli_fetch_array($friends))  {

        $result .= "<div class='resultDisplay'>
					<a href='./profile.php?profile_username=$friend[username]' style='color: #000'>
						<div class='liveSearchProfilePic'>
							<img src='$friend[profile_pic]'>
						</div>

						<div class='liveSearchText'>
						    $friend[first_name] $friend[last_name]
							<p style='margin: 0;'>$friend[username]</p>
						</div>
					</a>
				</div>";
        }

    echo $result;
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

			$user_from_friend_array = $user_from_obj->getFriends();

			if(isset($_POST['accept_request' . $user_from ])) {
                Database::singleton()->acceptFriendRequest($user, $user_from_obj);
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
				<input type="submit" name="accept_request<?= $user_from ?>" id="accept_button" value="Accept">
				<input type="submit" name="ignore_request<?= $user_from ?>" id="ignore_button" value="Ignore">
			</form>
			<?php


		}

	}

	?>
    <h3>External Contacts</h3>
    <div>
        <?php
        $query = get_contacts($userLoggedIn);
        if(mysqli_num_rows($query) == 0)
        echo "<p>You have no external contacts stored at this time!</p>";
        else {
            echo "<form action='contacts.php' method='GET'>";

            echo "<p>These are your external contacts. Your postings are also delivered to them via iconet.</p>";
            while ($row = mysqli_fetch_array($query)) {
                echo $row['friend_address'] . " with the pubkey: " . $row['friend_pubkey'];
                echo "   <button type='submit' name='delete_address' value =". $row['friend_address'] . ">X</button>";
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