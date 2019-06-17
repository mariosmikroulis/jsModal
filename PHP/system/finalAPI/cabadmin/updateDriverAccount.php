<?php

class updateDriverAccount
{
    private $dbCon;
    private $receivedData;

    public function __construct()
    {
        $this->dbCon = new mysqlConnection();
        $this->receivedData = generic::getReceivedData();

        $this->updateAccount();
    }

    public function __destruct()
    {
        unset($this->dbCon);
    }

    private function updateAccount()
    {
        try
        {
            $stmt = $this->dbCon->getDBCon()->query("UPDATE `driverAccounts` SET `driverEmailAddress`='".$this->receivedData->emailAddress."', `driverFullName`='".$this->receivedData->fullName."', `driverMobileNumber`='".$this->receivedData->mobileNumber."', `activationCode`='".$this->receivedData->actCode."', `temporaryPassword`='".$this->receivedData->tempPassword."', `allowAccess`='".$this->receivedData->allowAccess."' WHERE `driverID`='".$this->receivedData->recordID."'");
        }

        catch (RuntimeException $e)
        {
            generic::errorCatch("Mysql - Update Account", $e->getMessage());
            return;
        }

        generic::successEncDisplay("");
    }
}