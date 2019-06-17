<?php

/**
 * Class users
 */
class users extends MonoBehaviour
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * @param int $id
     * @return array
     */
    public function getResellerDetails($id = 0)
    {
        try
        {
            $request = ["resellerID" => $id];

            if($id === 0)
            {
                $request["resellerID"] = $this->receivedData->resellerID ?? 0;
            }

            $data = $this->dbCon->fetch("resellerAccounts", $request);

            $data["results"] = false;

            if(count($data) > 1) {
                $data["results"] = true;
                unset($data["resellerPassword"]);
                unset($data["hashSalt"]);
                unset($data["temporaryPassword"]);
            }

            return $data;
        }

        catch (RuntimeException $e)
        {
            generic::errorCatch("Mysql", $e->getMessage(), 0);
        }

        return ["results"=>false];
    }

    /**
     * @param int $num
     * @param bool $getByID
     * @return array
     */
    public function getDriverDetails($num = 0, $getByID = false)
    {
        try
        {
            $request = [];
            if($num === 0) {
                $request["driverCallsign"] = $this->receivedData->driverID ?? 0;
            } else {
                if (!$getByID) {
                    $request["driverCallsign"] = $num;
                } else {
                    $request["driverID"] = $num;
                }
            }

            $data = $this->dbCon->fetch("driverAccounts", $request);
            $data["results"] = false;

            if(count($data) > 1)
            {
                $data["results"] = true;
                unset($data["driverPassword"]);
                unset($data["hashSalt"]);
                unset($data["temporaryPassword"]);
            }

            return $data;
        }

        catch (RuntimeException $e)
        {
            generic::errorCatch("Mysql", $e->getMessage(), 0);
        }

        return ["results"=>false];
    }

    /**
     * @param int $id
     * @return array
     */
    public function getStaffDetails($id = 0)
    {
        try
        {
            $request = ["staffID" => $id];

            if($id === 0)
            {
                $request["staffID"] = $this->receivedData->resellerID ?? 0;
            }

            $data = $this->dbCon->fetch("staffAccounts", $request);

            $data["results"] = false;

            if(count($data) > 1) {
                $data["results"] = true;
                unset($data["Password"]);
                unset($data["hashSalt"]);
                unset($data["temporaryPassword"]);
            }

            return $data;
        }

        catch (RuntimeException $e)
        {
            generic::errorCatch("Mysql", $e->getMessage(), 0);
        }

        return ["results"=>false];
    }


    /**
     * @param $id
     * @param $type
     * @return array
     */
    public function getUserByIDType($id, $type)
    {
        $userDetails = ["results"=>false];

        if($type === "driver")
        {
            $userDetails = $this->getDriverDetails($id, true);
            $userDetails["userNickname"] = $userDetails["driverFullName"];
        }

        else if($type === "reseller")
        {
            $userDetails = $this->getResellerDetails($id);
            $userDetails["userNickname"] = $userDetails[$type."Nickname"];
        }

        else if($type === "staff")
        {
            $userDetails = $this->getStaffDetails($id);
            $userDetails["userNickname"] = $userDetails[$type."Nickname"];
        }

        return $userDetails;
    }


    public function __destruct()
    {
        parent::__destruct();
    }
}