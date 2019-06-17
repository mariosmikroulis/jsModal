<?php

class updateDriverBalanceList extends MonoBehaviour {
    private $activeDrivers = array();
    private $authCode = "";

    private $retry = 5;
    private $ghostConnectSuccess = false;

    public function __construct()
    {
        $this->dbCon = new mysqlConnection();
        $this->receivedData = generic::getReceivedData();
        $getGhostAuthentication = new getGhostAuthentication();
        $this->authCode = $getGhostAuthentication->returnAuthentication();
        unset($getGhostAuthentication);

        $this->getActiveDriverDetails();
        $this->setActiveDriverBalances();
        $this->displayActiveDrivers();
    }

    private function getActiveDriverDetails()
    {
        $stmt = $this->dbCon->getDBCon()->query("SELECT * FROM `driverAccounts` WHERE `allowAccess`='1' ORDER BY `driverID` ASC");

        while ($row = $stmt->fetch_array(MYSQLI_ASSOC))
        {
            $arrayID = count($this->activeDrivers);
            $this->activeDrivers[$arrayID] = new stdClass();
            $this->activeDrivers[$arrayID]->rowID = count($this->activeDrivers);
            $this->activeDrivers[$arrayID]->driverCallsign = $row["driverCallsign"];
            //$this->activeDrivers[$arrayID] = array($row["driverID"], $row["driverCallsign"]);
        }
    }

    private function setActiveDriverBalances()
    {
        for($i=0; $i < count($this->activeDrivers); $i++)
        {
            $getDriverAmount = $this->getDriverBalanceAmount($this->activeDrivers[$i]->driverCallsign);
            if($getDriverAmount == null) {
                $getDriverAmount = "UNKNOWN";
            }

            else
            {
                $getDriverAmount = "£".$getDriverAmount;
                $this->updateDriverRow($this->activeDrivers[$i]->rowID, $getDriverAmount);
            }

            $this->activeDrivers[$i]->balance = $getDriverAmount;

            usleep(20000);
        }
    }

    private function getDriverBalanceAmount($driverCallsign)
    {
        $payload = $this->getCallsignPINReady($driverCallsign);

        // Prepare new cURL resource

        $ch = curl_init(settings::getOption("ghostServerURL")."driverbalance");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLINFO_HEADER_OUT, true);
        curl_setopt($ch, CURLOPT_POST, true);

        curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);

        // Set HTTP Header for POST request
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                'Content-Type: application/JSON',
                'Content-Length: ' . strlen($payload),
                'Authentication-Token: ' . $this->authCode,
                'Connection: keep-alive')
        );

        $results = curl_exec($ch);
        $statusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        // Close cURL session handle
        curl_close($ch);

        if($statusCode == 200) {
            if (!generic::isJSON($results) && $statusCode == 200) {
                $this->retry--;

                if ($this->retry <= 0) {
                    generic::errorCatch("Ghost Get full driver balance list", $results, 1);
                }

                usleep(rand(50000, 800000));
                return $this->getDriverBalanceAmount($driverCallsign);
            } else {
                return json_decode($results)->currentBalance;
            }
        }

        return null;
    }

    private function getCallsignPINReady($driverCallsign)
    {
        $preparingData = new StdClass();

        $preparingData->Callsign = (string)$driverCallsign;

        if(strlen($preparingData->Callsign) === 1)
        {
            $preparingData->Callsign = "0".$preparingData->Callsign;
            $preparingData->PIN = "00".$preparingData->Callsign;
        }

        else if(strlen($preparingData->Callsign) === 2)
        {
            $preparingData->PIN = "00".$preparingData->Callsign;
        }

        else if(strlen($preparingData->Callsign) === 3)
        {
            $preparingData->PIN = "0".$preparingData->Callsign;
        }

        return generic::toJSON($preparingData);
    }


    private function updateDriverRow($rowID, $driverBalance=0)
    {
        $this->dbCon->getDBCon()->query("UPDATE `driverAccounts` SET `lastDriverBalance`='$driverBalance' WHERE `driverID`='$rowID'");
    }

    private function displayActiveDrivers()
    {
        generic::successEncDisplay($this->activeDrivers);
    }

    public function __destruct()
    {
        unset($this->dbCon);
    }
}