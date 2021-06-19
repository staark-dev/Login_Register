<?php
namespace Staark\LoginRegister;

use Staark\LoginRegister\Database as DB;
use PDO;

class Login {
    public $errors = [];
    protected $db;

    public function __construct() {
        $this->errors = [];
        $this->db = DB::getInstance()->dbh;
    }

    public function login(array $_data = []) {
        /**
         * If $_data is not array or is empty return function.
         * @param _data
         */
        if(!$_data || empty($_data)) {
            throw new \Exception('Invalid data post given.');
        }

        /**
         * If $_POST is empty or null return function
         * @param POST
         */
        if(!isset($_POST)) {
            throw new \Exception('$_POST given null data.');
        }

        // Check email is valid
        $email = filter_var($_data['email'], FILTER_SANITIZE_EMAIL);

        // Check password is a string
        $pass = filter_var($_data['password'], FILTER_SANITIZE_STRING);

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
        if (!$queryString->execute()) {
            throw new \Exception("Error Processing Request", 1);
            exit;
        }
        
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
                    $queryRemember->execute();

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
                    $queryToken->execute();
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
                    $queryRemember->execute();

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
                    $queryToken->execute();
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
                $loginQuery->execute();

                // Redirect succes login
                header("Location: ./");
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

        // BAD
        //$session_sql = "DELETE FROM sessions WHERE email = " . $_SESSION['user']['email'];
        //$this->db->query($session_sql);

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

    public function user() {
        if(!isset($_SESSION['user'])) {
            return;
            //throw new \Exception('Global variable SESSION for key user not set or is empty.');
        }

        return $_SESSION['user'];
    }
}