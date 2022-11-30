<?php

namespace Iconet;

require_once "../../config/config.php";

// User interface for open inbox and endpoint for incoming requests

if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $body = file_get_contents('php://input');

    if($body === false) {
        http_response_code(400);
        exit;
    }

    echo (new S2SReceiver())->receive($body);
} else {
    if(isset($_SESSION['username'])) {
        $username = $_SESSION['username'];
        $user = User::fromUsername($username);
    }
    if(!isset($user)) {
        header("Location: /register.php");
        exit();
    }
    include "inbox.php";
}
