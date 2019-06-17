<?php

class deleteDriverAccount extends MonoBehaviour
{
    public function __construct()
    {
        parent::__construct();

        $this->deleteDriverFileHistory();
        $this->deleteAccount();
        generic::successEncDisplay("The driver has been removed successfully!");
    }

    public function __destruct()
    {
        parent::__destruct();
    }

    private function deleteAccount()
    {
        $this->dbCon->update("driverAccounts", [
            "driverEmailAddress" => "",
            "driverPassword" => "",
            "driverFullName" => "",
            "driverMobileNumber" => "",
            "activationCode" => "",
            "temporaryPassword" => "",
            "driverStaffNotes" => "",
            "driverSince" => 0,
            "allowAccess" => 0,
        ], ["driverID"=>$this->receivedData->recordID]);
    }

    private function deleteDriverFileHistory()
    {
        //$driverCallsign = $this->dbCon->fetch("driverAccounts", ["driverID" => $this->receivedData->recordID])["driverID"];
        $this->dbCon->delete("driverFileUploadHistory", ["driverID" => $this->receivedData->recordID]);
    }
}