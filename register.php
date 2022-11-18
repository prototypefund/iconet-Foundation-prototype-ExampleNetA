<?php

require_once 'config/config.php';
require_once 'includes/form_handlers/register_handler.php';
require_once 'includes/form_handlers/login_handler.php';
?>

<!doctype html>
<html lang="en">
<head>
    <title>Welcome to ExampleNetA</title>
    <link rel="stylesheet" type="text/css" href="assets/css/register_style.css">
    <script src="assets/js/jquery.min.js"></script>
    <script src="assets/js/register.js"></script>
</head>
<body>

<?php

if(isset($_POST['register_button'])) {
    echo '
		<script>

		$(document).ready(function() {
			$("#first").hide();
			$("#second").show();
		});

		</script>

		';
}


?>

<div class="wrapper">

    <div class="login_box">

        <div class="login_header">
            <h1>ExampleNetA</h1>
            Login or sign up below!
        </div>
        <br>
        <div id="first">
            <div style="background-color: yellow; text-align: center">
                <?php
                foreach($error_array as $error) {
                    echo $error;
                }
                ?>
            </div>
            <form action="register.php" method="POST">
                <input type="email" name="log_email" placeholder="Email Address" value="<?php
                if(isset($_SESSION['log_email'])) {
                    echo $_SESSION['log_email'];
                }
                ?>" required>
                <br>
                <input type="password" name="log_password" placeholder="Password">
                <br>
                <input type="submit" name="login_button" value="Login">
                <br>
                <a href="#" id="signup" class="signup">New user? Register here!</a>
            </form>
        </div>


        <div id="second">
            <div style="background-color: yellow; text-align: center">
                <?php
                foreach($error_array as $error) {
                    echo $error;
                }
                ?>
            </div>

            <form action="register.php" method="POST">
                <input type="text" name="reg_fname" placeholder="First Name" value="<?php
                if(isset($_SESSION['reg_fname'])) {
                    echo $_SESSION['reg_fname'];
                }
                ?>" required>


                <input type="text" name="reg_lname" placeholder="Last Name" value="<?php
                if(isset($_SESSION['reg_lname'])) {
                    echo $_SESSION['reg_lname'];
                }
                ?>" required>
                <br>


                <input type="email" name="reg_email" placeholder="Email" value="<?php
                if(isset($_SESSION['reg_email'])) {
                    echo $_SESSION['reg_email'];
                }
                ?>" required>
                <br>

                <input type="email" name="reg_email2" placeholder="Confirm Email" value="<?php
                if(isset($_SESSION['reg_email2'])) {
                    echo $_SESSION['reg_email2'];
                }
                ?>" required>


                <input type="password" name="reg_password" placeholder="Password" required>
                <br>
                <input type="password" name="reg_password2" placeholder="Confirm Password" required>
                <br>
                <input type="submit" name="register_button" value="Register">
                <br>
                <a href="#" id="signin" class="signin">Already have an account? Sign in here!</a>
            </form>
        </div>

    </div>

</div>


</body>
</html>