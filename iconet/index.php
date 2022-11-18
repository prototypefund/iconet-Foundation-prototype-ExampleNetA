<?php

namespace Iconet;

require_once "../config/config.php";

// User interface for open inbox and endpoint for incoming requests

if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $body = file_get_contents('php://input');

    if($body === false) {
        http_response_code(400);
        exit;
    }

    echo (new S2SReceiver())->receive($body);
} else {
    // TODO present personal inbox
    echo "<h3>Your open Inbox!</h3>";
}
