<?php

use Iconet\Crypto;

require_once "iconet/Database.php";
require_once "iconet/Crypto.php";


//Declaring variables to prevent errors
$fname = ""; //First name
$lname = ""; //Last name
$email = ""; //email
$email2 = ""; //email 2
$password = ""; //password
$password2 = ""; //password 2
$date = ""; //Sign up date 
$error_array = array(); //Holds error messages

if(isset($_POST['register_button'])) {
    //Registration form values

    //First name
    $fname = strip_tags($_POST['reg_fname']); //Remove html tags
    $fname = str_replace(' ', '', $fname); //remove spaces
    $fname = ucfirst(strtolower($fname)); //Uppercase first letter
    $_SESSION['reg_fname'] = $fname; //Stores first name into session variable

    //Last name
    $lname = strip_tags($_POST['reg_lname']); //Remove html tags
    $lname = str_replace(' ', '', $lname); //remove spaces
    $lname = ucfirst(strtolower($lname)); //Uppercase first letter
    $_SESSION['reg_lname'] = $lname; //Stores last name into session variable

    //email
    $email = strip_tags($_POST['reg_email']); //Remove html tags
    $email = str_replace(' ', '', $email); //remove spaces
    $email = ucfirst(strtolower($email)); //Uppercase first letter
    $_SESSION['reg_email'] = $email; //Stores email into session variable

    //email 2
    $email2 = strip_tags($_POST['reg_email2']); //Remove html tags
    $email2 = str_replace(' ', '', $email2); //remove spaces
    $email2 = ucfirst(strtolower($email2)); //Uppercase first letter
    $_SESSION['reg_email2'] = $email2; //Stores email2 into session variable

    //Password
    $password = strip_tags($_POST['reg_password']); //Remove html tags
    $password2 = strip_tags($_POST['reg_password2']); //Remove html tags

    $date = date("Y-m-d"); //Current date

    if($email == $email2) {
        //Check if email is in valid format
        if(filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $email = filter_var($email, FILTER_VALIDATE_EMAIL);

            //Check if email already exists
            $e_check = mysqli_query($con, "SELECT email FROM users WHERE email='$email'");

            //Count the number of rows returned
            $num_rows = mysqli_num_rows($e_check);

            if($num_rows > 0) {
                array_push($error_array, "Email already in use<br>");
            }
        } else {
            array_push($error_array, "Invalid email format<br>");
        }
    } else {
        array_push($error_array, "Emails don't match<br>");
    }


    if(strlen($fname) > 25 || strlen($fname) < 2) {
        array_push($error_array, "Your first name must be between 2 and 25 characters<br>");
    }

    if(strlen($lname) > 25 || strlen($lname) < 2) {
        array_push($error_array, "Your last name must be between 2 and 25 characters<br>");
    }

    if($password != $password2) {
        array_push($error_array, "Your passwords do not match<br>");
    } else {
        if(preg_match('/[^A-Za-z0-9]/', $password)) {
            array_push($error_array, "Your password can only contain english characters or numbers<br>");
        }
    }

    if(strlen($password) > 30 || strlen($password) < 5) {
        array_push($error_array, "Your password must be betwen 5 and 30 characters<br>");
    }


    if(empty($error_array)) {
        $password = md5($password); //Encrypt password before sending to database

        //Generate username by concatenating first name and last name
        $username = strtolower($fname . "_" . $lname);
        $check_username_query = mysqli_query($con, "SELECT username FROM users WHERE username='$username'");


        $i = 0;
        //if username exists add number to username
        while(mysqli_num_rows($check_username_query) != 0) {
            $i++; //Add 1 to i
            $username = $username . "_" . $i;
            $check_username_query = mysqli_query($con, "SELECT username FROM users WHERE username='$username'");
        }

        //Generate global address by concatenating username and global URL
        $address = $username . "@" . $config['domain'];

        //Profile picture assignment
        $rand = rand(1, 2); //Random number between 1 and 2
        $profile_pic = "assets/images/profile_pics/defaults/head_deep_blue.png";
        if($rand == 2) {
            $profile_pic = "assets/images/profile_pics/defaults/head_emerald.png";
        }

        Database::singleton()->registerUser($fname, $lname, $username, $email, $password, $date, $profile_pic);

        global $iconetDB;
        $cryp = new Crypto();
        $keyPair = $cryp->genkeyPair();
        $iconetDB->add_user($username, $address, $keyPair[0], $keyPair[1]);

        array_push($error_array, "<span style='color: #14C800;'>You're all set! Go ahead and login!</span><br>");

        //Clear session variables
        $_SESSION['reg_fname'] = "";
        $_SESSION['reg_lname'] = "";
        $_SESSION['reg_email'] = "";
        $_SESSION['reg_email2'] = "";
    }
}
?>