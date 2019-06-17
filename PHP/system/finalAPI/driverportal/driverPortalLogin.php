<?php

/**
 * Class driverPortalLogin
 */
class driverPortalLogin extends MonoBehaviour
{
    public function __construct()
    {
        parent::__construct();

        if(settings::getOption("enabledDriverPortalLogin"))
        {
            $this->validateReceivedData();
            $this->checkLoginDetails();
        }

        else
        {
            generic::errorToDisplayEnc(settings::getOption("driverPortalLoginDisableMessage"));
        }
    }

    /**
     * @return bool
     */
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
            return false;
        }
    }

    public function checkLoginDetails()
    {
        try {
            $checkUserExsists = $this->dbCon->count("driverAccounts", ["driverEmailAddress" => $this->receivedData->emailAddress, "allowAccess" => "1"]);

            if($checkUserExsists === 1)
            {
                $driverAccDetails = $this->dbCon->fetch("driverAccounts", ["driverEmailAddress" => $this->receivedData->emailAddress, "allowAccess" => "1"]);
                $tmpPassword = hash("sha256", $this->receivedData->emailAddress ."|". $this->receivedData->password."|".$driverAccDetails["hashSalt"]);
                $isSecurePassAccurate = $this->dbCon->count("driverAccounts", ["driverEmailAddress" => $this->receivedData->emailAddress, "driverPassword" => $tmpPassword]);
                $isTempPassAccurate = $this->dbCon->count("driverAccounts", ["driverEmailAddress" => $this->receivedData->emailAddress, "temporaryPassword" => $this->receivedData->password]);

                if ($isSecurePassAccurate === 1 || $isTempPassAccurate === 1)
                {
                    $newAuthID = sha1(date("dmYHis"));
                    $this->dbCon->update("driverAccounts", ["loginAuthKey" => $newAuthID], ["driverEmailAddress" => $this->receivedData->emailAddress]);
                    $this->dbCon->insert("authorisationLogins", ["authKey" => $newAuthID, "emailAddress" => $this->receivedData->emailAddress, "made" => strtotime("now"), "expired" => strtotime("+5 minutes"), "ipAddress" => $_SERVER["REMOTE_ADDR"]]);

                    $displayData = new stdClass();

                    if ($isTempPassAccurate == 1) {
                        $displayData->forceReset = true;
                    }

                    if ($isSecurePassAccurate == 1) {
                        $displayData->forceReset = false;
                        //$this->dbCon->getDBCon()->query("UPDATE `driverAccounts` SET `temporaryPassword`='' WHERE `driverEmailAddress`='" . $this->receivedData->emailAddress . "'");
                    }

                    $displayData->authentication = $newAuthID;
                    generic::successEncDisplay($displayData);
                }
            }

            throw new RuntimeException("We don't recognise this combination of the email and password. Please, try again!");
        }

        catch (RuntimeException $e)
        {
            generic::errorToDisplayEnc($e->getMessage());
            return;
        }
    }

    public function __destruct()
    {
        parent::__destruct();
    }
}