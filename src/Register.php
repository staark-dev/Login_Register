<?php
namespace Staark\LoginRegister;

use Staark\LoginRegister\Login;
class Register extends Database {
    protected $dataStored = [];
    public $errors = [];

    // Import connection driver from Database Class
    //public $dbh;

    public function __construct() {
        parent::__construct();
        
        $this->dataStored = [];
        $this->errors = [];
    }

    /**
     * Store all _POST keys to new array variable
     * After get fields form transaction to store
     */
    public function store(array $_data = array()) {
        // Not give an array return function
        if( !is_array($_data) || empty($_data) ) {
            try {
                throw new \Exception('Error Processing Request Data Store', 1);
            } catch (\Exception $e) {
                die($e->getMessage() . "<br /> <b>on line</b> " . __LINE__ . " class " . __CLASS__ . "<b> on function </b>" . __FUNCTION__);
            }
        }

        /**
         * Associate must validate the request
         * @param string key
         * @param string value
         */
        foreach($_data as $key => $value) {
            $this->dataStored[$key] = $value;
        }
    }
    
    /**
     * Validate Password Strength
     * Password must be at least 8 characters in length.
     * Password must include at least one upper case letter.
     * Password must include at least one number.
     * Password must include at least one special character.
     */
    private function password_strength(string $password = NULL) {
        if(is_null($password)) return;

        // Validate password strength
        $uppercase = preg_match('@[A-Z]@', $password);
        $lowercase = preg_match('@[a-z]@', $password);
        $number    = preg_match('@[0-9]@', $password);
        $specialChars = preg_match('@[^\w]@', $password);

        if(!$uppercase || !$lowercase || !$number || !$specialChars || strlen($password) < 8) {
            return true;
        }

        //return password_hash($password, PASSWORD_DEFAULT, ['cost' => 12]);
    }

    /**
     * Validate all fields from store function
     */
    public function validate() {
        // Not give an array return function
        if( !$this->dataStored || !is_array($this->dataStored) || empty($this->dataStored) ) {
            try {
                throw new \Exception('Error Processing Request Data Validation', 1);
            } catch (\Exception $e) {
                die($e->getMessage() . "<br /> <b>on line</b> " . __LINE__ . " class " . __CLASS__ . "<b> on function </b>" . __FUNCTION__);
            }
        }
        
        // Validate user is string
        if(!empty($this->dataStored['user']) && !is_string($this->dataStored['user'])) {
            $this->errors['username'] = "(!) User should be at string.";
        }

        // Validate email is a valid email
        if(!empty($this->dataStored['email']) && !filter_var($this->dataStored['email'], FILTER_VALIDATE_EMAIL)) {
            $this->errors['email'] = "(!) Email should be at valid email.";
        }

        // Validate password is string and is Strength
        if(!empty($this->dataStored['password']) && $this->password_strength($this->dataStored['password'])) {
            $this->errors['password'] = "(!) Password should be at least 8 characters in length and should include at least one upper case letter, one number, and one special character.";
        }

        // Validate confirm password is match with password
        if( $this->dataStored['confirm-password'] != $this->dataStored['password']) {
            $this->errors['password'] = "(!) Confirm Password should be at Password";
        }

        // Check passed term and privacy
        if(!isset($this->dataStored['terms'])) {
            $this->errors['email'] = "(!) Terms and Privacy should be at confirmed.";
        }

        // Validate the VALUES
        $this->dataStored['user']               = filter_var($this->dataStored['user'], FILTER_SANITIZE_STRING);
        $this->dataStored['email']              = filter_var($this->dataStored['email'], FILTER_SANITIZE_EMAIL);
        $this->dataStored['password']           = htmlspecialchars($this->dataStored['password'], ENT_QUOTES, 'UTF-8');
        $this->dataStored['confirm-password']   = htmlspecialchars($this->dataStored['confirm-password'], ENT_QUOTES, 'UTF-8');

        return ($this->errors) ? $this->errors : true;
    }

    /**
     * Create user to datbase
     */
    public function create() {
        if( !$this->dataStored || !is_array($this->dataStored) || empty($this->dataStored) ) {
            try {
                throw new \Exception('Error Processing Request Data', 1);
            } catch (\Exception $e) {
                die($e->getMessage() . "<br /> <b>on line</b> " . __LINE__ . " class " . __CLASS__ . "<b> on function </b>" . __FUNCTION__);
            }
        }

        /**
         * After validate the string and data keys given in the form
         * Insert to database new user
         * 
         * @param string name
         * @param string email
         * @param string password
         */

        if($this->validate()) {
            /**
             * Hashing password for more secure passwords
             * Algo SHA256
             * 
             * @param string password
             * @param string hash
             * 
             * @return string hash
             */
            $this->dataStored['password'] = password_hash($this->dataStored['password'], PASSWORD_DEFAULT, [
                'cost' => 12
            ]);

            /**
             * Set on that form active session for store email and name
             * After successfull validation remove that session keys
             * 
             * @param string name
             * @param string email
             */
            $_SESSION['register']['user'] = $this->dataStored['user'];
            $_SESSION['register']['email'] = $this->dataStored['email'];

            /**
             * Prepare statement for registration
             */
            $this->prepare_sql("INSERT INTO accounts(name, email, password) VALUES (:name, :email, :pass)", [
                ':name'     => $this->dataStored['user'],
                ':email'    => $this->dataStored['email'],
                ':pass'     => $this->dataStored['password']
            ]);
            
            /**
             * After validation is successfull prepare statement for submitting
             * No valid data to send return error info
             */
            $_SESSION['register']['user'] = "";
            $_SESSION['register']['email'] = "";

            header("Location: ./?success");
            exit;
        }
    }
}