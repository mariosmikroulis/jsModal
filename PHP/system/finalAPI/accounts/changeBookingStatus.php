<?php

class changeBookingStatus extends userBehaviour
{
    private $bookingList = [];

    public function __construct()
    {
        parent::__construct();

        $this->main();
    }

    private function main()
    {
        $this->restrictActionTo(["staff"]);
        $this->validate();
        $this->changeBooking();
        generic::successEncDisplay("Updated");
    }

    private function validate()
    {
        try
        {
            $this->receivedData->bookingID = generic::filter_input($this->receivedData->bookingID);
            $this->receivedData->status = generic::filter_input($this->receivedData->status);

            return true;
        }

        catch(RuntimeException $e)
        {
            generic::errorToDisplayEnc("The data you have entered is invalid.");
            return false;
        }
    }

    private function changeBooking()
    {
        $this->dbCon->update("onlineBookings", [
            "bookingStatus" => $this->receivedData->status
        ], [
            "rowID" => $this->receivedData->bookingID
        ]);
    }

    public function __destruct()
    {
        parent::__destruct();
    }
}