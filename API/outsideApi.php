<?php

$respone["a"] = "A";
$respone["b"] = "B";
echo json_encode($respone);
exit;

if(!$_SERVER['REQUEST_METHOD'] == 'POST') {
    echo http_response_code(400);
    exit;
}


$body = file_get_contents('php://input');

if($body === false) {
    echo http_response_code(400);
    exit;
}

$packet = json_decode($body);
if(!$packet) {
    echo http_response_code(400);
    exit;
}


if(isset($packet->id, $packet->user)) {
    $post = Database::singleton()->getPost($packet->id, $packet->user);
    if($post != null) {
        return json_encode($post);
    } else {
        echo http_response_code(300); //no results
    }
} else {
    echo http_response_code(440); //bad querry
}

?>