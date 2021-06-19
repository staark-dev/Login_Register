<?php
namespace Staark\LoginRegister;

use PDO;
use PDOException;

class Database {
    public $dbh;
    public static $instance;
    private $driver = "mysql:host=localhost;dbname=login_register;port=3307";

    public function __construct()
    {
        try {
            $this->dbh = new PDO($this->driver, "root", "");

            $this->dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->dbh->setAttribute(PDO::ATTR_PERSISTENT, true);
            $this->dbh->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);

            return $this->dbh;
        } catch (PDOException $e) {
            throw new \Exception("[ERROR]: " . $e->getMessage(), 1);
            exit;
        }
    }

    public static function getInstance() {
        if(is_null(self::$instance)) {
            self::$instance = new Database();
        }

        return self::$instance;
    }
}