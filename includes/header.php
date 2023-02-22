<?php

require_once $_SERVER['DOCUMENT_ROOT'] . '/config/config.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/iconet/User.php';

if(isset($_SESSION['username'])) {
    $userLoggedIn = $_SESSION['username'];
    $user = new User($userLoggedIn);
    $iconetUser = iconet\User::fromUsername($userLoggedIn);
} else {
    header("Location: register.php");
    exit();
}

?>

<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Welcome to ExampleNetA</title>

    <!-- Javascript -->
    <script src="assets/js/jquery.min.js"></script>
    <script src="assets/js/bootstrap.js"></script>
    <script src="assets/js/bootbox.min.js"></script>
    <script src="assets/js/script.js"></script>
    <script src="assets/js/jquery.jcrop.js"></script>
    <script src="assets/js/jcrop_bits.js"></script>


    <!-- CSS -->
    <link rel="stylesheet" href="assets/css/font-awesome.min.css">
    <link rel="stylesheet" type="text/css" href="assets/css/bootstrap.css">
    <link rel="stylesheet" type="text/css" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/jquery.Jcrop.css" type="text/css"/>
</head>
<body>

<div class="top_bar">

    <div class="logo">
        <a href="index.php">ExampleNetA</a>
    </div>


    <div class="search">

        <form action="search.php" method="GET" name="search_form">
            <input type="text" onkeyup="getLiveSearchUsers(this.value, '<?= $userLoggedIn ?>')" name="q"
                   placeholder="Search..." autocomplete="off" id="search_text_input">

            <div class="button_holder">
                <img src="assets/images/icons/magnifying_glass.png">
            </div>

        </form>

        <div class="search_results">
        </div>

        <div class="search_results_footer_empty">
        </div>


    </div>

    <nav>
        <?php
        //Unread messages
        $messages = new Message($con, $userLoggedIn);
        $num_messages = $messages->getUnreadNumber();

        //Unread notifications
        $notifications = new Notification($con, $userLoggedIn);
        $num_notifications = $notifications->getUnreadNumber();

        //Unread notifications
        $user_obj = new User($userLoggedIn);
        $num_requests = $user_obj->getNumberOfFriendRequests();
        ?>


        <a href='./profile.php?profile_username=<?= $userLoggedIn ?>'>
            <?= $user->firstname ?>
        </a>

        <a href='./iconet/public/index.php'>
            <i class="fa fa-envelope fa-lg"></i>
        </a>

        <a href="index.php">
            <i class="fa fa-home fa-lg"></i>
        </a>
        <a href=javascript:void(0); onclick="getDropdownData('<?= $userLoggedIn ?>', 'message')">
            <i class="fa fa-envelope fa-lg"></i>
            <?php
            if($num_messages > 0) {
                echo '<span class="notification_badge" id="unread_message">' . $num_messages . '</span>';
            }
            ?>
        </a>
        <a href=javascript:void(0); onclick="getDropdownData('<?= $userLoggedIn ?>', 'notification')">
            <i class="fa fa-bell fa-lg"></i>
            <?php
            if($num_notifications > 0) {
                echo '<span class="notification_badge" id="unread_notification">' . $num_notifications . '</span>';
            }
            ?>
        </a>
        <a href="contacts.php">
            <i class="fa fa-users fa-lg"></i>
            <?php
            if($num_requests > 0) {
                echo '<span class="notification_badge" id="unread_requests">' . $num_requests . '</span>';
            }
            ?>
        </a>
        <a href="settings.php">
            <i class="fa fa-cog fa-lg"></i>
        </a>
        <a href="includes/handlers/logout.php">
            <i class="fa fa-sign-out fa-lg"></i>
        </a>

    </nav>

    <div class="dropdown_data_window" style="height:0; border:none;"></div>
    <input type="hidden" id="dropdown_data_type" value="">


</div>


<script>
    var userLoggedIn = '<?= $userLoggedIn ?>';

    $(document).ready(function() {

        $('.dropdown_data_window').scroll(function() {
            var inner_height = $('.dropdown_data_window').innerHeight(); //Div containing data
            var scroll_top = $('.dropdown_data_window').scrollTop();
            var page = $('.dropdown_data_window').find('.nextPageDropdownData').val();
            var noMoreData = $('.dropdown_data_window').find('.noMoreDropdownData').val();

            if((scroll_top + inner_height >= $('.dropdown_data_window')[0].scrollHeight) && noMoreData == 'false') {

                var pageName; //Holds name of page to send ajax request to
                var type = $('#dropdown_data_type').val();


                if(type == 'notification')
                    pageName = "ajax_load_notifications.php";
                else if(type == 'message')
                    pageName = "ajax_load_messages.php"


                var ajaxReq = $.ajax({
                    url: "includes/handlers/" + pageName,
                    type: "POST",
                    data: "page=" + page + "&userLoggedIn=" + userLoggedIn,
                    cache: false,

                    success: function(response) {
                        $('.dropdown_data_window').find('.nextPageDropdownData').remove(); //Removes current .nextpage
                        $('.dropdown_data_window').find('.noMoreDropdownData').remove(); //Removes current .nextpage


                        $('.dropdown_data_window').append(response);
                    }
                });

            } //End if

            return false;

        }); //End (window).scroll(function())


    });

</script>


<div class="wrapper">