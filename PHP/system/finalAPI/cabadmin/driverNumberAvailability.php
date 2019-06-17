<?php /** @noinspection ALL */


class driverNumberAvailability extends MonoBehaviour
{
    public function __construct()
    {
        parent::__construct();

        $this->checkNumberAvailability();
    }

    private function checkNumberAvailability()
    {
        if(isset($this->receivedData->driverLookupID) && $this->receivedData->driverLookupID >= 1 && $this->receivedData->driverLookupID < 1001 && $this->receivedData->driverLookupID != 333)
        {
            try
            {
                $stmt = $this->dbCon->getDBCon()->query("SELECT * FROM `driverAccounts` WHERE `driverCallsign`='".$this->receivedData->driverLookupID."'");
                $getEmailField = $stmt->fetch_array(MYSQLI_ASSOC)["driverEmailAddress"];


                if($getEmailField==="" || $getEmailField === null)
                {
                    generic::successEncDisplay(["status" => "Available"]);
                }

                else
                {
                    generic::successEncDisplay(["status" => "Unavailable"]);
                }
            }

            catch (RuntimeException $e)
            {
                generic::errorToDisplayEnc($e->getMessage());
                return;
            }
        }

        else
        {
            generic::errorToDisplayEnc("Invalid driver ID");
        }
    }

    public function __destruct()
    {
        parent::__destruct();
    }
}