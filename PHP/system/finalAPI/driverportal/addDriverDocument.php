<?php

class addDriverDocument
{
    private $dbCon;
    private $receivedData;
    private $driverDetails;
    private $fileDetails;

    public function __construct()
    {
        $this->dbCon = new mysqlConnection();
        $this->receivedData = generic::getReceivedData();

        if($this->checkDriverAuthentication() && $this->verifyFileUploading())
        {
            $this->recordFileSubmission();
            $this->sendEmail();
            generic::successEncDisplay("The file has been uploaded successfully.");
        }
    }


    public function checkDriverAuthentication()
    {
        if($this->receivedData->authKey !== "")
        {
            $getResults = $this->dbCon->getDBCon()->query("SELECT * FROM `driverAccounts` WHERE `loginAuthKey`='".$this->receivedData->authKey."'");

            if($getResults->num_rows === 1)
            {
                $this->driverDetails = $getResults->fetch_array(MYSQLI_ASSOC);
                generic::debugging(json_encode($this->driverDetails));
                return true;
            }

            else
            {
                generic::errorToDisplayEnc("Your section has been expired. Please re-login in order to complete this action.");
            }
        }

        else
        {
            generic::errorToDisplayEnc("The driver authentication code is missing.");
        }

        return false;
    }

    public function verifyFileUploading()
    {
        if($this->receivedData->documentEnc !== "")
        {
            $getResults = $this->dbCon->getDBCon()->query("SELECT * FROM `uplodatedFiles` WHERE `md5Enc`='".$this->receivedData->documentEnc."'");

            if($getResults->num_rows == 1)
            {
                $this->fileDetails = $getResults->fetch_array(MYSQLI_ASSOC);
                return true;
            }

            else
            {
                generic::errorToDisplayEnc("We do not recognise this file. Please try again!");
            }
        }

        else
        {
            generic::errorToDisplayEnc("We have noticed you did not upload any file.");
        }

        return false;
    }


    public function recordFileSubmission()
    {
        $this->dbCon->getDBCon()->query("INSERT INTO `driverFileUploadHistory` (`driverID`, `fileID`, `docType`,`fileStatus`) VALUES ('".$this->driverDetails["driverID"]."', '".$this->fileDetails["fileID"]."', '".$this->receivedData->docType."', 'Pending')");
    }


    private function sendEmail()
    {
        if(!settings::getOption("enabledEmailSystem"))
        {
            return false;
        }

        $generateEmail = new createEmail("addDriverDocument", [
            "driverName" => $this->driverDetails["driverFullName"],
        ]);

        $generateEmail->send([
            "to" => [$this->driverDetails["driverEmailAddress"]],
            "reply" => "Liberty Cars Driver <driver@minicabsinlondon.com>",
            "subject" => "We Received Your Document"
        ]);

        new createSystemNotification("'Driver <b>".$this->driverDetails["driverCallsign"]."</b>' has uploaded a new document and requested its renew.", ["Liberty Cars Driver <driver@minicabsinlondon.com>"]);
    }


    public function __destruct()
    {
        unset($this->dbCon);
    }
}