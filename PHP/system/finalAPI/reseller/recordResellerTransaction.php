<?php /** @noinspection ALL */

// authKey, driverID, amount
class recordResellerTransaction extends userBehaviour
{
    private $referenceID;

    public function __construct()
    {
        parent::__construct();

        $this->restrictActionTo(["reseller"]);
        $this->start();
    }

    public function __destruct()
    {
        unset($this->dbCon);
        unset($this->resellerDetials);
    }

    public function start()
    {
        try
        {
            if(isset($this->receivedData->amount) && $this->receivedData->amount > 0) {
                $this->recordTransactionHistory();
                $this->updateResellerInvoice();
                $this->displaySuccessMessage();
            }

            throw new RuntimeException("The amount that you have set is invalid.");
        }

        catch (RuntimeException $e)
        {
            generic::errorToDisplayEnc($e->getMessage());
            return false;
        }
    }

    public function recordTransactionHistory()
    {
        try
        {
            $dateToday = $this->referenceID = strtotime("now");
            $this->dbCon->insert("resellerTranHistory", array(
                    "resellerID" => $this->userData["resellerID"],
                    "driverCallsign" => $this->receivedData->driverID,
                    "date" => $dateToday,
                    "amount" => $this->receivedData->amount
            ));
        }

        catch (RuntimeException $e)
        {
            generic::errorToDisplayEnc($e->getMessage());
            return false;
        }
    }

    public function updateResellerInvoice()
    {
        if($this->userData["chargePerTransaction"] == 0)
        {
            return;
        }

        $queryData = ["resellerID" => $this->userData["resellerID"], "isTransactionsCleared" => 0];

        if($this->dbCon->count("resellerInvoicing", $queryData) == 0)
        {
            $this->createResellerInvoice();
        }

        $invoiceDetails = $this->dbCon->fetch("resellerInvoicing", $queryData);

        if($this->userData["commisionPerTransaction"] !== 0) {
            $totalAmountOwn = ($this->receivedData->amount * (1 - $this->userData["commisionPerTransaction"])) + $invoiceDetails["totalAmountOwn"];
            $totalAmountEarning = $this->receivedData->amount * $this->userData["commisionPerTransaction"] + $invoiceDetails["totalAmountEarning"];
        }

        else {
            $totalAmountOwn = $invoiceDetails["totalAmountOwn"] + $this->receivedData->amount;
            $totalAmountEarning = $invoiceDetails["totalAmountEarning"];
        }

        $totalAmountOwn = $totalAmountOwn - $this->userData["fixedPricePerTransaction"];
        $totalAmountEarning = $totalAmountEarning + $this->userData["fixedPricePerTransaction"];
        $totalAmountReceived = $invoiceDetails["totalAmountReceived"] + $this->receivedData->amount;

        $this->dbCon->update("resellerInvoicing", array("totalAmountOwn" => $totalAmountOwn, "totalAmountEarning" => $totalAmountEarning, "totalAmountReceived" => $totalAmountReceived), array("resellerInvoiceID" => $invoiceDetails["resellerInvoiceID"]));
    }

    public function displaySuccessMessage()
    {
        $displayData = new stdClass();
        $displayData->reference = $this->referenceID;
        generic::successEncDisplay($displayData);
    }

    public function createResellerInvoice()
    {
        $this->dbCon->insert("resellerInvoicing", ["resellerID" => $this->userData["resellerID"], "startDate" => strtotime("now")]);
    }
}