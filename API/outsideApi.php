<?php
require_once('../config/config.php');


if($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit('Request method must be POST');
}

$body = file_get_contents('php://input');
if(!$body) {
    http_response_code(400);
    exit('Empty body');
}

$packet = json_decode($body, true);
if(!$packet) {
    http_response_code(400);
    exit('Invalid json');
}

if(!isset($packet['id'], $packet['user'])) {
    http_response_code(400);
    exit('"id" and "user" fields are required');
}

$post = Database::singleton()->getPost($packet['id'], $packet['user'], true);

if($post == null) {
    exit(404);
}

echo json_encode($post);
?>