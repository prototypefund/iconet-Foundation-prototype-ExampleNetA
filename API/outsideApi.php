<?php

$error = "";

if(!$_SERVER['REQUEST_METHOD'] == 'POST') {
    $error = "no post";
}

$body = file_get_contents('php://input');

if($body === false) {
    $error = "no body;";
}

$packet = json_decode($body);
if(!$packet) {
    $error = " no packet";
}


if(isset($packet->id, $packet->user)) {
    $post = Database::singleton()->getPost($packet->id, $packet->user);
    if($post != null) {
        echo json_encode($post);
        exit;
    } else {
        $error = "no results"; //no results
    }
} else {
    $erorr = "bad querry"; //bad querry
}
echo json_encode($error);
exit;

?>