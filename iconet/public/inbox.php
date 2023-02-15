
<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta
            http-equiv="Content-Security-Policy"
            content=
            "default-src 'self';
            child-src 'none';
            connect-src *;
            style-src 'unsafe-inline';
            script-src 'self' 'unsafe-inline';
            img-src 'self' data: blob:;"/>
    <!--    FIXME does the parent need to have a laxer csp than its children? unsafe inline-->
    <title>Inbox</title>
    <script defer src='/iconet/public/inbox.js' type="module"></script>
    <style>
        embedded-experience {
            width: calc(100% - 4em);
            height: 15em;

            margin: 2em;
            padding: 0.5em;
            border: solid 1px;
            flex-direction: column;
            background: whitesmoke;
            overflow: auto;
            resize: both;
        }
    </style>
</head>
<body>

<?php
global $user;
$inboxController = new Iconet\IconetInbox($user);
?>

<h3>Your Open Inbox</h3>
<details>
    <summary>Raw Data</summary>
    <pre><?= json_encode($inboxController->inboxContents(), JSON_PRETTY_PRINT) ?></pre>
</details>


<?php
$inboxController->renderInbox() ?>


</body>
</html>


