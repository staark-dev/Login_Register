<?php
namespace Staark\LoginRegister;
class Database {
    public $dbh = null;
    private $driver = "mysql:host=localhost;dbname=login_register;port=3307";

    public function __construct()
    {
        try {
            $this->dbh = new \PDO($this->driver, "root", "");

            $this->dbh->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
            $this->dbh->setAttribute(\PDO::ATTR_PERSISTENT, true);
            $this->dbh->setAttribute(\PDO::ATTR_EMULATE_PREPARES, false);

            return $this->dbh;
        } catch (\PDOException $e) {
            throw new \Exception("[ERROR]: " . $e->getMessage(), 1);
            exit;
        }
    }

    public function prepare_sql(string $sqlString = "", array $sqlParams = []) {
        if(!is_string($sqlString)) return;
        if(!is_array($sqlParams) || empty($sqlParams)) return;

        $safeQuery = $this->dbh->prepare($sqlString);
        $checkParam = null;

        if(is_array($sqlParams)) {
            foreach($sqlParams as $key => $val) {

                if(is_string($val)) $checkParam = \PDO::PARAM_STR;
                if(is_bool($val)) $checkParam = \PDO::PARAM_BOOL;
                if(is_int($val) || is_numeric($val)) $checkParam = \PDO::PARAM_INT;

                $safeQuery->bindValue($key, $val, $checkParam);
            }
        }

        try {
            $safeQuery->execute();

            return $safeQuery;
        } catch (\PDOException $e) {
            die($e->getMessage() . "<br /> <b>on line</b> " . __LINE__ . " class " . __CLASS__ . "<b> on function </b>" . __FUNCTION__);
        }
    }

    public function get($sqlQuery = null) {
        try {
            if($sqlQuery->rowCount() > 0) {
                $saveFetch = $sqlQuery->fetchAll(\PDO::FETCH_ASSOC);

                return $saveFetch;
            } else {
                echo "No records found " . $sqlQuery->rowCount();
            }
        } catch (\PDOException $e) {
            die($e->getMessage() . "<br /> <b>on line</b> " . __LINE__ . " class " . __CLASS__ . "<b> on function </b>" . __FUNCTION__);
        }
    }
}