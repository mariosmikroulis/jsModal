<?php

/**
 * Class createTicket
 */
class createTicket extends ticketSystem
{
    private $newTicketID = 0;

    public function __construct()
    {
        parent::__construct();

        $this->validateData();
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
        try {
            $this->receivedData->ticketType = generic::filter_input($this->receivedData->ticketType);
            $this->receivedData->type = generic::filter_input($this->receivedData->type);
            $this->receivedData->subject = generic::filter_input($this->receivedData->subject);
            $this->receivedData->content = generic::filter_input($this->receivedData->content);
            return true;
        }

        catch(RuntimeException $e)
        {
            generic::errorToDisplayEnc("One or more parameters are missing.");
            return false;
        }
    }

    private function recordTicket()
    {
        $this->recordDriverTicket();
        $this->recordComment();
    }


    /**
     * @return bool
     */
    private function recordDriverTicket()
    {
        try {
            $this->newTicketID = $this->dbCon->insert("ticketDetails", [
                "type" => $this->receivedData->type,
                "subject" => $this->receivedData->subject,
                "dateSubmitted" => strtotime("now"),
                "ticketType" => $this->receivedData->ticketType,
                "createdBy" => $this->userData["userID"],
                "createdUserType" => $this->userData["userType"],
                "updatedBy" => $this->userData["userID"],
                "updatedUserType" => $this->userData["userType"],
                "updatedDate" => strtotime("now")
            ]);

            return true;
        }

        catch (RuntimeException $e)
        {
            return false;
        }
    }

    /**
     * @return bool
     */
    private function recordComment()
    {
        try {
            $this->dbCon->insert("ticketReplies", [
                "ticketID" => $this->newTicketID,
                "userID" => $this->userData["userID"],
                "userType" => $this->userData["userType"],
                "content" => $this->receivedData->content,
                "date" => strtotime("now")
            ]);

            return true;
        }

        catch (RuntimeException $e)
        {
            return false;
        }
    }

    private function sendEmail()
    {
        if(!settings::getOption("enabledEmailSystem"))
        {
            return false;
        }

        if($this->userData["userType"] === "driver") {
            $generateEmail = new createEmail("createTicketDriver", [
                "driverName" => $this->userData["driverFullName"],
                "subject" => $this->receivedData->subject
            ]);

            $generateEmail->send([
                "to" => [$this->userData["driverEmailAddress"]],
                "from" => "driver@driversinlondon.com",
                "reply" => "driver@minicabsinlondon.com",
                "subject" => "Liberty Cars - We have received your ticket on " . date("d-m-Y")
            ]);
        }

        new createSystemNotification("There is a new ticket made by a " . $this->userData["userType"] . "!", ["Liberty Cars Driver <driver@minicabsinlondon.com>"]);
    }

    private function showSuccess()
    {
        $returnData = new stdClass();
        $returnData->ticketID = $this->newTicketID;
        generic::successEncDisplay($returnData);
    }
}