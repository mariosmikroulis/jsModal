<?php
/**
 * Created by PhpStorm.
 * Class viewTickets
 * User: xfran
 * Date: 31/01/2019
 * Time: 20:25
 */

class viewTickets extends ticketSystem
{
    private $ticketDetails;

    public function __construct()
    {
        parent::__construct();

        $this->validateData();
        $this->viewTicket();
        $this->showSuccess();
    }

    public function __destruct()
    {
        parent::__destruct();
    }

    /**
     * @return bool
     */
    private function validateData()
    {
        try
        {
            $this->receivedData->ticketID = generic::filter_input($this->receivedData->ticketID);
            $this->receivedData->ticketType = generic::filter_input($this->receivedData->ticketType);

            if($this->receivedData->ticketID > 0 && !$this->isValidTicket()) {
                throw new RuntimeException();
            }

            return true;
        }

        catch(RuntimeException $e)
        {
            generic::errorToDisplayEnc("The data you have entered is invalid.");
            return false;
        }
    }


    /**
     * @return bool
     */
    private function isValidTicket()
    {
        $dataLookup = ["ticketID" => $this->receivedData->ticketID];

        if($this->userData["userType"] !== "staff") {
            $dataLookup["createdBy"] = $this->userData["userID"];
            $dataLookup["createdUserType"] = $this->userData["userType"];
        }

        $stmt = $this->dbCon->count("ticketDetails", $dataLookup);

        if($stmt === 0)
        {
            generic::errorToDisplayEnc("Sorry but we do not recognise this ticket.");
        }

        return true;
    }

    private function viewTicket()
    {
        $fetchingData = ["ticketType"=>$this->receivedData->ticketType];
        $receiveMultiple = true;

        if($this->receivedData->ticketID != 0)
        {
            $fetchingData["ticketID"] = $this->receivedData->ticketID;
            $receiveMultiple = false;
        }

        if($this->userData["userType"] !== "staff")
        {
            $fetchingData["createdBy"] = $this->userData["userID"];
            $fetchingData["createdUserType"] = $this->userData["userType"];
        }

        $this->ticketDetails = $this->dbCon->fetch("ticketDetails", $fetchingData, $receiveMultiple);
        $this->replaceReceivedData();

        if($this->receivedData->ticketID > 0)
        {
            $this->ticketDetails["comments"] = $this->dbCon->fetch("ticketReplies", ["ticketID"=>$this->receivedData->ticketID], true, "ORDER BY `date` ASC");
            $this->replaceCommentData();
        }
    }

    private function replaceReceivedData()
    {
        if($this->receivedData->ticketID == 0)
        {
            for($i=0; $i < count($this->ticketDetails);$i++)
            {
                $this->ticketDetails[$i] = $this->changeTicketData($this->ticketDetails[$i]);
            }
        }

        else
        {
            $this->ticketDetails = $this->changeTicketData($this->ticketDetails);
        }
    }

    /**
     * @param $data
     * @return mixed
     */
    private function changeTicketData($data)
    {
        //print_r($data);
        if($this->userData["userType"] === $data["createdUserType"])
        {
            $data["createdBy"] = $this->userData["userNickname"];
        }

        else
        {
            $userDetails =$this->users->getUserByIDType($data["createdBy"], $data["createdUserType"]);

            $data["createdBy"] = $userDetails["userNickname"];

            if($data["createdUserType"] === "driver")
            {
                $data["createdBy"] .= " (Driver ".$userDetails["driverCallsign"].")";
            }
        }

        if($this->userData["userType"] === $data["updatedUserType"])
        {
            $data["updatedBy"] = $this->userData["userNickname"];
        }

        else
        {
            $data["updatedBy"] = $this->users->getUserByIDType($data["updatedBy"], $data["updatedUserType"])["userNickname"];
        }

        $data["dateSubmitted"] = date("d-m-Y H:i", $data["dateSubmitted"]);
        $data["updatedDate"] = date("d-m-Y H:i", $data["updatedDate"]);

        return $data;
    }


    private function replaceCommentData()
    {
        for($i=0;$i<count($this->ticketDetails["comments"]);$i++)
        {
            $this->ticketDetails["comments"][$i] = $this->changeCommentData($this->ticketDetails["comments"][$i]);
        }
    }


    /**
     * @param $data
     * @return mixed
     */
    private function changeCommentData($data)
    {
        $data["userNickname"] = $this->users->getUserByIDType($data["userID"], $data["userType"])["userNickname"];
        if($this->userData["userType"] === "staff" && $data["userType"] === "driver")
        {
            $data["userNickname"] .= " (Driver ".$this->users->getUserByIDType($data["userID"], $data["userType"])["driverCallsign"].")";
        }
        $data["date"] = date("d-m-Y H:i", $data["date"]);

        return $data;
    }


    private function showSuccess()
    {
        generic::successEncDisplay(generic::toObject($this->ticketDetails));
    }
}