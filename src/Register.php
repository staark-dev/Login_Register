<?php
namespace Staark\LoginRegister;

use Staark\LoginRegister\Database as DB;
use Staark\LoginRegister\Login;
use Throwable;

class Register {
    protected $dataStored = [];
    protected $db;
    public $errors = [];

    public function __construct() {
        $this->dataStored = [];
        $this->errors = [];
        $this->db = DB::getInstance()->dbh;
    }

    private function validate(array $_data = []) {
        // Not give an array return function
        if( !$_data || !is_array($_data) ) {
            throw new \Exception('Error Processing Request', 1);
        }

        // Check username is ok and no xss injection
        if( !empty($_data['user']) && is_string($_data['user']) ) {
            $this->dataStored['user'] = filter_var($_data['user'], FILTER_SANITIZE_STRING);
        }

        // Check email is ok and no xss injection
        if(!empty($_data['email']) && filter_var($_data['email'], FILTER_VALIDATE_EMAIL) && is_string($_data['email']) ) {
            $this->dataStored['email'] = filter_var($_data['email'], FILTER_VALIDATE_EMAIL);
        } else {
            $this->errors['email'] = "That email is not valid, please check your email !";
        }
        
        
        // Check password is ok and no xss injection
        if( !empty($_data['password']) && is_string($_data['password']) ) {
            $this->dataStored['password'] = htmlspecialchars($_data['password'], ENT_QUOTES | ENT_HTML401, 'UTF-8');
            $this->dataStored['pass'] = password_hash($this->dataStored['password'], PASSWORD_DEFAULT, ['cost' => 12]);
        }

        // Check password is ok and no xss injection
        if( !empty($_data['confirm-password']) && is_string($_data['confirm-password']) ) {
            $this->dataStored['confirm-password'] = htmlspecialchars($_data['confirm-password'], ENT_QUOTES | ENT_HTML401, 'UTF-8');
        }
        
        if($_data['password'] != $_data['confirm-password']) {
            $this->errors['confirm-password'] = "(!) Confirm Password not match with password<br>";
        }

        if( !isset($_data['terms']) ) {
            $this->errors['terms'] = "(!) Confirm Accept Terms and Conditions";
        }

        $this->dataStored['terms'] = true;

        return [
            'data' => (object) $this->dataStored,
            'error' => $this->errors
        ];
    }

    public function create($_data = []) {
        if( !$_data || !is_array($_data) ) {
            throw new \Exception('Error Processing Request', 1);
        }

        /**
         * After validate the string and data keys given in the form
         * Insert to database new user
         * 
         * @param string name
         * @param string email
         * @param string password
         */
        if($valid = $this->validate($_data)) {

            if(!empty($valid['error'])) {
                $_SESSION['register']['user'] = $valid['data']->user;
                $_SESSION['register']['email'] = $valid['data']->email;
                $this->errors['errors'] = $valid['error'];
            }

            /**
             * Set on that form active session for store email and name
             * After successfull validation remove that session keys
             * 
             * @param string name
             * @param string email
             */
            $_SESSION['register']['user'] = $valid['data']->user;
            $_SESSION['register']['email'] = $valid['data']->email;

            /**
             * Prepare statement for registration
             */
            $queryString = $this->db->prepare("INSERT INTO accounts(name, email, password) VALUES (:name, :email, :pass)");
            
            /**
             * Binding validation is ok and no xss injection
             */
            $queryString->bindParam(':name', $valid['data']->user, \PDO::PARAM_STR, 64);
            $queryString->bindParam(':email', $valid['data']->email, \PDO::PARAM_STR, 128);
            $queryString->bindParam(':pass', $valid['data']->pass, \PDO::PARAM_STR, 256);

            /**
             * After validation is successfull prepare statement for submitting
             */
            if($query = $queryString->execute()) {
                $_SESSION['register']['user'] = "";
                $_SESSION['register']['email'] = "";

                header("Location: ./");
                exit;
            } else {
                throw new \Exception("Error Processing Request", 1);
                exit;
            }
        }

        return $this->errors ?? [];
    }
}