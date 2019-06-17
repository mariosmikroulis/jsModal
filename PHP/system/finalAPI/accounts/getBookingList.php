<?php

class getBookingList extends userBehaviour
{
    private $bookingList = [];

    public function __construct()
    {
        parent::__construct();

        $this->main();
    }

    private function main()
    {
        $this->restrictActionTo(["accounts", "staff"]);
        $this->getList();
        $this->setDisplayList();
        generic::successEncDisplay($this->bookingList);
    }

    private function getList()
    {
        if($this->userData["userType"] === "staff")
        {
            $this->bookingList = $this->dbCon->fetch("onlineBookings", [], true);
        }

        else
        {
            $this->bookingList = $this->dbCon->fetch("onlineBookings", ["ownerID" => $this->userData["userID"]], true);
        }
    }

    private function setDisplayList()
    {
        $newList = [];

        for($i=0; $i < count($this->bookingList); $i++)
        {
            $currentList = [];

            if($this->userData["userType"] === "staff")
            {
                $currentList["bookingID"] = $this->bookingList[$i]["rowID"];
            }

            $currentList["bookingRef"] = $this->bookingList[$i]["bookingRef"];
            $currentList["name"] = $this->bookingList[$i]["cName"];
            $currentList["pickup"] = $this->bookingList[$i]["pickup"];
            $currentList["dropoff"] = $this->bookingList[$i]["dropoff"];
            $currentList["bookingDate"] = date("d-m-Y H:i", strtotime($this->bookingList[$i]["journeyDateTime"]));
            $currentList["vehicle"] = $this->bookingList[$i]["selectedVeh"];
            $currentList["price"] = $this->bookingList[$i]["price"];
            $currentList["placedOn"] = date("d-m-Y H:i", $this->bookingList[$i]["placedOn"]);
            $currentList["bookingStatus"] = $this->bookingList[$i]["bookingStatus"];

            $newList[] = $currentList;
        }

        $this->bookingList = $newList;
    }

    public function __destruct()
    {
        parent::__destruct();
    }
}