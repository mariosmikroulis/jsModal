<?php
/**
 * Created by PhpStorm.
 * User: Marios
 * Date: 01/02/2019
 * Time: 15:18
 */

class MonoBehaviour
{
    protected $dbCon;
    protected $receivedData;

    protected function __construct()
    {
        $this->dbCon = new mysqlConnection();
        $this->receivedData = generic::getReceivedData();
    }

    protected function __destruct()
    {
        unset($this->dbCon);
        unset($this->receivedData);
    }
}