<?php
declare(strict_types=1);

require_once __DIR__ . "/../vendor/autoload.php";

ob_start(); //Turns on output buffering
session_start();
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

//Try loading .env, if it does not exist, use .env.default
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . "/..", ['.env', '.env.default']);
$dotenv->load();

date_default_timezone_set($_ENV['TIMEZONE']);

//TODO there should be nothing below. Instead use the Database class

$con = mysqli_connect(
    $_ENV['DB_HOST'],
    $_ENV['DB_USER'],
    $_ENV['DB_PASSWORD'],
    $_ENV['DB_DATABASE']
);

if(mysqli_connect_errno()) {
    echo "Failed to connect: " . mysqli_connect_errno();
}

?>