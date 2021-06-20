<?php
namespace Staark\LoginRegister;

use Staark\LoginRegister\Database as DB;
use PDO;

class Login {
    public $errors = [];
    protected $db;
    protected $dataStored = [];

    public function __construct() {
        $this->errors = [];
        $this->dataStored = [];
        $this->db = DB::getInstance()->dbh;
    }

    protected function send($query = null) {
        try {
            $query->execute();
        } catch (\PDOException $e) {
            die($e->getMessage() . "<br /> <b>on line</b> " . __LINE__ . " class " . __CLASS__ . "<b> on function </b>" . __FUNCTION__);
        }
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

    public function login() {
        if( !is_array($this->dataStored) || empty($this->dataStored) ) {
            try {
                throw new \Exception('Error Processing Request Data Store', 1);
            } catch (\Exception $e) {
                die($e->getMessage() . "<br /> <b>on line</b> " . __LINE__ . " class " . __CLASS__ . "<b> on function </b>" . __FUNCTION__);
            }
        }


        // Check email is valid
        if(filter_var($this->dataStored['email'], FILTER_VALIDATE_EMAIL)) {
            $email = filter_var($this->dataStored['email'], FILTER_SANITIZE_EMAIL);
        } else {
            $this->errors['not_found'] = "Email was given not valid";
        }

        // Check password is a string
        $pass = htmlspecialchars($this->dataStored['password'], ENT_QUOTES, 'UTF-8');

        // Check user remeber or false
        $remember = isset($_data['remember']) ?? FALSE;

        /**
         * Is acctive sessions in database return to home page or delete curent session from database
         * @param email
         */
        $session_sql = "DELETE FROM sessions WHERE email = '$email'";
        $this->db->query($session_sql);

        /**
         * Select from users table curent user details
         * If match in database curent email, store fields from database
         * 
         * @param name
         * @param password
         * @param email
         *
         */
        $queryString = $this->db->prepare("SELECT email, name, password FROM accounts WHERE email = :email LIMIT 0,1");
        $queryString->bindParam(':email', $email, PDO::PARAM_STR, 128);

        /**
         * Execute the query and get the result
         * 
         */
        $this->send($queryString);
        
        /**
         * Check user exit or not found
         */
        if($queryString->rowCount() > 0) {
            // Fetch Result from user
            $user = $queryString->fetch(PDO::FETCH_OBJ);

            // Check user password is match
            if(password_verify($pass, $user->password)) {
                // Session Key for current user
                $session = @session_id();

                // Set user for remember
                if($remember != false) {
                    /**
                     * Insert in cookie user remember key
                     * Use that for login after close page or back after time
                     * 
                     * @param remember
                     * @return remember_keys
                     */
                    setcookie("remember", "1", time() + 60 * 60 * 24, "/");
                    setcookie("remember_email", $email, time() + 60 * 60 * 24, "/");

                    /**
                     * Insert in databse session user detalis
                     * Update user remember for current user
                     * 
                     * @param email
                     * @param session_id
                     * @param remember
                     * @return null
                     */
                    $queryRemember = $this->db->prepare("UPDATE accounts SET remember_me = true WHERE email = :email");
                    
                    /**
                     * Bind query statement in database
                     */
                    $queryRemember->bindParam(':email', $user->email, PDO::PARAM_STR, 128);

                    /**
                     * Execute the query statement.
                     */
                    $this->send($queryRemember);

                    /**
                     * Prepare query statement
                     */
                    $queryToken = $this->db->prepare("INSERT INTO sessions(email, `key`, password, active) VALUES (:email, :key, :password, 1)");
                    
                    /**
                     * Bind query statement in database
                     */
                    $queryToken->bindParam(':email', $user->email, PDO::PARAM_STR, 128);
                    $queryToken->bindParam(':key', $session, PDO::PARAM_STR, 32);
                    $queryToken->bindParam(':password', $user->password, PDO::PARAM_STR, 256);
                    //$queryToken->bindParam(':active', 1, PDO::PARAM_INT);

                    /**
                     * Execute the query statement.
                     */
                    $this->send($queryToken);
                } else {
                    /**
                     * Insert in databse session user detalis
                     * Update user remember for current user
                     * 
                     * @param email
                     * @param session_id
                     * @param remember
                     * @return null
                     */
                    $queryRemember = $this->db->prepare("UPDATE accounts SET remember_me = false WHERE email = :email");
                    
                    /**
                     * Bind query statement in database
                     */
                    $queryRemember->bindParam(':email', $user->email, PDO::PARAM_STR, 128);

                    /**
                     * Execute the query statement.
                     */
                    $this->send($queryRemember);

                    /**
                     * Prepare query statement
                     */
                    $queryToken = $this->db->prepare("INSERT INTO sessions(email, `key`, password, active) VALUES (:email, :key, :password, 0)");
                    
                    /**
                     * Bind query statement in database
                     */
                    $queryToken->bindParam(':email', $user->email, PDO::PARAM_STR, 128);
                    $queryToken->bindParam(':key', $session, PDO::PARAM_STR, 32);
                    $queryToken->bindParam(':password', $user->password, PDO::PARAM_STR, 256);
                    //$queryToken->bindParam(':active', 0, PDO::PARAM_INT);

                    /**
                     * Execute the query statement.
                     */
                    $this->send($queryToken);
                }

                /**
                 * Store user detalis in session
                 * 
                 * @param email
                 * @param name
                 * @param login
                 * @return user_session_keys
                 */
                $_SESSION['user']['id'] = $user->name;
                $_SESSION['user']['email'] = $user->email;
                $_SESSION['user']['login'] = true;

                /**
                 * After check it's ok, store in database user session login
                 * @param last_login
                 * @param email
                 */
                $loginQuery = $this->db->prepare("UPDATE accounts SET last_login = current_timestamp WHERE email = :email");
                
                /**
                 * Bind query statement in database
                 */
                $loginQuery->bindParam(':email', $user->email, PDO::PARAM_STR, 128);

                /**
                 * Execute the query statement.
                 */
                $this->send($loginQuery);

                // Redirect succes login
                header("Location: ./?dashboard");
                exit;
            } else {
                $this->errors['password'] = "That password not match or is incorect";
            }
        } else {
            $this->errors['not_found'] = "User with that email not found.";
        }
        
        return $this->errors ?? [];
    }

    public function logout() {
        if(!isset($_SESSION['user'])) {
            return;
        }

        /**
         * Is acctive sessions in database return to home page or delete curent session from database
         * @param string email
         */
        // God
        $sessionQuery = $this->db->prepare("DELETE FROM sessions WHERE email = :email");
        $sessionQuery->bindParam(':email', $_SESSION['user']['email'], PDO::PARAM_STR, 128);
        $sessionQuery->execute();

        /**
         * Destroy current user session.
         */
        session_destroy();

        /**
         * Remove remember cookie set.
         */
        setcookie("remember", "", time() - 3600, "/");
        setcookie("remember_email", "", time() - 3600, "/");

        /**
         * Redirect to homepage.
         */
        header("Location: ./");
        exit;
    }

    public function remember() {
        if(!isset($_COOKIE['remember'])) {
            return;
        }

        return $_COOKIE['remember'];
    }

    /**
     * Get keys from current user session
     * 
     * @param string key
     * @return string
     */
    public function user(string $key = NULL) {
        if(!isset($_SESSION['user'])) {
            return false;
        }

        return $_SESSION['user'][$key];
    }
}