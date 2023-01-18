<?php

require_once("includes/header.php");
require_once("iconet/IconetOutbox.php");

global $user;
global $iconetUser;

if(isset($_POST['post'])) {
    $uploadOk = 1;
    $imageName = $_FILES['fileToUpload']['name'];
    $errorMessage = "";

    if($imageName == "") {
        $imageName = null;
    }

    if($imageName) {
        $targetDir = "assets/images/posts/";
        $imageName = $targetDir . uniqid() . basename($imageName);
        $imageFileType = pathinfo($imageName, PATHINFO_EXTENSION);

        if($_FILES['fileToUpload']['size'] > 10000000) {
            $errorMessage = "Sorry your file is too large";
            $uploadOk = 0;
        }

        if(strtolower($imageFileType) != "jpeg" && strtolower($imageFileType) != "png" && strtolower(
                $imageFileType
            ) != "jpg") {
            $errorMessage = "Sorry, only jpeg, jpg and png files are allowed";
            $uploadOk = 0;
        }

        if($uploadOk) {
            if(!move_uploaded_file($_FILES['fileToUpload']['tmp_name'], $imageName)) {
                $errorMessage = "Saving the upload failed";
                $uploadOk = 0;
            }
        }
    }

    if($uploadOk) {
        $post = new Post($con, $userLoggedIn);
        $postId = $post->submitPost($_POST['post_text'], null, $imageName);

        $payload = array('content'=>$_POST['post_text'], '$username'=>$userLoggedIn);

        $iconetOutbox = new iconet\IconetOutbox($iconetUser);
        $iconetOutbox->createPost($payload, "test");
    } else {
        echo "<div style='text-align:center;' class='alert alert-danger'>
				$errorMessage
			</div>";
    }
}
?>

<div class="user_details column">
    <a href=./profile.php?profile_username=<?= $user->username ?>>
        <img src="<?= $user->profilePicture ?>">
    </a>

    <div class="user_details_left_right">
        <a href=./profile.php?profile_username=<?= $user->username ?>>
            <?= $user->firstname ?> <?= $user->lastname ?>
        </a>
        <br>
        Posts: <?= $user->postsCount ?> <br>
        Likes: <?= $user->likesCount ?>
    </div>

</div>

<div class="main_column column">
    <form class="post_form" action="index.php" method="POST" enctype="multipart/form-data">
        <input type="file" name="fileToUpload" id="fileToUpload">
        <textarea name="post_text" id="post_text" placeholder="Got something to say?"></textarea>
        <input type="submit" name="post" id="post_button" value="Post">
        <hr>

    </form>

    <div class="posts_area"></div>
    <!-- <button id="load_more">Load More Posts</button> -->
    <img id="loading" src="assets/images/icons/loading.gif">


</div>

<div class="user_details column">

    <h4>Popular</h4>

    <div class="trends">

    </div>


</div>

<div id="test" style="background-color: red; position: absolute; left:0; top:0;"></div>


<script>
    var userLoggedIn = '<?=$userLoggedIn; ?>';

    $(document).ready(function() {

        $('#loading').show();

        //Original ajax request for loading first posts
        $.ajax({
            url: "includes/handlers/ajax_load_posts.php",
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
                    url: "includes/handlers/ajax_load_posts.php",
                    data: "startAfter=" + last,
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