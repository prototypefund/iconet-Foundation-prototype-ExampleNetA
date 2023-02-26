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

if(!isset($packet['id'])) {
    http_response_code(400);
    exit('"id" required');
}

$post = Database::singleton()->getPost($packet['id'], true);

if($post == null) {
    exit(404);
}

echo json_encode($post);
?>