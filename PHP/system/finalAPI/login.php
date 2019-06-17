<?php

class login extends MonoBehaviour
{
    public function __construct()
    {
        parent::__construct();

        if(settings::getOption("enabledAccountsLogin"))
        {
            $this->validateReceivedData();
            $this->checkLoginDetails();
        }

        else
        {
            generic::errorToDisplayEnc(settings::getOption("accountsLoginDisableMessage"));
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
            $this->receivedData->userType = filter_var(generic::filter_input($this->receivedData->userType), FILTER_SANITIZE_STRING);
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
            $userLookup = [
                "userEmail" => $this->receivedData->emailAddress,
                "userType"=> $this->receivedData->userType,
                "allowAccess" => "1"
            ];

            $checkUserExsists = $this->dbCon->count("userAccounts", $userLookup);

            if($checkUserExsists === 0) {
                throw new RuntimeException("We don't recognise this combination of the email and password. Please, try again!");
            }

            $userDetails = $this->dbCon->fetch("userAccounts", $userLookup);
            $authCredentials = $this->dbCon->fetch("authorisationCredentials", ["userID" => $userDetails["userID"]]);
            
            $password = hash("sha256", $userDetails["userEmail"] ."|". $this->receivedData->password."|".$authCredentials["hashSalt"]);

            if ($authCredentials["password"] === $password)
            {
                $newAuthID = sha1(date("dmYHis"));

                $this->dbCon->update("userAccounts", ["loginAuthKey" => $newAuthID], ["userID" => $userDetails["userID"]]);

                $this->dbCon->insert("authorisationLogins", [
                    "authKey" => $newAuthID,
                    "emailAddress" => $userDetails["userEmail"],
                    "made" => strtotime("now"),
                    "expired" => strtotime("+5 minutes"),
                    "ipAddress" => $_SERVER["REMOTE_ADDR"]
                ]);

                $displayData = new stdClass();

                $displayData->authentication = $newAuthID;
                generic::successEncDisplay($displayData);
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