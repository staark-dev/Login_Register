<?php
declare(strict_types=1);

namespace Staark\LoginRegister;

use Staark\LoginRegister\Database as DB;
use Staark\LoginRegister\Login;

class Register {
    
    private static function validate($_data = []) {
        // After check string return xss clean
        $dataStored = [];

        // After check string return errors
        $errors = [];

        // Check username is ok and no xss injection
        if( is_string($_data['user']) && !empty($_data['user']) ) {
            $dataStored['user'] = filter_var($_data['user'], FILTER_SANITIZE_STRING);
        }

        // Check email is ok and no xss injection
        if( is_string($_data['email']) && !empty($_data['email']) ) {
            $dataStored['email'] = filter_var($_data['email'], FILTER_VALIDATE_EMAIL);
        } else {
            $errors['email'] = "That email is not valid, please check your email !";
        }

        // Check password is ok and no xss injection
        if( is_string($_data['password']) && !empty($_data['password']) ) {
            $dataStored['password'] = filter_var($_data['password'], FILTER_SANITIZE_SPECIAL_CHARS);
            $dataStored['password'] = filter_var($_data['password'], FILTER_SANITIZE_NUMBER_INT);
            $dataStored['pass'] = password_hash($dataStored['password'], PASSWORD_DEFAULT, ['cost' => 12]);
        }

        // Check password is ok and no xss injection
        if( is_string($_data['confirm-password']) && !empty($_data['confirm-password'])) {
            $dataStored['confirm-password'] = filter_var($_data['confirm-password'], FILTER_SANITIZE_SPECIAL_CHARS);
            $dataStored['confirm-password'] = filter_var($_data['confirm-password'], FILTER_SANITIZE_NUMBER_INT);
        }
        
        if($_data['password'] != $_data['confirm-password']) {
            $errors['confirm-password'] = "(!) Confirm Password not match with password<br>";
        }

        if( isset($_data['terms']) && !empty($_data['terms']) ) {
            $dataStored['terms'] = true;
        } else {
            $errors['terms'] = "(!) Confirm Accept Terms and Conditions";
        }

        return ['data' => (object) $dataStored, 'error' => $errors];
    }

    public static function create($_data = []) {
        // Store errors
        $error = [];

        if(!empty($_data)) {
            if($valid = self::validate($_data)) {
                if(empty($valid['error'])) {
                    $_SESSION['register']['user'] = $valid['data']->user;
                    $_SESSION['register']['email'] = $valid['data']->email;
                    $sql = "INSERT INTO accounts(name, email, password) VALUES ('{$valid['data']->user}', '{$valid['data']->email}', '{$valid['data']->pass}')";
                    
                    if($query = DB::getInstance()->dbh->prepare($sql)) {
                        // Execute the query
                        $query->execute();

                        echo "Thanks for registring your user id is: " . DB::getInstance()->dbh->lastInsertId();
                        $_SESSION['register']['user'] = "";
                        $_SESSION['register']['email'] = "";
                        exit;
                    }
                } else {
                    $_SESSION['register']['user'] = $valid['data']->user;
                    $_SESSION['register']['email'] = $valid['data']->email;
                    $error['errors'] = $valid['error'];
                }
            }
        } else {
            $error['empty'] = "Nothing to read or send";
        }

        return $error;
    }
}