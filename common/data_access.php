<?php

class DataAccessManagerFactory {

    private static $instance;

    public static function getInstance() {
        global $dbhost, $dbuser, $dbpass, $dbname;
        if(self::$instance == NULL) {
            self::$instance = new DataAccessManager($dbhost, $dbuser, $dbpass, $dbname);
        }
        return self::$instance;
    }

}

class DataAccessManager {

    private $connection;
    private $transactionStarted = false;

    /**
     * DataAccessManager constructor.
     *
     * @param $dbhost
     * @param $dbuser
     * @param $dbpass
     * @param $dbname
     */
    public function __construct($dbhost, $dbuser, $dbpass, $dbname)
    {
        // Connect to the database.
        $this->connection = new mysqli($dbhost, $dbuser, $dbpass, $dbname);
    }

    function __destruct() {
        if($this->transactionStarted) {
            $this->rollbackTransaction();
        }
        $this->connection->close();
    }

    public function isInTransaction() {
        return  $this->transactionStarted;
    }


    public function startTransaction() {
        $this->executeStatement("SET AUTOCOMMIT=0");
        $this->executeStatement("BEGIN");
        $this->transactionStarted = true;
    }

    public function commitTransaction() {
        $this->executeStatement("COMMIT");
        $this->transactionStarted = false;
        $this->executeStatement("SET AUTOCOMMIT=1");
    }

    public function rollbackTransaction() {
        $this->executeStatement("ROLLBACK");
        $this->transactionStarted = false;
        $this->executeStatement("SET AUTOCOMMIT=0");
    }

    public function queryForField($sqlStatement) {
        $objectResult = $this->queryForObject($sqlStatement);
        if($objectResult != NULL) {
            $arrayObject = (array) $objectResult;
            foreach($arrayObject as $field) {
                return $field;
            }
        }
        return null;
    }

    public function queryForObject($sqlStatement) {
        $list = $this->queryForList($sqlStatement);
        if(!empty($list)) {
            if(sizeof($list)>1) {
                throw new DataAccessException("Expecint zero or one result, returned ".sizeof($list), $sqlStatement);
            }
            return $list[0];
        } else {
            return NULL;
        }
    }

    public function queryForList($sqlStatement) {
        $query_result = $this->executeStatement($sqlStatement);
        $column_meta = $this->getColumnMetaInfo($query_result);
        return $this->toTypedArray($query_result, $column_meta);
    }


    public function executeStatement($sqlStatement) {
        $query_result = $this->connection->query($sqlStatement);
        $err = $this->connection->error;
        if(!empty($err)) {
            throw new DataAccessException($err, $sqlStatement);
        }
        return $query_result;
    }

    public function insertId() {
        return $this->connection->insert_id;
    }


    private function toTypedArray($query_result, $column_meta) {
        $result_list = array();
        while ($result = $query_result->fetch_object()) {
            $result_row = array();
            $column_idx = 0;
            foreach ($result as $k=>$string_value) {
                $value = $string_value;
                switch ($column_meta[$column_idx]) {
                    case "int":
                        $value = (int) $string_value;
                        break;
                    case "real":
                        $value = (float) $string_value;
                        break;
                }
                $result_row[$k] = $value;
                $column_idx++;
            }
            $result_list[] = $result_row;
        }
        return $result_list;
    }

    private function getColumnMetaInfo($query_result)
    {
        $column_idx = 0;
        $column_meta = array();
        while ($meta_info = $query_result->fetch_field()) {
            if ($meta_info) {
                $column_meta[$column_idx] = $meta_info->type;
            } else {
                $column_meta[$column_idx] = "string";
            }
            $column_idx++;
        }
        return $column_meta;
    }
}

class DataAccessException extends Exception {

    private $sqlStatement;

    public function __construct($error, $sqlStatement)
    {
        parent::__construct("Api error happened : ".$error, 0, NULL);
        $this->sqlStatement = $sqlStatement;
    }
}


?>