<?php /** @noinspection PhpUndefinedMethodInspection */
/** @noinspection PhpUndefinedFieldInspection */

/** @noinspection PhpUndefinedClassInspection */

class bookingAvailability extends MonoBehaviour
{
    private $quoteDetails = [];
    private $agentBookingAPI = "";
    private $ghostAccount = [];
    private $agentRef = "";
    private $ghostRequests = [];
    private $ghostResponse = [];

    public function __construct($quoteDetails = [])
    {
        parent::__construct();
        $this->quoteDetails = $quoteDetails;
        $this->start();
    }

    private function start()
    {
        $this->getGhostAccounts();
        $this->setPaymentMethod();
        $this->getAgentBookingAPI();
        $this->agentRef = $this->ghostAccount["agentRef"].generic::getUniqueID("");
        $this->replaceAPIProperties();
        $this->makeRequest();
    }

    private function getGhostAccounts()
    {
        if($this->quoteDetails["ghostAccountRef"] === "" || $this->dbCon->count("bookingGhostCredentials", ["ghostAccountRef" => $this->quoteDetails["ghostAccountRef"]]) === 0)
        {
            $this->quoteDetails["ghostAccountRef"] = "b3tg9";
        }

        $this->ghostAccount = $this->dbCon->fetch("bookingGhostCredentials", ["ghostAccountRef" => $this->quoteDetails["ghostAccountRef"]]);
    }

    private function getAgentBookingAPI()
    {
        $this->agentBookingAPI = file_get_contents("./fileData/AgentBookingAvailabilityRequest.xml");
        $this->replaceAPIProperties();
    }

    private function replaceAPIProperties()
    {
        $replacing = [
            "agentID" => $this->ghostAccount["agentID"],
            "agentPassword" => $this->ghostAccount["password"],
            "agentRef" => $this->agentRef,
            "ventorID" => $this->ghostAccount["vendorID"],
            "currency" => $this->ghostAccount["currency"],
            "bookingDate" => $this->quoteDetails["bookingDate"],
            "pickupFull" => $this->quoteDetails["pickup"]["address"],
            "pickupLat" => $this->quoteDetails["pickup"]["lat"],
            "pickupLng" => $this->quoteDetails["pickup"]["lng"],
            "dropoffFull" => $this->quoteDetails["dropoff"]["address"],
            "dropoffLat" => $this->quoteDetails["dropoff"]["lat"],
            "dropoffLng" => $this->quoteDetails["dropoff"]["lng"],
            "numPassengers" => 1,
            "numLuggages" => 1,
            "wheelchairSupport" => "",
            "paymentType" => $this->quoteDetails["paymentType"],
            "ContractReference" => ""
        ];

        if($this->ghostAccount["fixNumOfPax"] === "0")
        {
            $replacing["numPassengers"] = $this->quoteDetails["numPassengers"];
        }

        if($this->ghostAccount["fixNumOfLug"] === "0")
        {
            $replacing["numLuggages"] = $this->quoteDetails["numLuggages"];
        }

        if($this->quoteDetails["paymentType"] === "InvoicedAccount")
        {
            $replacing["ContractReference"] = "<ContractReference>Card</ContractReference>";
        }

        $this->agentBookingAPI = utilities::replaceText($this->agentBookingAPI, $replacing);

        if($this->receivedData->action === "quotePrice")
        {
            $vehicles = explode(",", $this->ghostAccount["vehicle"]);
            $this->ghostRequests["main"] = $this->createAPI($vehicles);

            if($this->receivedData->journeys->isReturnRequested === "1")
            {
                $this->ghostRequests["return"] = $this->createAPI($vehicles);
            }
        }

        else
        {
            if($this->receivedData->journeys->isReturnRequested === "1")
            {
                $this->receivedData->journeys->returnVehicle = $this->replaceVehicleNaming($this->receivedData->journeys->returnVehicle);
                $this->ghostRequests["return"] = $this->createAPI([$this->receivedData->journeys->returnVehicle]);
            }

            $this->receivedData->journeys->mainVehicle = $this->replaceVehicleNaming($this->receivedData->journeys->mainVehicle);
            $this->ghostRequests["main"] = $this->createAPI([$this->receivedData->journeys->mainVehicle]);
        }
    }

    private function createAPI($vehicles)
    {
        $results = [];

        for($i=0;$i<count($vehicles); $i++)
        {
            $results[] = utilities::replaceText($this->agentBookingAPI, ["vehicleType" => $vehicles[$i]]);
        }

        return $results;
    }

    private function replaceVehicleNaming($vehicleName)
    {
        if($vehicleName === "Standard")
        {
            return "Saloon";
        }

        else if($vehicleName === "MPV+")
        {
            return "Coach";
        }

        return $vehicleName;
    }

    private function makeRequest()
    {
        $this->ghostResponse["main"] = new ghostConnector($this->ghostRequest["main"]);
        $this->recordRequestResponse($this->ghostRequest["main"], $this->ghostResponse["main"]);

        if($this->receivedData->journeys->isReturnRequested === "1")
        {
            $this->ghostResponse["return"] = new ghostConnector($this->ghostRequest["return"]);
            $this->recordRequestResponse($this->ghostRequest["return"], $this->ghostResponse["return"]);
        }
    }

    private function recordRequestResponse($requests, $responses)
    {
        for($i=0; $i < count($requests); $i++) {
            $this->dbCon->insert("ghostRequestsResponss", [
                "task" => "price_request",
                "requestAPI" => $requests[$i],
                "delivered" => "1",
                "responseAPI" => $responses[$i],
                "status" => "1",
                "tries" => "0",
                "dateTime" => strtotime("now")
            ]);
        }
    }

    private function setPaymentMethod()
    {
        if($this->quoteDetails["paymentMethod"] === "Paypal" && $this->ghostAccount["accPaypalBookings"] === "1")
        {
            $this->quoteDetails["paymentType"] = "InvoicedAccount";
            return true;
        }

        else if($this->quoteDetails["paymentMethod"] === "Cash" && $this->ghostAccount["accCashBookings"] === "1")
        {
            $this->quoteDetails["paymentType"] = "Cash";
            return true;
        }

        else if($this->quoteDetails["paymentMethod"] === "Card" && $this->ghostAccount["accCardBookings"] === "1")
        {
            $this->quoteDetails["paymentType"] = "InvoicedAccount";
            return true;
        }

        else if($this->quoteDetails["paymentMethod"] === "Account" && $this->ghostAccount["accAccBookings"] === "1")
        {
            $this->quoteDetails["paymentType"] = "InvoicedAccount";
            return true;
        }

        else if($this->quoteDetails["paymentMethod"] === "")
        {
            $this->quoteDetails["paymentMethod"] = "Cash";
            return true;
        }

        generic::errorToDisplayEnc("We do not allow this type of payment, or this type of payment is temporary disabled.");
        return false;
    }

    public function __destruct()
    {
        parent::__destruct();
    }
}