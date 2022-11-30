<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta
            http-equiv="Content-Security-Policy"
            content="default-src 'self'; child-src 'none';  connect-src *; style-src 'unsafe-inline'; script-src 'self' 'unsafe-inline';"/>
    <!--    FIXME unsafe inline-->
    <title>Inbox</title>
    <script defer src='/iconet/public/inbox.js' type="module"></script>
    <style>
        embedded-experience {
            width: 27em;
            height: 15em;

            margin: 2em;
            padding: 0.5em;
            border: solid 1px;
            display: inline-grid;
            flex-direction: column;
            background: lightgray;
        }
    </style>
</head>
<body>
<h3>Your open Inbox!</h3>

<?php

use Iconet\InboxController;

global $user;
echo (new InboxController($user))->renderInbox();
?>
</body>
</html>


