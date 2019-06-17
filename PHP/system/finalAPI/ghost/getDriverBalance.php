<?php /** @noinspection SqlDialectInspection */
/** @noinspection PhpUndefinedMethodInspection */

/** @noinspection PhpUndefinedClassInspection */

/**
 * Class getDriverBalance
 */
class getDriverBalance extends userBehaviour
{
    private $authenticationID = "";

    private $ghostRequest;
    private $ghostResponse;

    private $retry = 5;
    private $ghostConnectSuccess = false;

    private $resellerDetails;
    private $driverDetails;


    public function __construct()
    {
        parent::__construct();


        if($this->identifyUser())
        {
            $this->setGhostAuthenticationID();
            $this->getBalance();
        }

        else
        {
            generic::errorToDisplayEnc("We do not authorise this action. Please re-login and try again!");
        }
    }

    private function getBalance()
    {
        $this->prepareRequestingData();
        $this->executeDriverBalanceCheck();
        $temp = new stdClass();
        $temp->nickname = $this->driverDetails->driverFullName;
        $temp->currentBalance = json_decode($this->ghostResponse)->currentBalance;
        //generic::debugging($this->driverDetails);
        generic::successEncDisplay($temp);
    }


    private function executeDriverBalanceCheck()
    {
        $ch = curl_init(settings::getOption("ghostServerURL")."driverbalance");

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLINFO_HEADER_OUT, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $this->ghostRequest);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authentication-Token: '.$this->authenticationID,
            'Content-Type: application/json',
            'Content-Length: ' . strlen($this->ghostRequest),
            'Connection: keep-alive'
        ]);


        $this->ghostResponse = curl_exec($ch);
        $statusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if(!generic::isJSON($this->ghostResponse)) {
            $this->retry--;

            if ($this->retry <= 0) {
                generic::errorCatch("Ghost - Get Driver Balance", $this->ghostRequest."\nStatus Code: ".$statusCode, 1);
            }

            usleep(rand(50000,800000));
            $this->executeDriverBalanceCheck();
        }

        else
        {
            $this->ghostConnectSuccess = true;
        }
    }

    private function setGhostAuthenticationID()
    {
        $authClass = new getGhostAuthentication();

        $this->authenticationID = $authClass->returnAuthentication();
        unset($authClass);
    }

    /**
     * @return bool
     */
    public function identifyUser()
    {
        $usersAllowed = ["driver", "staff", "reseller"];

        if(in_array($this->userData["userType"], $usersAllowed))
        {
            if($this->userData["userType"] === "driver")
            {
                $this->driverDetails = generic::toObject($this->userData);
            }

            else
            {
                $this->resellerDetails = generic::toObject($this->userData);
                $this->getDriverDetails();
            }

            return true;
        }

        return false;
    }


    /**
     * @return bool
     */
    public function getDriverDetails()
    {
        $users = new users();
        $this->driverDetails = generic::toObject($users->getDriverDetails());

        return $this->driverDetails->results;
    }

    private function prepareRequestingData()
    {
        $preparingData = new stdClass();

        $preparingData->Callsign = $this->driverDetails->driverCallsign;


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

        $this->ghostRequest = generic::toJSON($preparingData);
    }


    public function __destruct()
    {
        parent::__destruct();
    }
}