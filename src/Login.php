<?php
namespace Staark\LoginRegister;

use Staark\LoginRegister\Database;

class Login extends Database {
    public $errors = [];
    protected $dataStored = [];

    // Import connection driver from Database Class
    public $dbh;

    public function __construct() {
        $this->dbh = parent::getInstance();
        $this->errors = [];
        $this->dataStored = [];
    }

    protected function token(int $lenght = 0) {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        return substr(str_shuffle($characters), 0, $lenght);
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
     * Check user in database for get informations give
     * Login user and create an session
     */
    public function login(): array
    {
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

        // Check user remember or false
        $remember = isset($this->dataStored['remember']) ?? false;

        /**
         * Select from users table curent user details
         * If match in database curent email, store fields from database
         * 
         * @param string $email
         *
         */
        $queryString = $this->dbh->prepare_sql("SELECT `token`, `email`, `name`, `password` FROM accounts WHERE `email` = :email LIMIT 0,1", [
            ':email' => $email
        ]);

        /**
         * Check user exit or not found
         */
        if($queryString->rowCount() > 0) {
            // Fetch Result from user
            $user = $queryString->fetch(\PDO::FETCH_OBJ);

            // Check user active session.
            $getSession = $this->dbh->prepare_sql("SELECT * FROM sessions WHERE `token` = :token", [
                ':token' => $user->token
            ]);

            // Check user password is match
            if(password_verify($pass, $user->password)) {
                // Session Key for current user
                $session = @session_id();
                $tokenKey = $this->token(32);

                // Set user for remember
                if($remember != false) {
                    /**
                     * Insert in cookie user remember key
                     * Use that for login after close page or back after time
                     * 
                     * @param string remember
                     * @return string remember_keys
                     */
                    setcookie("remember", 1, time() + 60 * 60 * 24, "/");
                    setcookie("remember_email", $user->email, time() + 60 * 60 * 24, "/");

                    /**
                     * Insert in databse session user detalis
                     * Update user remember for current user
                     * 
                     * @param string email
                     */
                    $this->dbh->prepare_sql("UPDATE accounts SET `remember_me` = 1, `token` = :token WHERE email = :email", [
                        ':token' => $tokenKey,
                        ':email' => $user->email
                    ]);

                    /**
                     * Prepare query statement and sending
                     * Insert a new session to database
                     * 
                     * @param string email
                     * @param string key
                     * @param string password
                     */
                    if($getSession->rowCount() == 0) {
                        $this->dbh->prepare_sql("INSERT INTO sessions(`token`, `email`, `key`, `password`, `active`) VALUES (:token, :email, :key, :password, 1)", [
                            ':token'    => $tokenKey,
                            ':email'    => $user->email,
                            ':key'      => $session,
                            ':password' => $user->password
                        ]);
                    } else {
                        $this->dbh->prepare_sql("UPDATE sessions SET `token` = :token WHERE `email` = :email", [
                            'token' => $tokenKey,
                            'email' => $user->email
                        ]);
                    }
                } else {
                    /**
                     * Insert in database session user details
                     * Update user remember for current user
                     * 
                     * @param string email
                     */
                    $this->dbh->prepare_sql("UPDATE accounts SET `remember_me` = 0, `token` = :token WHERE email = :email", [
                        ':token' => $tokenKey,
                        ':email' => $user->email
                    ]);

                    /**
                     * Prepare query statement and sending
                     * Insert a new session to database
                     * 
                     * @param string email
                     * @param string key
                     * @param string password
                     */
                    if($getSession->rowCount() == 0) {
                        $this->dbh->prepare_sql("INSERT INTO sessions(`token`, `email`, `key`, `password`, `active`) VALUES (:token, :email, :key, :password, 0)", [
                            ':token'    => $tokenKey,
                            ':email'    => $user->email,
                            ':key'      => $session,
                            ':password' => $user->password
                        ]);
                    } else{
                        $this->dbh->prepare_sql("UPDATE sessions SET `token` = :token WHERE `email` = :email", [
                            'token' => $tokenKey,
                            'email' => $user->email
                        ]);
                    }
                }

                /**
                 * Store user detalis in session
                 * 
                 * @param string email
                 * @param string name
                 * @param string login
                 * @return mixed
                 */
                $_SESSION['user']['id'] = $user->name;
                $_SESSION['user']['email'] = $user->email;
                $_SESSION['user']['login'] = true;

                /**
                 * After check it's ok, store in database user session login
                 * 
                 * @param string last_login
                 * @param string email
                 */

                $this->dbh->prepare_sql("UPDATE accounts SET last_login = current_timestamp WHERE email = :email", [
                    ':email' => $user->email
                ]);

                // Redirect success login
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

    /**
     * After user has logged that remove active session
     */
    public function logout() {
        if(!isset($_SESSION['user'])) {
            return;
        }

        /**
         * Is acctive sessions in database return to home page or delete curent session from database
         * @param string email
         */
        
        $sessionQuery = $this->dbh->prepare_sql("DELETE FROM sessions WHERE email = :email", [
            ':email' => $_SESSION['user']['email']
        ]);

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

    public function remember(): bool
    {
        if(!isset($_COOKIE)) {
            return false;
        }

        $getSession = $this->dbh->prepare_sql("SELECT * FROM sessions WHERE `email` = :email", [
            ':email' => $_COOKIE['remember_email']
        ]);

        if($getSession->rowCount() > 0) {
            header("Location: ./?dashboard");
            exit;
        } else {
            header("Location: ./");
            exit;
        }
    }

    /**
     * Get keys from current user session
     *
     * @param string|null $key
     * @return mixed
     */
    public function user(string $key = NULL) {
        if(!isset($_SESSION)) {
            return null;
        }

        if(isset($key) && isset($_SESSION['user'][$key]))
            return $_SESSION['user'][$key];
        else
            return null;
    }
}