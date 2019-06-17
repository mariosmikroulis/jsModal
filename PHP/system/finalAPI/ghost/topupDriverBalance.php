<?php /** @noinspection PhpUndefinedFieldInspection */
/** @noinspection SqlDialectInspection */
/** @noinspection PhpUndefinedMethodInspection */
/** @noinspection PhpUndefinedClassInspection */

/** @noinspection PhpMethodParametersCountMismatchInspection */

/**
 * Class topupDriverBalance
 */
class topupDriverBalance extends userBehaviour
{
    private $authenticationID = "";

    private $transactionData;
    private $allPaymentID = "";

    private $ghostRequest;
    private $ghostResponse;

    private $retry = 5;
    private $ghostTopupSuccess = false;

    private $driverDetails;
    private $is3DSecure = false;

    private $amount = 0.0;


    public function __construct()
    {
        parent::__construct();
        if($this->validateData())
        {
            $this->getDriverDetails();
            $this->setGhostAuthenticationID();
            $this->makeTransaction();
        }
    }

    private function makeTransaction()
    {
        $paymentStatus = false;

        if($this->userData["userType"] === "driver")
        {
            if(!$this->is3DSecure)
            {
                $this->allPaymentID = $this->recordDirectTransaction("insert");
                $payment = new judopayDirectPayment($this->allPaymentID);
            }

            else
            {
                $payment = new judopay3DSPayment();
            }

            $this->transactionData = $payment->returnPaymentResponse();
            $this->allPaymentID = $this->transactionData->customRef;

            $this->recordDirectTransaction("update");

            if($this->transactionData->result  === "Success")
            {
                $paymentStatus = true;
            }

            else if($this->transactionData->result  === "Declined")
            {
                $this->displayFinalResults(0, $this->transactionData->message);
            }

            unset($payment);
        }

        else if($this->userData["userType"] === "reseller")
        {
            //$this->getResellerDetails();
            $paymentStatus = true;
            $this->allPaymentID = generic::getUniqueID("res".$this->userData["resellerID"]."-");
        }

        if($paymentStatus)
        {
            $this->prepareTopUpData();
            $this->executeDriverTopup();

            $temp = new stdClass();

            if($this->userData["userType"] === "reseller")
            {
                $this->recordTransactionHistory();
                $this->updateResellerInvoice();
            }

            $temp->currentBalance = $this->ghostResponse->currentBalance;
            $this->sendEmail();
            $this->displayFinalResults(1, $temp);
        }
    }

    /**
     * @param $type
     * @return bool|string
     */
    private function recordDirectTransaction($type)
    {
        if($type === "insert")
        {
            $paymentRef = generic::getUniqueID($this->driverDetails->driverCallsign);

            if($_GET["using"] === "direct")
            {
                $cardNumber = substr($this->receivedData->cardNumber, -4);
            }

            else
            {
                $cardNumber = "****";
            }

            $this->dbCon->insert("allPayments", [
                "paymentRef" => $paymentRef,
                "driverCallsign" => $this->driverDetails->driverCallsign,
                "amountPaid" => $this->receivedData->amount,
                "dateTime" => strtotime("now"),
                "lastFourDigits" => $cardNumber,
                "paymentStatus" => "Pending",
                "resellerUsed" => 2,
            ]);
            
            return $paymentRef;
        }

        else if($type === "update")
        {
            $this->dbCon->update("allPayments", ["paymentStatus" => $this->transactionData->result], ["paymentRef" => $this->allPaymentID]);
        }

        return "";
    }


    private function executeDriverTopup()
    {
        $ch = curl_init(settings::getOption("ghostServerURL")."driverpayment");

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLINFO_HEADER_OUT, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $this->ghostRequest);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authentication-Token: '.$this->authenticationID,
            'Content-Type: application/json',
            'Content-Length: ' . strlen($this->ghostRequest),
            'Connection: keep-alive'
        ]);


        $this->ghostResponse = curl_exec($ch);
        curl_close($ch);

        if(!generic::isJSON($this->ghostResponse)) {
            $this->retry--;

            if ($this->retry <= 0) {
                generic::errorCatch("Ghost Top-up", $this->ghostRequest."\n".$this->ghostResponse, 1);
            }

            usleep(rand(50000,800000));
            $this->executeDriverTopup();
        }

        else
        {
            $this->ghostResponse = json_decode($this->ghostResponse);
            $this->ghostTopupSuccess = true;
        }
    }


    /**
     * @return bool
     */
    private function validateData()
    {
        try
        {
            if(isset($_GET["using"]) && $_GET["using"] === "3DS")
            {
                $this->is3DSecure = true;
            }

            if(!$this->is3DSecure)
            {
                $this->receivedData->amount = generic::filter_input($this->receivedData->amount);

                if($this->userData["userType"] === "reseller")
                {
                    $this->receivedData->driverID = generic::filter_input($this->receivedData->driverID);
                }

                return true;
            }

            $_GET["receiptID"] = generic::filter_input($_GET["receiptID"]);
            return true;
        }

        catch (RuntimeException $e)
        {
            $this->displayFinalResults(0, $e->getMessage());
            return false;
        }
    }


    private function setGhostAuthenticationID()
    {
        $authClass = new getGhostAuthentication();

        $this->authenticationID = $authClass->returnAuthentication();
        unset($authClass);
    }


    /**
     * @return bool
     */
    public function getDriverDetails()
    {
        try
        {
            if($this->userData["userType"] === "driver")
            {
                $this->driverDetails = generic::toObject($this->userData);
                return true;
            }

            else if($this->userData["userType"] === "reseller")
            {
                $users = new users();
                $this->driverDetails = generic::toObject($users->getDriverDetails());

                if($this->driverDetails->results)
                {
                    return true;
                }
            }

            throw new RuntimeException("We don't authorise your connection. Please try to login again!");
        }

        catch (RuntimeException $e)
        {
            $this->displayFinalResults(0, $e->getMessage());
            return false;
        }
    }

    /**
     * @return bool
     */
    public function recordTransactionHistory()
    {
        try
        {
            $stmt = $this->dbCon->insert("allPayments", [
                "paymentRef" => $this->allPaymentID,
                "resellerUsed" => $this->userData["resellerID"],
                "driverCallsign" => $this->driverDetails->driverCallsign,
                "dateTime" => strtotime("now"),
                "amountPaid" => $this->receivedData->amount,
                "paymentStatus" => "Reseller",
            ]);

            if($stmt < 1) {
                throw new RuntimeException("There was a problem with your request but we will try to solve it soon.");
            }
        }

        catch (RuntimeException $e)
        {

            $this->displayFinalResults(0, $e->getMessage());
            return false;
        }

        return true;
    }

    public function updateResellerInvoice()
    {
        if($this->userData["chargePerTransaction"]==0)
        {
            return;
        }

        $queryData = ["resellerID"=>$this->userData["resellerID"], "isTransactionsCleared" => 0];

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

        $this->dbCon->update("resellerInvoicing", ["totalAmountOwn" => $totalAmountOwn, "totalAmountEarning" => $totalAmountEarning, "totalAmountReceived" => $totalAmountReceived], ["resellerInvoiceID" => $invoiceDetails["resellerInvoiceID"]]);
    }

    public function createResellerInvoice()
    {
        $this->dbCon->insert("resellerInvoicing", ["resellerID" => $this->userData["resellerID"], "startDate" => strtotime("now")]);
    }


    private function prepareTopUpData()
    {
        $preparingData = new stdClass();

        if($this->userData["userType"] === "driver")
        {
            $tempData = $this->dbCon->fetch("allPayments", ["paymentRef"=>$this->allPaymentID]);
            $preparingData->Callsign = $this->driverDetails->driverCallsign;
            $preparingData->Amount = $tempData["amountPaid"];
            $preparingData->Description = "Topped up by the Driver Portal.";
        }

        else
        {
            $preparingData->Callsign = $this->driverDetails->driverCallsign;
            $preparingData->Amount = $this->receivedData->amount;
            $preparingData->Description = "Topped up by the Reseller '".$this->userData["resellerNickname"]."'.";
        }

        $this->amount = $preparingData->Amount;

        if(strlen($preparingData->Callsign) === 1)
        {
            $preparingData->Callsign = "0".$preparingData->Callsign;
            $preparingData->PIN = "00".$preparingData->Callsign;
        }

        else if(strlen($preparingData->Callsign) === 2)
        {
            $preparingData->PIN = "00".$preparingData->Callsign;
        }

        else if(strlen($preparingData->Callsign) === 3)
        {
            $preparingData->PIN = "0".$preparingData->Callsign;
        }

        $preparingData->TransactionId = $this->allPaymentID;

        $this->ghostRequest = generic::toJSON($preparingData);
    }


    private function sendEmail()
    {
        if(!settings::getOption("enabledEmailSystem"))
        {
            return false;
        }

        $generateEmail = new createEmail("topupDriverBalance", [
            "driverName" => $this->driverDetails->driverFullName,
            "amount" =>  $this->amount,
            "receipt" => $this->allPaymentID
        ]);

        $generateEmail->send([
            "to" => [$this->driverDetails->driverEmailAddress],
            "from" => "Liberty Cars System <system@driversinlondon.com>",
            "reply" => "Liberty Cars Driver <driver@minicabsinlondon.com>",
            "subject" => "Your Top-Up Confirmation on ".date("d-m-Y"),
            "bcc" => ["bhavish@minicabsinlondon.com"]
        ]);

        new createSystemNotification("A new driver payment has been received!", ["Liberty Cars Driver <driver@minicabsinlondon.com>"]);
    }


    /**
     * @param $results
     * @param $data
     */
    private function displayFinalResults($results, $data)
    {
        if(!$this->is3DSecure)
        {
            if($results === 0)
            {
                generic::errorToDisplayEnc($data);
            }

            else if($results === 1)
            {
                $data->result = "Success";
                generic::successEncDisplay($data);
            }
        }

        else
        {
            if($results === 0)
            {
                //$data->result = "Fail";
            }

            else if($results === 1)
            {
                $data->result = "Success";
            }

            generic::iframeEncDisplay($results, $data);
        }
    }


    public function __destruct()
    {
        parent::__destruct();
    }
}