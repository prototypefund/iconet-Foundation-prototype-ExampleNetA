<!-- Load comments -->
<?php
$get_comments = mysqli_query($con, "SELECT * FROM comments WHERE post_id='$post_id' ORDER BY id ASC");
$count = mysqli_num_rows($get_comments);

if($count != 0) {
    while($comment = mysqli_fetch_array($get_comments)) {
        $comment_body = $comment['post_body'];
        $posted_to = $comment['posted_to'];
        $posted_by = $comment['posted_by'];
        $date_added = $comment['date_added'];
        $removed = $comment['removed'];
        $externalURL = $comment['external_url'];

        if(isset($externalURL)) {
            ?>
            <div class="comment_section"> 
                External Profile: <a href=<?= $externalURL ?> target="_parent">
                    <?php echo $posted_by ?>
                </a>
                <?= $date_added ?>
                <br>
                <?= $comment_body ?>
                <hr>
            </div>

            <?php
        } else {
            $user_obj = new User($posted_by);

            ?>
            <div class="comment_section">
                <a href=./profile.php?profile_username=<?= $posted_by ?> target="_parent">
                    <img src="<?= $user_obj->profilePicture ?>" title="<?= $posted_by ?>" style="float:left;" height="30">
                </a>
                <a href=./profile.php?profile_username=<?php
                    echo $posted_by ?> target="_parent"> <b> <?= $user_obj->getFirstAndLastName() ?> </b></a>
                &nbsp;&nbsp;&nbsp;&nbsp;
                <?= $date_added ?>
                <br>
                <?= $comment_body ?>
                <hr>
            </div>
            
            <?php
        }
    }
} else {
    echo "<br><br>No Comments to Show!";
}
?>