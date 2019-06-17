<?php /** @noinspection ALL */


class createNewDriverAccount
{
    private $dbCon;
    private $receivedData;
    private $applicationDetails;

    public function __construct()
    {
        $this->dbCon = new mysqlConnection();
        $this->receivedData = generic::getReceivedData();

        $this->getDriverApplicationDetails();
        $this->recordNewDriverAccount();
        $this->updateApplicationDetails();
        $this->updateFileUploadHistory();
        $this->sendEmail();
        $this->displaySuccessMessage();
    }


    private function getDriverApplicationDetails()
    {
        try
        {
            $stmt = $this->dbCon->getDBCon()->query("SELECT * FROM `newDriverApplications` WHERE `appID`='".$this->receivedData->applicationID."'");
            $this->applicationDetails = $stmt->fetch_array(MYSQLI_ASSOC);
        }

        catch (RuntimeException $e)
        {
            generic::errorCatch("Mysql - Get Driver Application Details", $e->getMessage());
            return;
        }
    }


    private function recordNewDriverAccount()
    {
        try
        {
            $this->dbCon->getDBCon()->query("UPDATE `driverAccounts` SET `driverEmailAddress`='".$this->applicationDetails['emailAddress']."', `driverFullName`='".$this->applicationDetails['fullName']."', `driverEmailAddress`='".$this->applicationDetails['emailAddress']."', `driverMobileNumber`='".$this->applicationDetails['mobileNumber']."', `activationCode`='".$this->receivedData->activationCode."', `temporaryPassword`='".$this->receivedData->driverPassword."', `driverSince`='".strtotime("now")."', `allowAccess`='1' WHERE `driverCallsign`='".$this->receivedData->driverID."'");
        }

        catch (RuntimeException $e)
        {
            generic::errorCatch("Mysql - Record New DriverAccount", $e->getMessage());
            return;
        }
    }


    private function updateApplicationDetails()
    {
        try
        {
            $this->dbCon->getDBCon()->query("UPDATE `newDriverApplications` SET `appStatus`='Approved', `updatedBy`='".$this->receivedData->staffID."', `updatedDate`='".strtotime("now")."' WHERE `appID`='".$this->receivedData->applicationID."'");
        }

        catch (RuntimeException $e)
        {
            generic::errorCatch("Mysql - Update DriverAccount", $e->getMessage());
            return;
        }
    }



    private function updateFileUploadHistory()
    {
        try
        {
            $docKeyNames = ["DLF", "DLB", "PHDL", "PHVL", "MOT", "PHI", "PSP", "VHA"];
            $docKeyTypes = ["Front Driving Licence", "Rear Driving Licence", "Private Hire Driver Licence", "Private Hire Vehicle Licence", "MOT", "Private Hire Insurance", "Photograph", "Hire Agreement"];

            for($i = 0; $i<count($docKeyNames); $i++)
            {
                if($this->applicationDetails[$docKeyNames[$i]] !== "" && $this->applicationDetails[$docKeyNames[$i]] !== null)
                {
                    $fileID = $this->dbCon->fetch("uplodatedFiles", ["md5Enc"=> $this->applicationDetails[$docKeyNames[$i]]]);
                    $this->dbCon->insert("driverFileUploadHistory", [
                        "driverID" => $this->receivedData->driverID,
                        "fileID" => $fileID,
                        "docType" => $docKeyTypes[$i],
                        "fileStatus" => "Accepted"
                    ]);
                }
            }
        }

        catch (RuntimeException $e)
        {
            generic::errorCatch("Mysql - Upload files to history", $e->getMessage());
            return;
        }
    }


    private function displaySuccessMessage()
    {
        generic::successEncDisplay("");
    }

    private function sendEmail()
    {
        $generateEmail = new createEmail("createNewDriverAccount", [
            "driverName" => $this->applicationDetails['fullName']
        ]);

        $generateEmail->send([
            "to" => [$this->applicationDetails['emailAddress']],
            "reply" => "Liberty Cars Driver <driver@minicabsinlondon.com>",
            "subject" => "Liberty Cars - Final Driver Application Decision (Confirmation Status)"
        ]);
    }


    public function __destruct()
    {
        unset($this->dbCon);
    }
}