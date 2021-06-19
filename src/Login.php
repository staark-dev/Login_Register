<?php
declare(strict_types=1);

namespace Staark\LoginRegister;

use Staark\LoginRegister\Database as DB;
use PDO;

class Login {
    public static function sign_in() {
        //Fetch errors
        $errors = [];

        // Check email is valid
        $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);

        // Check password is a string
        $pass = filter_var($_POST['password'], FILTER_SANITIZE_STRING);

        // Check user remeber or false
        $remember = isset($_POST['remember']) ?? FALSE;

        // Query to database
        $sql = "SELECT email, name, password FROM accounts WHERE email = '$email' LIMIT 0,1";
        $session_sql = "DELETE FROM sessions WHERE email = '$email'";
        DB::getInstance()->dbh->query($session_sql);

        if($query = DB::getInstance()->dbh->prepare($sql)) {
            // Execute Query
            $query->execute();

            if($query->rowCount() > 0) {
                // Fetch Result
                $user = $query->fetch(PDO::FETCH_OBJ);

                // Check user password is match
                if(password_verify($pass, $user->password)) {
                    // Session Key for user
                    $session = @session_id();

                    // Set user for remember
                    if($remember != false) {
                        setcookie("remember", "1", time() + 60 * 60 * 24, "/");
                        setcookie("remember_email", $email, time() + 60 * 60 * 24, "/");

                        // Intert to database user session
                        DB::getInstance()->dbh->query("UPDATE accounts SET remember_me = true");
                        DB::getInstance()->dbh->query("INSERT INTO sessions(email, `key`, password, active) VALUES ('$user->email', '$session', '$user->password', 1)");
                    } else {
                        // Create database session for other pages
                        DB::getInstance()->dbh->query("UPDATE accounts SET remember_me = false");
                        DB::getInstance()->dbh->query("INSERT INTO sessions(email, `key`, password, active) VALUES ('$user->email', '$session', '$user->password', 0)");
                    }

                    // Set user sesion
                    $_SESSION['user']['id'] = $user->name;
                    $_SESSION['user']['email'] = $user->email;
                    $_SESSION['user']['login'] = true;

                    // Update in database last login time
                    DB::getInstance()->dbh->query("UPDATE accounts SET last_login = current_timestamp");

                    // Redirect succes login
                    header("Location: ./");
                    exit;
                } else {
                    $errors['password'] = "That password not match or is incorect";
                }
            } else {
                $errors['not_found'] = "User with that email not found.";
            }

            return $errors;
        }
    }

    public static function logout() {
        if(isset($_SESSION['user'])) {
            session_destroy();
            setcookie("remember", "", time() - 3600, "/");
            setcookie("remember_email", "", time() - 3600, "/");

            header("Location: ./");
            exit;
        }
    }

    public static function getSession() {
        if(isset($_SESSION['user'])) {
            return $_SESSION['user'];
        } else {
            return false;
        }
    }
}