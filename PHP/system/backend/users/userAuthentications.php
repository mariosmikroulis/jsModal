<?php

/**
 * Class userAuthentications
 */
class userAuthentications extends MonoBehaviour
{
    private $userAuthenticated = false;
    private $authKey = "";
    private $userType = "UNKNOWN";
    private $userData = [];

    public function __construct()
    {
        parent::__construct();
        $this->determineUserAuthKey();
        $this->getUserAuthDetails();
        $this->setUserData();
    }

    public function __destruct()
    {
        parent::__destruct();
    }

    private function determineUserAuthKey()
    {
        $this->authKey = $this->receivedData->authKey ?? $_GET["authKey"] ?? "";
    }

    /**
     * @return bool
     */
    private function isAuthKeyValid()
    {
        /** @noinspection SpellCheckingInspection */
        $stmt = $this->dbCon->count("authorisationLogins", ["authKey" => $this->authKey, "ipAddress" => $_SERVER["REMOTE_ADDR"]]);

        if($stmt === 0)
        {
            return false;
        }

        return true;
    }

    /**
     * @param $tableName
     * @return bool
     */
    private function getUserAuthStatus($tableName)
    {
        $stmt = $this->dbCon->count($tableName."Accounts", ["loginAuthKey" => $this->authKey]);

        if ($stmt === 0)
        {
            return false;
        }

        return true;
    }


    private function retrieveUserData()
    {
        $this->userData = $this->dbCon->fetch($this->userType."Accounts", ["loginAuthKey" => $this->authKey]);

        if($this->userType === "driver")
        {
            $this->userData["access"] = $this->dbCon->fetch($this->userType . "Accounts", ["loginAuthKey" => $this->authKey]);
        }

        unset($this->userData["hashSalt"]);
        unset($this->userData["temporaryPassword"]);
        unset($this->userData[$this->userType."Password"]);
    }


    /**
     * @return bool
     */
    private function getUserAuthDetails()
    {
        if($this->isAuthKeyValid())
        {
            $this->userAuthenticated = true;

            if($this->getUserAuthStatus("reseller"))
            {
                $this->userType = "reseller";
            }

            else if($this->getUserAuthStatus("driver"))
            {
                $this->userType = "driver";
            }

            else if($this->getUserAuthStatus("staff"))
            {
                $this->userType = "staff";
            }

            else if($this->getUserAuthStatus("user"))
            {
                $this->userType = "user";
            }

            else
            {
                $this->userAuthenticated = false;
            }
        }

        return $this->userAuthenticated;
    }

    private function setUserData()
    {
        if($this->userAuthenticated)
        {
            $this->retrieveUserData();
        }

        if($this->userType!=="user") {
            if ($this->userType === "driver") {
                $this->userData["userNickname"] = $this->userData["driverFullName"];
            } else {
                $this->userData["userNickname"] = $this->userData[$this->userType . "Nickname"];
            }

            $this->userData["userID"] = $this->userData[$this->userType . "ID"];
            $this->userData["userType"] = $this->userType;
        }

        else
        {
            $this->userType = $this->userData["userType"];
            $this->userData["userNickname"] = $this->userData["firstName"] . " " . $this->userData["lastName"];
        }

        $this->userData["isUserAuth"] = $this->userAuthenticated;
    }

    /**
     * @noinspection SpellCheckingInspection
     * @return bool
     */
    public function stopUnauthUser()
    {
        if(!$this->userAuthenticated)
        {
            generic::errorToDisplayEnc("We couldn't authorise your connection. This can be caused by expiring your section. Please re-login and try again!");
            return false;
        }

        return true;
    }

    /**
     * @return bool
     */
    public function isUserAuthorised()
    {
        return $this->userAuthenticated;
    }

    /**
     * @return string
     */
    public function getUserType()
    {
        return $this->userType;
    }

    /**
     * @return mixed
     */
    public function getUserData()
    {
        return $this->userData;
    }
}