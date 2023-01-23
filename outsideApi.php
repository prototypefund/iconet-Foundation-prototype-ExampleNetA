<?php

require_once("config/config.php");
require_once "includes/classes/Post.php";

if(isset($_GET['id'], $_GET['user'])) {
    echo $_GET['id'];
    $post = new Post($con, $_GET['user']);
    var_dump($post->getSinglePost($_GET['id'], $html = false));
}

?>