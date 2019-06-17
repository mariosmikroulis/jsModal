<?php

class getGhostAuthentication
{
    private $receivedData;
    private $usedAuthentication = "";
    private $retry = 5;
    private $ghostConnectSuccess = false;

    public function __construct()
    {
        $this->receivedData = generic::getReceivedData();

        if(!$this->checkAuthFromFile())
        {
            $this->makeGhostCon();
        }

        return $this->printGhostAuthResults();
    }


    public function makeGhostCon()
    {
        $payload = json_encode(array(
            'Username' => settings::getOption("ghostUser"),
            'Password' => settings::getOption("ghostPass")
        ));

        // Prepare new cURL resource
        //$ch = curl_init();
        $ch = curl_init(settings::getOption("ghostServerURL")."authenticate");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLINFO_HEADER_OUT, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);

        // Set HTTP Header for POST request
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                'Content-Type: application/JSON',
                'Content-Length: ' . strlen($payload),
                'Connection: keep-alive')
        );

        //ghostCommunicationIssue.txt

        $results = curl_exec($ch);

        // Close cURL session handle
        curl_close($ch);

        if(!generic::isJSON($results)) {
            $this->retry--;

            if ($this->retry <= 0) {
                generic::errorCatch("Ghost Authentication", $results, 1);
            }

            usleep(rand(50000,800000));
            $this->makeGhostCon();
        }

        else
        {
            $this->ghostConnectSuccess = true;
            $this->usedAuthentication = generic::toObject($results)->secret;
            $this->recordNewAuthentication();
        }
    }


    private function checkAuthFromFile()
    {
        $file = file_get_contents("fileData/data.txt");
        $data = json_decode($file);

        if($data->ghost->expiresAt > strtotime("1 hour ago"))
        {
            $this->usedAuthentication = $data->ghost->authKey;
            return true;
        }

        return false;
    }

    private function recordNewAuthentication()
    {
        $data = file_get_contents("fileData/data.txt");
        $data = json_decode($data);
        $data->ghost->authKey = $this->usedAuthentication;
        $data->ghost->expiresAt = strtotime("now");
        file_put_contents("fileData/data.txt", json_encode($data));
    }


    private function printGhostAuthResults()
    {
        if ($this->receivedData->action === "getGhostAuthentication")
        {
            $this->displaySuccessData();

            return "";
        }

        return $this->usedAuthentication;
    }

    private function displaySuccessData()
    {
        $preparingData = new StdClass();
        $preparingData->Secret = $this->usedAuthentication;
        generic::successEncDisplay($preparingData);
    }


    public function returnAuthentication()
    {
        return $this->usedAuthentication;
    }


    public function __destruct()
    {
    }

}