<?php

/**
 * Class tempStaffLogin
 */
class tempStaffLogin
{
    private $dbCon;
    private $receivedData;

    public function __construct()
    {
        $this->dbCon = new mysqlConnection();
        $this->receivedData = generic::getReceivedData();
        if(settings::getOption("enabledDriverPortalLogin"))
        {
            if ($this->validateReceivedData())
            {
                $this->checkLoginDetails();
            }
        }

        else
        {
            generic::errorToDisplayEnc(settings::getOption("driverPortalLoginDisableMessage"));
        }
    }

    private function validateReceivedData()
    {
        try {
            $this->receivedData->emailAddress = filter_var(generic::filter_input($this->receivedData->emailAddress), FILTER_SANITIZE_EMAIL);
            $this->receivedData->password = filter_var(generic::filter_input($this->receivedData->password), FILTER_SANITIZE_STRING);
            return true;
        }

        catch (RuntimeException $e)
        {
            generic::errorToDisplayEnc("Some parameters are missing.");
        }
    }

    public function checkLoginDetails()
    {
        try {
            $checkUserExsists = $this->dbCon->getDBCon()->query("SELECT * FROM `staffAccounts` WHERE `staffUsername`='" . $this->receivedData->emailAddress . "' AND `allowAccess`='1'");
            if($checkUserExsists->num_rows == 1)
            {

                $tmpPassword = hash("sha256", $this->receivedData->emailAddress ."|". $this->receivedData->password."|".$checkUserExsists->fetch_array(MYSQLI_ASSOC)["hashSalt"]);
                $stmt = $this->dbCon->getDBCon()->query("SELECT * FROM `staffAccounts` WHERE `staffUsername`='" . $this->receivedData->emailAddress . "' AND `password`='$tmpPassword' AND `allowAccess`='1'");

                $stmt1 = $this->dbCon->getDBCon()->query("SELECT * FROM `staffAccounts` WHERE `staffUsername`='" . $this->receivedData->emailAddress . "' AND `password`='" . md5($this->receivedData->password) . "' AND `allowAccess`='1'");


                if ($stmt->num_rows == 1 || $stmt1->num_rows == 1)
                {
                    $newAuthID = sha1(date("dmYHis"));
                    $this->dbCon->getDBCon()->query("UPDATE `staffAccounts` SET `loginAuthKey`='$newAuthID' WHERE `staffUsername`='" . $this->receivedData->emailAddress . "'");
                    $made = strtotime("now");
                    $expired = strtotime("+2 hours");
                    $ipAddress = $_SERVER["REMOTE_ADDR"];
                    $this->dbCon->getDBCon()->query("INSERT INTO `authorisationLogins` (authKey, emailAddress, made, expired, ipAddress) VALUES ('$newAuthID','" . $this->receivedData->emailAddress . "', '$made', '$expired', '$ipAddress')");
                    $displayData = new stdClass();

                    if ($stmt1->num_rows == 1) {
                        $displayData->forceReset = true;
                    }

                    if ($stmt->num_rows == 1) {
                        $displayData->forceReset = false;
                        $this->dbCon->getDBCon()->query("UPDATE `staffAccounts` SET `temporaryPassword`='' WHERE `staffUsername`='" . $this->receivedData->emailAddress . "'");
                    }

                    $displayData->authentication = $newAuthID;
                    generic::successEncDisplay($displayData);
                }

                throw new RuntimeException("We don't recognise this combination of the email and password. Please, try again!");
            }
        }

        catch (RuntimeException $e)
        {
            generic::errorToDisplayEnc($e->getMessage());
            return;
        }
    }

    public function __destruct()
    {
        unset($this->dbCon);
    }
}