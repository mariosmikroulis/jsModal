<?php

class postEmails extends MonoBehaviour
{
    private $expireDateTime = 0;
    private $emailList = [];
    private $dbTableName = "emailPosting";

    public function __construct()
    {
        parent::__construct();
        $this->start();
    }

    private function start()
    {
        $this->setStartTime();
        $this->isThereWorkToBeDone();
        $this->getEmailList();
        $this->startPostingLoop();
        generic::successEncDisplay("The Postman managed to deliver all emails successfully.");
    }

    private function setStartTime()
    {
        $this->expireDateTime = strtotime("+50 seconds");
    }

    private function isThereWorkToBeDone()
    {
        // If there is no work, the Postman has two minutes off.
        if($this->dbCon->count($this->dbTableName, ["isDelivered" => 0]) < 1)
        {
            generic::successEncDisplay("There is no email for posting");

            // add the return to follow the PHP principles and manual, however, it will terminate from the above!
            return false;
        }

        return true;
    }

    private function getEmailList()
    {
        $this->emailList = $this->dbCon->fetch($this->dbTableName, ["isDelivered" => 0], true, "ORDER BY `priority` ASC");
    }

    private function startPostingLoop()
    {
        for($index = 0; $index < count($this->emailList); $index++)
        {
            if(!mail($this->emailList[$index]["sendTo"], $this->emailList[$index]["subject"], $this->emailList[$index]["content"], $this->emailList[$index]["headers"]))
            {
                $this->markEmailAsSentFailed($this->emailList[$index][$this->dbTableName."ID"], 2);
            }

            $this->markEmailAsSentFailed($this->emailList[$index][$this->dbTableName."ID"]);

            $this->checkIfTimeLeft();
        }
    }

    private function markEmailAsSentFailed($emailPostingID, $status = 1)
    {
        try
        {
            $this->dbCon->update($this->dbTableName, ["isDelivered" => $status], [$this->dbTableName."ID"=> $emailPostingID]);
        }

        catch(RuntimeException $e)
        {
            generic::errorCatch("Email Postman", "Couldn't update the email status of email ID ". $emailPostingID. " with status '". $status ."''!", 0);
        }
    }

    /*
     * This function is checking if the is done within 30 seconds. If not, this might take longer!
     */
    private function checkIfTimeLeft()
    {
        if($this->expireDateTime < strtotime("now"))
        {
            generic::successEncDisplay("Emails have been sent out, however, there are a number of emails to be sent, and the server run out of time.");
            return false;
        }

        return true;
    }

    public function __destruct()
    {
        parent::__destruct();
    }
}