<?php

require_once __DIR__ . "/../vendor/autoload.php";

ob_start(); //Turns on output buffering
session_start();
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

$config = [
    'db_user' => 'admin',
    'db_password' => '',
    'db_database' => 'netA',
    'db_host' => '127.0.0.1',

    'db_iconet_user' => 'admin',
    'db_iconet_password' => '',
    'db_iconet_database' => 'netAiconet',
    'db_iconet_host' => '127.0.0.1',

    'domain' => "exampleneta.net",
    'timezone' => date_default_timezone_set("Europe/Berlin"),
    'storage'   => "./iconet/posts/id.",
    'openssl' => 'C:/xampp/php/extras/openssl/openssl.cnf',

];

//TODO there should be nothing below. Instead use the Database class

$con = mysqli_connect(
    $config['db_host'],
    $config['db_user'],
    $config['db_password'],
    $config['db_database']
);

if(mysqli_connect_errno()) {
    echo "Failed to connect: " . mysqli_connect_errno();
}

$iconetDB = new \Iconet\Database();

?>