<?php
require_once('../config/config.php');


if($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    exit('Request method must be GET');
}


if(!isset($_GET['id'])) {
    http_response_code(400);
    exit('"id" required');
}

$post = Database::singleton()->getPost($_GET['id'], true);

if($post == null) {
    exit(404);
}

echo json_encode($post);
?>