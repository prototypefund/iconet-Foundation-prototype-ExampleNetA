<?php

require_once("includes/header.php");

$message_obj = new Message($con, $userLoggedIn);

if(isset($_GET['profile_username'])) {
    $username = $_GET['profile_username'];

    if(!User::exists($username)) {
        echo "<p>Account does not exist</p>";
        exit();
    }

    $profileUser = new User($username);
    $num_friends = mysqli_query($con, "SELECT COUNT(*) FROM is_friend WHERE user='$username'")->num_rows;
}


if(isset($_POST['remove_friend'])) {
    $user = new User($userLoggedIn);
    $user->removeFriend($profileUser);
}

if(isset($_POST['add_friend'])) {
    $user = new User($userLoggedIn);
    $user->sendFriendRequest($username);
}
if(isset($_POST['respond_request'])) {
    header("Location: contacts.php");
}

if(isset($_POST['post_message'])) {
    if(isset($_POST['message_body'])) {
        $body = mysqli_real_escape_string($con, $_POST['message_body']);
        $date = date("Y-m-d H:i:s");
        $message_obj->sendMessage($username, $body, $date);
    }

    $link = '#profileTabs a[href="#messages_div"]';
    echo "<script> 
          $(function() {
              $('" . $link . "').tab('show');
          });
        </script>";
}

?>

<style type="text/css">
    .wrapper {
        margin-left: 0;
        padding-left: 0;
    }

</style>

<div class="profile_left">
    <img src="<?= $profileUser->profilePicture ?>">
    <div class="profile_info">
        <?php
        global $iconetDB;

        $address = $iconetDB->get_globaladdress($username); ?>
        <p>Address: <?= $address ?></p>
        <p>Posts: <?= $profileUser->postsCount ?></p>
        <p>Likes: <?= $profileUser->likesCount ?></p>
        <p>Friends: <?= $num_friends ?></p>
    </div>

    <form action="profile.php?profile_username=<?= $username ?>" method="POST">
        <?php
        $profile_user_obj = new User($username);
        if($profile_user_obj->isClosed) {
            header("Location: user_closed.php");
        }

        $logged_in_user_obj = new User($userLoggedIn);

        if($userLoggedIn != $username) {
            if($logged_in_user_obj->isFriend($profile_user_obj)) {
                echo '<input type="submit" name="remove_friend" class="danger" value="Remove Friend"><br>';
            } else {
                if($logged_in_user_obj->didReceiveRequest($username)) {
                    echo '<input type="submit" name="respond_request" class="warning" value="Respond to Request"><br>';
                } else {
                    if($logged_in_user_obj->didSendFriendRequest($username)) {
                        echo '<input type="submit" name="" class="default" value="Request Sent"><br>';
                    } else {
                        echo '<input type="submit" name="add_friend" class="success" value="Add Friend"><br>';
                    }
                }
            }
        }

        ?>
    </form>
    <input type="submit" class="deep_blue" data-toggle="modal" data-target="#post_form" value="Post Something">

    <?php
    if($userLoggedIn != $username) {
        echo '<div class="profile_info_bottom">';
        echo $logged_in_user_obj->getMutualFriendsCount($profile_user_obj) . " Mutual friends";
        echo '</div>';
    }


    ?>

</div>


<div class="profile_main_column column">

    <ul class="nav nav-tabs" role="tablist" id="profileTabs">
        <li role="presentation" class="active"><a href="#newsfeed_div" aria-controls="newsfeed_div" role="tab"
                                                  data-toggle="tab">Newsfeed</a></li>
        <li role="presentation"><a href="#messages_div" aria-controls="messages_div" role="tab" data-toggle="tab">Messages</a>
        </li>
    </ul>

    <div class="tab-content">

        <div role="tabpanel" class="tab-pane active" id="newsfeed_div">
            <div class="posts_area"></div>
            <img id="loading" src="assets/images/icons/loading.gif">
        </div>


        <div role="tabpanel" class="tab-pane" id="messages_div">
            <?php


            echo "<h4>You and <a href='/profile.php?profile_username=$username'>" . $profile_user_obj->getFirstAndLastName(
                ) . "</a></h4><hr><br>";

            echo "<div class='loaded_messages' id='scroll_messages'>";
            echo $message_obj->getMessages($username);
            echo "</div>";
            ?>


            <div class="message_post">
                <form action="" method="POST">
                    <textarea name='message_body' id='message_textarea' placeholder='Write your message ...'></textarea>
                    <input type='submit' name='post_message' class='info' id='message_submit' value='Send'>
                </form>

            </div>

            <script>
                $('a[data-toggle="tab"]').on('shown.bs.tab', function() {
                    var div = document.getElementById("scroll_messages");
                    div.scrollTop = div.scrollHeight;
                });
            </script>
        </div>


    </div>


</div>

<!-- Modal -->
<div class="modal fade" id="post_form" tabindex="-1" role="dialog" aria-labelledby="postModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">

            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span>
                </button>
                <h4 class="modal-title" id="postModalLabel">Post something!</h4>
            </div>

            <div class="modal-body">
                <p>This will appear on the user's profile page and also their newsfeed for your friends to see!</p>

                <form class="profile_post" action="" method="POST">
                    <div class="form-group">
                        <textarea class="form-control" name="post_body"></textarea>
                        <input type="hidden" name="user_from" value="<?= $userLoggedIn ?>">
                        <input type="hidden" name="user_to" value="<?= $username ?>">
                    </div>
                </form>
            </div>


            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" name="post_button" id="submit_profile_post">Post</button>
            </div>
        </div>
    </div>
</div>


<script>
    var userLoggedIn = '<?= $userLoggedIn ?>';
    var profileUsername = '<?= $username ?>';

    $(document).ready(function() {

        $('#loading').show();

        //Original ajax request for loading first posts
        $.ajax({
            url: "includes/handlers/ajax_load_profile_posts.php",
            data: `profile=${profileUsername}`,
            cache: false,

            success: function(data) {
                $('#loading').hide();
                $('.posts_area').html(data);
            }
        });

        $(window).scroll(function() {
            var last = $('.posts_area').find('.last').val();
            var more = $('.posts_area').find('.more').val();

            if(((window.innerHeight + window.scrollY) >= document.documentElement.offsetHeight)
                && more && $('#loading').is(":hidden")
            ) {
                $('#loading').show();

                var ajaxReq = $.ajax({
                    url: "includes/handlers/ajax_load_profile_posts.php",
                    data: `profile=${profileUsername}&startAfter=${last}`,
                    cache: false,

                    success: function(response) {
                        $('.posts_area').find('.last').remove();
                        $('.posts_area').find('.more').remove();
                        $('.posts_area').find('.noMorePostsText').remove();

                        $('#loading').hide();
                        $('.posts_area').append(response);
                    }
                });
            } //End if

            return false;
        }); //End (window).scroll(function())
    });

</script>


</div>
</body>
</html>