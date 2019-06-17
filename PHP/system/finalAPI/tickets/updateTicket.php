<?php

/**
 * Class updateTicket
 */
class updateTicket extends ticketSystem
{
    private $ticketStatus = "Pending";
    private $ticketDetails = [];

    public function __construct()
    {
        parent::__construct();

        $this->validateData();
        $this->determineUpdates();
        $this->recordTicket();
        $this->sendEmail();
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
            $this->receivedData->content = generic::filter_input($this->receivedData->content);
            $this->receivedData->issueResolved = generic::filter_input($this->receivedData->issueResolved);

            if(!$this->isValidTicket()) {
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

    private function determineUpdates()
    {
        if($this->userData["userType"] !== "staff")
        {
            $this->ticketStatus = "Waiting for reply";
        }

        else
        {
            generic::debugging($this->receivedData->issueResolved);

            $this->ticketStatus = "Pending";

            if($this->receivedData->issueResolved === "1")
            {
                $this->ticketStatus = "Resolved";
            }
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
        $this->ticketDetails = $this->dbCon->fetch("ticketDetails", $dataLookup);

        if($stmt === 0)
        {
            generic::errorToDisplayEnc("Sorry but we do not recognise this ticket.");
        }

        return true;
    }


    private function recordTicket()
    {
        $this->dbCon->update("ticketDetails", [
            "ticketStatus" => $this->ticketStatus,
            "updatedBy" => $this->userData["userID"],
            "updatedUserType" => $this->userData["userType"],
            "updatedDate" => strtotime("now"),
            "ticketStatus" => $this->ticketStatus
        ],[
            "ticketID" => $this->receivedData->ticketID
        ]);

        $this->dbCon->insert("ticketReplies", [
            "ticketID" => $this->receivedData->ticketID,
            "userID" => $this->userData["userID"],
            "userType" => $this->userData["userType"],
            "content" => $this->receivedData->content,
            "date" => strtotime("now")
        ]);
    }

    private function sendEmail()
    {
        if(!settings::getOption("enabledEmailSystem"))
        {
            return false;
        }

        if($this->userData["userType"] !== "staff") {
            new createSystemNotification("A ".$this->userData["userType"]." has replied to a ticket.", ["Liberty Cars <driver@minicabsinlondon.com>"]);
        }

        if($this->userData["userType"] === "staff")
        {
            $driverDetails = $this->users->getUserByIDType($this->ticketDetails["createdBy"], "driver");

            $generateEmail = new createEmail("replyTicketDriver", [
                "driverName" => $driverDetails["driverFullName"],
                "subject" => $this->ticketDetails["subject"]
            ]);

            $generateEmail->send([
                "to" => [$driverDetails["driverEmailAddress"]],
                "reply" => "Liberty Cars Driver <driver@minicabsinlondon.com>",
                "subject" => "There is a reply on a recent ticket you opened"
            ]);
        }
    }



    private function showSuccess()
    {
        $returnSuccess = new stdClass();
        $returnSuccess->ticketID = $this->receivedData->ticketID;
        $returnSuccess->userNickname = $this->userData["userNickname"];
        $returnSuccess->userType = $this->userData["userType"];
        $returnSuccess->date = date("d-m-Y H:i");
        $returnSuccess->content = $this->receivedData->content;
        generic::successEncDisplay($returnSuccess);
    }
}

