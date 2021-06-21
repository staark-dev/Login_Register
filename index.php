<?php
@session_start();
require_once 'vendor/autoload.php';

use Staark\LoginRegister\Login;
use Staark\LoginRegister\Register;

$login = new Login();
$register = new Register();

if(isset($_GET['login']) && isset($_POST['login'])) {
    // Check session state
    //if(!$login->remember()) {
        // your functions for get active session of current users
    //}

    /**
     * Store $_POST request variable
     */
    $login->store($_POST);

    /**
     * After store $_POST request variable and validate it, create account.
     */
    $login->login();
}

if(isset($_GET['register']) && isset($_POST['submit'])) {
    /**
     * Store $_POST request variable
     */
    $register->store($_POST);

    /**
     * After store $_POST request variable and validate it, create account.
     */
    $register->create();
}

if(isset($_GET['logout'])) {
    $login->logout();
}

if(isset($_GET['dashboard'])) {
    $login->remember();
}
?>

<!DOCTYPE HTML>
<html lang="en">
    <head> 
        <title>PHP - Login / Register</title>
        <link rel="stylesheet" href="style.css">
    </head>
    <body>
        <?php if($login->user('login')): ?>
            <div class="div user">
                <h5>
                    Welcome back <?=$login->user('id'); ?>
                    <a href="?logout">Logout</a>
                </h5>
                
                <p>Username: <?=$login->user('id'); ?></p>
                <p>Email: <?=$login->user('email'); ?></p>
            </div>
        <?php else: ?>
        <form action="?login" method="post" enctype="multipart/form">
            <?php if(isset($getLogin)): ?>
            <div class="errors">
                <?=($getLogin['not_found']) ?? ""; ?>
                <?=($getLogin['password']) ?? ""; ?>
            </div>
            <?php endif; ?>

            <label for="">Email</label>
            <input type="email" name="email" id="" placeholder="Enter Email" required />
            <br />
            <label for="">Password</label>
            <input type="password" name="password" id="" placeholder="*********" required />
            <br />
            <br />
            <label id="checkbox" for="checkbox">Remember Me</label>
            <input type="checkbox" name="remember" id="" />

            <br>
            <input type="submit" value="Login" name="login" />
        </form>

        <form action="?register" method="post" enctype="multipart/form">
            <?php if(isset($getErrors) && !empty($getErrors)): ?>
            <div class="errors">
                <?=($getErrors['empty']) ?? ""; ?>
                <?=($getErrors['errors']['confirm-password']) ?? ""; ?>
                <?=($getErrors['errors']['terms']) ?? ""; ?>
            </div>
            <?php endif; ?>
            <label for="">Username</label>
            <input type="text" name="user" value="<?php echo $_SESSION['register']['user'] ?? "";?>" placeholder="Enter Username" required />
            <br />
            <label for="">Email</label>
            <input type="email" name="email" value="<?php echo $_SESSION['register']['email'] ?? "";?>" placeholder="Enter Email" required />
            <br />
            <label for="">Password</label>
            <input type="password" name="password" placeholder="*********" required />
            <br />
            <label for="">Confirm Password</label>
            <input type="password" name="confirm-password" placeholder="*********" required />
            <br />
            <label id="checkbox" for="checkbox">Accept Terms and Conditions</label>
            <input type="checkbox" name="terms" />

            <br>
            <input type="submit" value="Register" name="submit" />
        </form>
        <?php endif; ?>
    </body>
</html>