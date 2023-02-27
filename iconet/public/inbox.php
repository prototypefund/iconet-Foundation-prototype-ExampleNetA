
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


    <!-- <link rel="stylesheet" href="../../assets/css/font-awesome.min.css"> -->
    <link rel="stylesheet" type="../../text/css" href="assets/css/bootstrap.css">
    <link rel="stylesheet" type="../../text/css" href="assets/css/style.css">
    <!-- <link rel="stylesheet" href="../../assets/css/jquery.Jcrop.css" type="text/css"/> -->

    <style>
        @font-face {
            font-family: 'Bellota-LightItalic';
            src: url('../fonts/Bellota-LightItalic.otf');
        }

        @font-face {
            font-family: 'Bellota-BoldItalic';
            src: url('../fonts/Bellota-BoldItalic.otf');
        }

        embedded-experience {
            width: calc(100% - 4em);
            height: 35em;

            margin: 2em;
            padding: 0.5em;
            flex-direction: column;
            overflow: auto;
            resize: both;

            background-color: #fff;
            border: 1px solid #D3D3D3;
            border-radius: 5px;
            box-shadow: 2px 2px 1px #D3D3D3;

            color: #777;
            font-size: 80%;
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


