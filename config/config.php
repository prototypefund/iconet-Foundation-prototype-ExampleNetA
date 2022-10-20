<?php
require_once __DIR__ . DIRECTORY_SEPARATOR . ".." . DIRECTORY_SEPARATOR . "autoloader.php";

ob_start(); //Turns on output buffering
session_start();
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

$config = [
    'db_user'           => 'netA',
    'db_password'       => '',
    'db_database'       => 'netA',
    'db_host'           => 'localhost',
    'db_ionet_user'     => 'netA',
    'db_iconet_password'=> '',
    'db_iconet_database'=> 'netAiconet',
    'db_iconet_host'    => 'localhost',
    'domain'            => "exampleneta.net",
    'timezone'          => date_default_timezone_set("Europe/Berlin")
];

//TODO there should be nothing below. Instead use the Database class

$con = mysqli_connect($config['db_host'],
    $config['db_user'],
    $config['db_password'],
    $config['db_database']);

if(mysqli_connect_errno()) 
{
	echo "Failed to connect: " . mysqli_connect_errno();
}

$icon = mysqli_connect($config['db_iconet_host'],
    $config['db_ionet_user'],
    $config['db_iconet_password'],
    $config['db_iconet_database']);
if(mysqli_connect_errno())
{
    echo "Failed to connect: " . mysqli_connect_errno();
}

?>