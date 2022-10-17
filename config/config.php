<?php
ob_start(); //Turns on output buffering 
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
$timezone = date_default_timezone_set("Europe/London");

$con = mysqli_connect("localhost", "root", "", "social"); //Connection variable

if(mysqli_connect_errno()) 
{
	echo "Failed to connect: " . mysqli_connect_errno();
}

$icon = mysqli_connect("localhost", "root", "", "iconet"); //Connection variable
if(mysqli_connect_errno())
{
    echo "Failed to connect: " . mysqli_connect_errno();
}

$domain = "exampleneta.net";

?>