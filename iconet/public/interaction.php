<?php

namespace Iconet;

require_once "../../config/config.php";

// Endpoint for client interactions

// TODO All these redundant checks should be handled by a middleware layer

if(isset($_SESSION['username'])) {
    $username = $_SESSION['username'];
    $user = User::fromUsername($username);
}

if(!isset($user)) {
    http_response_code(401);
    exit;
}

if(!$_SERVER['REQUEST_METHOD'] == 'POST') {
    http_response_code(400);
    exit;
}


$body = file_get_contents('php://input');

if($body === false) {
    http_response_code(400);
    exit;
}

$packet = json_decode($body);
if(!$packet) {
    http_response_code(400);
    exit;
}


// TODO Validate $packet input

if(isset($packet->payload)) {
    (new ArchivedProcessor($user))->postInteraction(
        $packet->payload,
        $packet->contentId,
        $user->address,
        $packet->to,
        $packet->secret
    );
}


// TODO Since we do decryption on the server, we use this as endpoint to fetch decrypted content
$contentData = (new InboxController($user))->prepareContentDataForClient(
    $packet->contentId,
    new Address($packet->actor ?? $packet->to),
    $packet->secret
);
echo json_encode($contentData, JSON_PRETTY_PRINT);




