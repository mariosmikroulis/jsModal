<?php /** @noinspection SqlDialectInspection */
/** @noinspection PhpUndefinedMethodInspection */

/** @noinspection PhpUndefinedClassInspection */

/**
 * Class mysqlConnection
 */
class mysqlConnection
{
    private $dbCon;
    private $dbDetails = [];

    /**
     * mysqlConnection constructor.
     * @param string $dbCallName
     */
    public function __construct($dbCallName = "default")
    {
        $this->selectDBDetails($dbCallName);
        $this->makeConnection();
    }

    /**
     * @param $dbCallName
     */
    private function selectDBDetails($dbCallName)
    {
        switch ($dbCallName)
        {
            case "default":
                $this->dbDetails = settings::getOption("dbDetails");
                break;

            case "minicabsinlondon.com":
                generic::errorCatch("Mysql in beta - " . $dbCallName, "Have not set parameters yet.", 1);
                break;

            default:
                generic::errorCatch("Mysql unknown - " . $dbCallName, "UNKNOWN THIS DB, haven't been set on the website.", 1);
        }
    }

    private function makeConnection()
    {
        try
        {
            $this->dbCon = new mysqli($this->dbDetails["host"], $this->dbDetails["user"], $this->dbDetails["pass"], $this->dbDetails["name"]);

            if ($this->dbCon->connect_error)
            {
                throw new RuntimeException("Connection failed: " . $this->dbCon->connect_error);
            }
        }

        catch (RuntimeException $e)
        {
            generic::errorCatch("Mysql", $e->getMessage(), 0);
        }
    }

    /**
     * @return bool
     */
    public function checkResellerAuth()
    {
        $stmt = $this->count("resellerAccounts", ["loginAuthKey" => generic::getReceivedData()->authKey]);
        $stmt2 = $this->count("authorisationLogins", ["authKey" => generic::getReceivedData()->authKey, "ipAddress" => $_SERVER["REMOTE_ADDR"]]);

        if($stmt > 0 && $stmt2 > 0)
        {
            return true;
        }

        return false;
    }

    /**
     * @param bool $usingAuth
     * @param int $id
     * @return array
     */
    public function getResellerDetails($usingAuth = true, $id = 0)
    {
        try
        {
            $request = [];
            if ($usingAuth) {
                $request["loginAuthKey"] = generic::getReceivedData()->authKey;
            } else {
                $request["resellerID"] = $id;
            }

            $data = $this->fetch("resellerAccounts", $request);

            $data["results"] = false;

            if(count($data) > 1) {
                $data["results"] = true;
                unset($data["resellerPassword"]);
            }

            return $data;
        }

        catch (RuntimeException $e)
        {
            generic::errorCatch("Mysql", $e->getMessage(), 0);
        }

        return [];
    }

    /**
     * @return bool
     */
    public function checkDriverAuth()
    {
        $stmt = $this->count("driverAccounts", ["loginAuthKey" => generic::getReceivedData()->authKey]);
        $stmt2 = $this->count("authorisationLogins", ["authKey" => generic::getReceivedData()->authKey, "ipAddress" => $_SERVER["REMOTE_ADDR"]]);

        if($stmt > 0 && $stmt2 > 0)
        {
            return true;
        }

        return false;
    }

    /**
     * @param bool $usingAuth
     * @param int $num
     * @param bool $useDriverNumber
     * @return array
     */
    public function getDriverDetails($usingAuth = true, $num = 0, $useDriverNumber = true)
    {
        try
        {
            $request = [];

            if ($usingAuth) {
                $request["loginAuthKey"] = generic::getReceivedData()->authKey;
            } else {
                if($useDriverNumber) {
                    $request["driverCallsign"] = $num;
                } else {
                    $request["driverID"] = $num;
                }
            }

            $data = $this->fetch("driverAccounts", $request);
            $data["results"] = false;

            if(count($data) > 1)
            {
                $data["results"] = true;
                unset($data["driverPassword"]);
            }

            return $data;
        }

        catch (RuntimeException $e)
        {
            generic::errorCatch("Mysql", $e->getMessage(), 0);
        }

        return [];
    }

    /**
     * @param $table
     * @param array $receivedData
     * @param string $extras
     * @return int
     */
    public function query($table, $receivedData = [], $extras = "")
    {
        try
        {
            $sql = "SELECT * FROM `".$table."`";

            if(count($receivedData) > 0) {
                $sql .= " ".$this->filterData("where", $receivedData);
            }

            if(strlen($extras)>0)
            {
                $sql .= " ".$extras;
            }

            $retrieveData = $this->dbCon->query($sql);

            if($retrieveData !== FALSE)
            {
                return $retrieveData;
            }

            else
            {
                throw new RuntimeException($this->dbCon->error."\nSQL: ".$sql);
            }
        }

        catch (RuntimeException $e)
        {
            generic::errorCatch("Mysql", $e->getMessage(), 0);
            return 0;
        }
    }

    /**
     * @param $table
     * @param array $receivedData
     * @param string $extras
     * @return int
     */
    public function count($table, $receivedData = [], $extras = "")
    {
        try
        {
            $retrieveData = $this->query($table, $receivedData, $extras);

            if($retrieveData !== FALSE)
            {
                return $retrieveData->num_rows;
            }

            else
            {
                throw new RuntimeException($this->dbCon->error);
            }
        }

        catch (RuntimeException $e)
        {
            generic::errorCatch("Mysql", $e->getMessage(), 1);
            return -1;
        }
    }

    /**
     * @param $table
     * @param $receivedData
     * @param bool $returnMassData
     * @param string $extras
     * @return array
     */
    public function fetch($table, $receivedData, $returnMassData = false, $extras = "")
    {
        try
        {
            $retrieveData = $this->query($table, $receivedData, $extras);

            if($retrieveData !== FALSE)
            {
                if($returnMassData)
                {
                    $dataReturn = [];

                    while ($row = $retrieveData->fetch_array(MYSQLI_ASSOC))
                    {
                        $dataReturn[] = $row;
                    }
                }

                else
                {
                    $dataReturn = $retrieveData->fetch_array(MYSQLI_ASSOC);
                }

                return $dataReturn;
            }

            else
            {
                throw new RuntimeException($this->dbCon->error);
            }
        }

        catch (RuntimeException $e)
        {
            generic::errorCatch("Mysql", $e->getMessage(), 0);
            return [];
        }
    }

    /**
     * @param $table
     * @param array $receivedData
     * @return bool
     */
    public function insert($table, $receivedData = [])
    {
        try
        {
            $sql = "INSERT INTO `".$table."`";

            if(count($receivedData) > 0) {
                $sql .= " ".$this->filterData("ins", $receivedData);
            }

            if($this->dbCon->query($sql))
            {
                return $this->dbCon->insert_id;
            }

            else
            {
                throw new RuntimeException($this->dbCon->error."\nSQL: ".$sql);
            }
        }

        catch (RuntimeException $e)
        {
            generic::errorCatch("Mysql", $e->getMessage(), 0);
            return false;
        }
    }

    /**
     * @param $table
     * @param array $receivedData
     * @return bool
     */
    public function delete($table, $receivedData = [])
    {
        try
        {
            $sql = "DELETE FROM `".$table."`";

            if(count($receivedData) > 0) {
                $sql .= " ".$this->filterData("set", $receivedData);
            }

            if($this->dbCon->query($sql))
            {
                return true;
            }

            else
            {
                throw new RuntimeException($this->dbCon->error."\nSQL: ".$sql);
            }
        }

        catch (RuntimeException $e)
        {
            generic::errorCatch("Mysql", $e->getMessage(), 0);
            return false;
        }
    }

    /**
     * @param $table
     * @param array $receivedData
     * @param array $where
     * @return bool
     */
    public function update($table, $receivedData = [], $where = [])
    {
        try
        {
            $sql = "UPDATE `".$table."`";

            if(count($receivedData) > 0) {
                $sql .= " ".$this->filterData("set", $receivedData);
            }

            if(count($where) > 0)
            {
                $sql .= " ".$this->filterData("where", $where);
            }

            if($this->dbCon->query($sql))
            {
                return true;
            }

            else
            {
                throw new RuntimeException($this->dbCon->error."\nSQL: ".$sql);
            }
        }

        catch (RuntimeException $e)
        {
            generic::errorCatch("Mysql", $e->getMessage(), 0);
            return false;
        }
    }

    /**
     * @param $query
     * @param $requestedData
     * @return bool|string
     */
    private function filterData($query, $requestedData)
    {
        try
        {
            $columns = "";
            $values = "";
            $insert = "";
            $set = "SET ";
            $where = "WHERE ";
            $finalReturn = "";

            if(count($requestedData) > 0) {
                foreach ($requestedData as $index => $value)
                {
                    $value = $this->dbCon->escape_string(generic::filter_input($value));

                    $where .= "`$index`='$value' AND ";
                    $set .= "`$index`='$value', ";
                    $columns .= "`$index`, ";
                    $values .= "'$value', ";
                }

                $where = substr($where, 0, -5);
                $set = substr($set, 0,-2);
                $insert = "(".substr($columns, 0,-2).") VALUES (".substr($values, 0,-2).")";
            }

            if($query === "ins")
            {
                $finalReturn = $insert;
            }

            else if($query === "where")
            {
                $finalReturn = $where;
            }

            else if($query === "set")
            {
                $finalReturn = $set;
            }

            return $finalReturn;
        }


        catch (RuntimeException $e)
        {
            generic::errorCatch("Mysql", $e->getMessage(), 0);
            return "";
        }
    }

    /*private function filteredBindData($query, $requestedData)
    {
        try
        {
            $columns = "";
            $values = "";
            $insert = "";
            $set = "SET ";
            $where = "WHERE ";
            $finalReturn = "";

            if(count($requestedData) > 0) {
                foreach ($requestedData as $index => $value)
                {
                    $value = $this->dbCon->escape_string(generic::filter_input($value));

                    $where .= "`$index`='$value' AND ";
                    $set .= "`$index`='$value', ";
                    $columns .= "`$index`, ";
                    $values .= "'$value', ";
                }

                $where = substr($where, 0, -5);
                $set = substr($set, 0,-2);
                $insert = "(".substr($columns, 0,-2).") VALUES (".substr($values, 0,-2).")";
            }

            if($query === "ins")
            {
                $finalReturn = $insert;
            }

            else if($query === "where")
            {
                $finalReturn = $where;
            }

            else if($query === "set")
            {
                $finalReturn = $set;
            }

            return $finalReturn;
        }


        catch (RuntimeException $e)
        {
            generic::errorCatch("Mysql", $e->getMessage(), 0);
            return "";
        }
    }*/

    public function getDBCon()
    {
        return $this->dbCon;
    }

    public function __destruct()
    {
        if($this->dbCon->errno)
        {
            generic::errorCatch("Mysql on close", $this->dbCon->error, 0);
        }

        $this->dbCon->close();
    }
}
