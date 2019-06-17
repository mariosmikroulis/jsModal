<?php

class quotePrice extends MonoBehaviour
{
    private $isPickupSet = false;
    private $quoteDetails = [];
    private $tabletAccountDetails;
    private $tabletAccID = 1;
    private $ghostCredentials = "";

    public function __construct()
    {
        $this->quoteDetails["pickup"] = new stdClass();
        $this->quoteDetails["dropoff"] = new stdClass();

        parent::__construct();
        $this->validateInfo();
        $this->finalizeQuoteDetails();
    }

    private function validateInfo()
    {
        if(isset($this->receivedData->bookingType))
        {
            if($this->receivedData->bookingType === "tabletQuote" && $this->tabletQuote)
            {
                if($this->dbCon->count("tabletAccounts", ["tabletAccID" => $this->receivedData->tabletID, "enableAccount" => 1]) > 0) {
                    $this->tabletAccID = $this->receivedData->tabletID;
                }

                $this->isPickupSet = true;
                return true;
            }

            else if($this->receivedData->bookingType === "randomQuote" && $this->randomQuote)
            {
                if(isset($this->receivedData->pickupID)) {
                    return true;
                }
            }
        }

        generic::errorToDisplayEnc("We do not recognise those booking details");
        return false;
    }

    private function tabletQuote()
    {
        $this->quoteDetails["ghostAccountRef"] = "a6tq4";

        $this->tabletAccountDetails = $this->dbCon->fetch("tabletAccounts", ["tabletAccID" => $this->tabletAccID]);

        $this->quoteDetails["pickup"]->address = $this->tabletAccountDetails["pickup"];
        $this->quoteDetails["pickup"]->lat = $this->tabletAccountDetails["pickupLat"];
        $this->quoteDetails["pickup"]->lng = $this->tabletAccountDetails["pickupLng"];

    }

    private function randomQuote()
    {
        $this->quoteDetails["ghostAccountRef"] = "b3tg9";
        $this->quoteDetails["pickup"] = new getAddressPCA($this->receivedData->pickupID);
    }

    private function finalizeQuoteDetails()
    {
        $this->quoteDetails["dropoff"] = new getAddressPCA($this->receivedData->dropoffID);
        $this->quoteDetails["bookingDate"] = date('Y-m-d\TH:i:s.u', strtotime($this->receivedData->bookingDate));
        $this->quoteDetails["paymentMethod"] = "";
        $this->quoteDetails["mainVehicle"] = "";
        $this->quoteDetails["returnVehicle"] = "";
    }

    public function __destruct()
    {
        parent::__destruct();
    }
}