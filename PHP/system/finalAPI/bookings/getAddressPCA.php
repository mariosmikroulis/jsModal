<?php

class getAddressPCA extends MonoBehaviour
{
    private $addressID = "";
    private $returnAddress;
    private $searchCompleted = false;

    public function __construct($addressID)
    {

        parent::__construct();

        $this->addressID = $addressID;

        $this->returnAddress = new stdClass();
        $this->addressDetailsReturn();

        return $this->returnAddress;
    }

    public function searchCacheAddress()
    {
        if($this->dbCon->count("pcaLocations", ["pcaID" => $this->addressID]) > 0)
        {
            $store = $this->dbCon->fetch("pcaLocations", ["pcaID" => $this->addressID]);

            $this->returnAddress->id = $this->addressID;
            $this->returnAddress->address = $store["fullAddress"];
            $this->returnAddress->lat = $store["latitude"];
            $this->returnAddress->lng = $store["longitude"];

            $this->searchCompleted = true;
        }
    }

    public function searchCachePCA()
    {
        if($this->dbCon->count("pcaLocations", ["pcaID" => $this->addressID]) > 0)
        {
            $store = $this->dbCon->fetch("pcaLocations", ["pcaID" => $this->addressID]);

            $this->returnAddress->id = $this->addressID;
            $this->returnAddress->address = $store["fullAddress"];
            $this->returnAddress->lat = $store["latitude"];
            $this->returnAddress->lng = $store["longitude"];

            $this->searchCompleted = true;

            return true;
        }

        return $this->requestPCA();
    }

    public function requestPCA()
    {
        $curl = curl_init();

        curl_setopt_array($curl, [
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_URL => "https://services.postcodeanywhere.co.uk/Capture/Interactive/Retrieve/v1.00/json3ex.ws?Key=XR65-BX99-XJ17-MG95&Field1Format={Latitude}&Field2Format={Longitude}&id=".$this->addressID,
            CURLOPT_USERAGENT => $_SERVER["REMOTE_ADDR"]
        ]);

        // Send the request & save response to $resp
        $exec = curl_exec($curl);
        $finalData = json_decode($exec)->Items[0];
        curl_close($curl);

        $remove_character = ["\n", "\r\n", "\r"];

        try {
            if(isset($finalData->Error))
            {
                $this->onErrorAct($finalData);
                return false;
            }

            $label = str_replace($remove_character, ", ", $finalData->Label);

            $this->returnAddress->id = $this->addressID;
            $this->returnAddress->address = $label;
            $this->returnAddress->lat = $finalData->Field1;
            $this->returnAddress->lng = $finalData->Field2;


            $this->dbCon->insert("cacheAddresses", [
                "pcaID" => $this->addressID,
                "label" => $label,
                "latitude" => $finalData->Field1,
                "longtitude" => $finalData->Field2,
                "type" => $finalData->Type,
            ]);

            $this->searchCompleted = true;
        }

        catch (RuntimeException $e)
        {
            generic::errorToDisplayEnc("There has been a problem with our system. Please try again another time!");
            return false;
        }

        return true;
    }

    private function addressDetailsReturn()
    {
        if($this->searchCacheAddress() || $this->searchCachePCA())
        {
            return;
        }

        $this->requestPCA();
        return;
    }

    private function onErrorAct($data)
    {
        if($data->Error === "1001")
        {
            generic::errorToDisplayEnc("We couldn't recognise the address you have selected. Please try again!");
        }

        else if($data->Error === "3")
        {
            $email = new createEmail("pcaOutOfCreditNotification", ["name" => "Bhavish"]);
            $email->send([
                "to" => "bhavish@minicabsinlondon.com",
                "from" => "Liberty Cars System <system@driversinlondon.com>",
                "subject" => "PCA lookup has run out of credit (".strtotime("now").")."
            ]);
        }

        else
        {
            $this->emailToDev($data);
        }
    }

    private function emailToDev($data)
    {
        $email = new createEmail("pcaDevNotificationError", [
            "name" => "Marios",
            "description" => $data->Error,
            "cause" => $data->Cause,
            "resolution" => $data->Resolution
        ]);
        
        $email->send([
            "to" => "xrulez.gr@gmail.com",
            "from" => "Liberty Cars System <system@driversinlondon.com>",
            "subject" => "PCA issue appeared (".strtotime("now").")."
        ]);
    }

    public function __destruct()
    {
        parent::__destruct();
    }
}