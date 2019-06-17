<?php

/**
 * Class resellerLogin
 */
class resellerLogin
{
    private $dbCon;
    private $receivedData;

    public function __construct()
    {
        $this->dbCon = new mysqlConnection();
        $this->receivedData = generic::getReceivedData();

        if(settings::getOption("enabledResellerLogin")) {
            $this->checkLoginDetails();
        }

        else
        {
            generic::errorToDisplayEnc(settings::getOption("resellerLoginDisableMessage"));
        }
    }

    public function checkLoginDetails()
    {
        try
        {
            $tmpPassword = hash("sha256", $this->receivedData->password);
            $stmt = $this->dbCon->count("resellerAccounts", array("resellerUsername" => $this->receivedData->emailAddress, "resellerPassword" => $tmpPassword));


            if($stmt == 1)
            {
                $newAuthID = sha1(date("dmYHis"));
                $this->dbCon->update("resellerAccounts", array("loginAuthKey" => $newAuthID), array("resellerUsername" => $this->receivedData->emailAddress));
                $this->dbCon->insert("authorisationLogins", array("authKey" => $newAuthID, "emailAddress" => $this->receivedData->emailAddress, "made" => strtotime("now"), "expired" => strtotime("+5 minutes"), "ipAddress" => $_SERVER["REMOTE_ADDR"]));

                $displayData = new stdClass();
                $displayData->authentication = $newAuthID;
                generic::successEncDisplay($displayData);
            }

            else
            {
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