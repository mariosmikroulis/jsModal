<?php

class judopay3DSPayment extends MonoBehaviour
{
    private $preparedCurlData;
    private $curlResponse;

    private $resultsToReturn;
    public $paymentReference;

    private $storedToDB;

    public function __construct()
    {
        parent::__construct();
        if($this->validateTransaction())
        {
            $this->prepareData();
            $this->makeNormalPayment();
            $this->checkPrepareResponse();
            $this->returnPaymentResponse();
        }

        else
        {
            generic::errorToDisplayEnc("Unavailable to complete the payment, as we do not recognise this transaction.");
        }
    }

    public function __destruct()
    {
        unset($this->preparedCurlData);
        unset($this->curlResponse);
        unset($this->resultsToReturn);
        parent::__destruct();
    }

    public function validateTransaction()
    {
        if(isset($this->receivedData->PaRes) && isset($this->receivedData->MD) && isset($_GET["receiptID"]))
        {
            if($this->dbCon->count("judopayTransactions", ["receiptId" => $_GET["receiptID"]]) === 1)
            {
                return true;
            }
        }

        return false;
    }


    private function prepareData()
    {
        $this->storedToDB = $this->dbCon->fetch("judopayTransactions", ["receiptId" => $_GET["receiptID"]]);
        $data = new stdClass();

        $data->PaRes = $this->receivedData->PaRes;
        $data->Md =  $this->receivedData->MD;

        $this->preparedCurlData = json_encode($data);
    }

    private function makeNormalPayment()
    {
        try {
            $ch = curl_init(settings::getJudo()["url"] . $this->storedToDB["receiptId"]);

            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $this->preparedCurlData);
            curl_setopt($ch, CURLOPT_HEADER, $this->preparedCurlData);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 0);
            curl_setopt($ch, CURLOPT_TIMEOUT, 400);
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'API-Version: 5.6',
                'Accept: application/json',
                'Authorization: Basic ' . settings::getJudo()["token"],
                'Content-Type: application/json'
            ]);

            $this->curlResponse = curl_exec($ch);
            curl_close($ch);
        }

        catch (RuntimeException $e)
        {
            generic::errorCatch("Judo 3DS payment Connection", $this->preparedCurlData);
        }
    }

    private function updatePayment()
    {
        $this->dbCon->update("judopayTransactions", [
            "type"=>$this->curlResponse->type,
            "result"=>$this->curlResponse->result,
            "message" => urlencode($this->curlResponse->message),
            "originalAmount" => $this->curlResponse->originalAmount,
            "netAmount" => $this->curlResponse->netAmount,
            "amount" => $this->curlResponse->amount,
            "cardToken" => $this->curlResponse->cardToken ?? "",
            "consumerToken" => $this->curlResponse->consumerToken ?? "",
            "paReq" => "",
            "md" => "",
        ], ["receiptId" => $this->storedToDB["receiptId"]]);
    }


    private function checkPrepareResponse()
    {
        $results = new stdClass();

        if(generic::isJSON($this->curlResponse))
        {
            $this->curlResponse = json_decode($this->curlResponse);

            $this->updatePayment();

            $results->result = $this->curlResponse->result;
            $results->judopayPaymentRef = $this->paymentReference;
            $results->customRef = $this->storedToDB["customRef"];
            $results->results = "Success";


            if($results->result === "Success")
            {
                if($this->receivedData->action === "topupDriverBalance")
                {
                    $results->data = $this->curlResponse;
                }

                $results->message = "";
            }


            else
            {
                $results->message = $this->curlResponse->message;
            }
        }

        else
        {
            $results->result = $results->results = "Failed";
            $results->message = "Our payment system is currently unavailable to complete your payment. Please try again later!";
        }

        $this->resultsToReturn = $results;
    }


    public function returnPaymentResponse()
    {
        if($this->resultsToReturn->results === "Success" || $this->resultsToReturn->results === "Declined")
        {
            if($this->receivedData->action === "topupDriverBalance")
            {
                return $this->resultsToReturn;
            }

            if($this->resultsToReturn->results === "Success") {
                generic::successEncDisplay($this->resultsToReturn);
            }

            else
            {
                generic::errorToDisplayEnc("Sorry but your payment has been declined by your bank. This is the following message we have received: <br>". $this->resultsToReturn->message);
            }
        }

        return "";
    }
}
