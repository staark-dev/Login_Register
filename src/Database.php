<?php
declare(strict_types=1);
namespace Staark\LoginRegister;

use PDO;
use PDOException;
use function count;

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
        } catch (PDOException $e) {
            echo "[ERROR]: " . $e->getMessage();
            exit;
        }

        return $this;
    }

    public static function getInstance() {
        if(is_null(self::$instance)) {
            self::$instance = new Database();
        }

        return self::$instance;
    }

    public function __invoke() {

    }
}