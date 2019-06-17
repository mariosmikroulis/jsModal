<?php /** @noinspection ALL */

class judopayDirectPayment extends MonoBehaviour
{
    private $preparedCurlData;
    private $curlResponse;
    private $resultsToReturn;
    public $paymentReference;
    private $consumerReference;

    private $customRef = "";


    public function __construct($customRef = "")
    {
        parent::__construct();

        $this->customRef = $customRef;

        if($this->validateCardInfo())
        {
            $this->prepareData();
            $this->makeNormalPayment();
            $this->checkPrepareResponse();
            $this->returnPaymentResponse();
        }

        else
        {
            generic::errorToDisplayEnc("Unavailable to complete the payment, as one or more provided information does not pass our validating process.");
        }
    }

    public function __destruct()
    {
        unset($this->preparedCurlData);
        unset($this->curlResponse);
        unset($this->resultsToReturn);
        parent::__destruct();
    }

    public function validateCardInfo()
    {
        if(isset($this->receivedData->amount) && isset($this->receivedData->cardNumber) && isset($this->receivedData->cv2) && isset($this->receivedData->expiryDate))
        {
            if(10 < strlen($this->receivedData->cardNumber) && strlen($this->receivedData->cardNumber) < 20 && 1 < strlen($this->receivedData->cv2) && strlen($this->receivedData->cv2) < 5)
            {
                if($this->checkExpiryDate())
                {
                    return true;
                }
            }
        }

        return false;
    }

    private function checkExpiryDate()
    {
        if(date("m/Y") === $this->receivedData->expiryDate)
        {
            return true;
        }

        else
        {
            $dateParts = explode("/", $this->receivedData->expiryDate);

            if(strtotime("01/".date("m/Y")) < strtotime("01/".$dateParts[0]."/20".$dateParts[1]))
            {
                return true;
            }
        }

        return false;
    }

    private function prepareData()
    {
        $data = new stdClass();

        $data->yourConsumerReference = $this->consumerReference = "REF".strtotime("now");
        $data->yourPaymentReference = $this->paymentReference = generic::getUniqueID("PAY");


        if($this->receivedData->action === "topupDriverBalance")
        {
            $data->yourConsumerReference = $this->consumerReference = "DRV".$this->receivedData->driverID;
            $data->yourPaymentReference = $this->paymentReference = generic::getUniqueID($this->receivedData->driverID);
        }

        $data->judoId = settings::getOption("judoID");
        $data->amount = $this->receivedData->amount;
        $data->cardNumber = $this->receivedData->cardNumber;
        $data->expiryDate = $this->receivedData->expiryDate;
        $data->cv2 = $this->receivedData->cv2;
        $data->currency = "GBP";
        $data->clientDetails = substr(strtotime("now"), -5);
        $this->preparedCurlData = $data;
    }

    private function makeNormalPayment()
    {
        $ch = curl_init(settings::getJudo()["url"]."payments");

        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($this->preparedCurlData));
        curl_setopt($ch, CURLOPT_HEADER , json_encode($this->preparedCurlData));
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 0);
        curl_setopt($ch, CURLOPT_TIMEOUT, 400);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'API-Version: 5.6',
            'Accept: application/json',
            'Authorization: Basic '.settings::getJudo()["token"],
            'Content-Type: application/json'
        ));

        $this->curlResponse = curl_exec($ch);
        curl_close($ch);
    }

    private function recordPayment()
    {
        $this->dbCon->insert("judopayTransactions", array(
            "yourPaymentReference" => $this->curlResponse->yourPaymentReference,
            "receiptId" => $this->curlResponse->receiptId,
            "customRef" => $this->curlResponse->customRef,
            "madeOn" => strtotime("now"),
            "type" => $this->curlResponse->type,
            "result" => $this->curlResponse->result,
            "message" => $this->curlResponse->message,
            "originalAmount" => $this->curlResponse->originalAmount,
            "netAmount" => $this->curlResponse->netAmount,
            "amount" => $this->curlResponse->amount,
            "cardLastFour" => $this->curlResponse->cardLastFour,
            "endDate" => $this->curlResponse->endDate,
            "cardToken" => $this->curlResponse->cardToken,
            "cardType" => $this->curlResponse->cardType,
            "consumerToken" => $this->curlResponse->consumerToken,
            "yourConsumerReference" => $this->curlResponse->yourConsumerReference
        ));
        return "";
    }

    private function recordPayment3DS()
    {
        $expiryDate = stripslashes($this->receivedData->expiryDate);
        $cardNumber = substr($this->receivedData->cardNumber, -4);
        $this->dbCon->insert("judopayTransactions", [
            "yourPaymentReference" => $this->paymentReference,
            "receiptId" => $this->curlResponse->receiptId,
            "customRef" => $this->customRef,
            "madeOn" => strtotime("now"),
            "result" => $this->curlResponse->result,
            "message" => $this->curlResponse->message,
            "originalAmount" => $this->curlResponse->originalAmount ?? "",
            "cardLastFour" => $cardNumber,
            "endDate" => $expiryDate,
            "yourConsumerReference" => $this->consumerReference,
            "paReq" => $this->curlResponse->paReq,
            "md" => $this->curlResponse->md,
            "acsUrl" => $this->curlResponse->acsUrl,
        ]);

        return "";
    }

    private function checkPrepareResponse()
    {
        $results = new stdClass();

        if(generic::isJSON($this->curlResponse))
        {
            $this->curlResponse = json_decode($this->curlResponse);
            $results->result = $this->curlResponse->result;
            $results->judopayPaymentRef = $this->paymentReference;
            $results->results = "Success";
            $results->customRef = $this->customRef;


            if($results->result === "Success")
            {
                $this->recordPayment();
                $results->message = "";
            }

            else if($results->result === "Requires 3D Secure")
            {
                $this->recordPayment3DS();
                $results->paReq = $this->curlResponse->paReq;
                $results->md = $this->curlResponse->md;
                $results->acsUrl = $this->curlResponse->acsUrl;
                $testMode = "";

                if(isset($_GET["testMode"]))
                {
                    $testMode = "&testMode=true";
                }

                $addUserAuthPar = "";
                if(isset($this->receivedData->authKey))
                {
                    $addUserAuthPar = "&authKey=".$this->receivedData->authKey;
                }

                $results->termURL = settings::getPathLoc("serverStartUrl")."?action=".$this->receivedData->action."&using=3DS&receiptID=".$this->curlResponse->receiptId.$testMode.$addUserAuthPar;
                $results->message = "We are connecting you with your bank, as your bank is requesting some additional information. Once you pass the verification your payment will be completed.";
            }

            else
            {
                $this->recordPayment();
                $results->message = $this->curlResponse->message;
            }
        }

        else
        {
            generic::errorCatch("Judopay Direct Payment", $this->curlResponse);
            $results->result = $results->results = "Declined";
            $results->message = "Our payment system is currently unavailable to complete your payment. Please try again later!";
        }

        $this->resultsToReturn = $results;
    }

    public function returnPaymentResponse()
    {
        if($this->resultsToReturn->result === "Success" || $this->resultsToReturn->result === "Requires 3D Secure")
        {
            if($this->receivedData->action === "topupDriverBalance" && $this->resultsToReturn->result === "Success")
            {
                return $this->resultsToReturn;
            }

            generic::successEncDisplay($this->resultsToReturn);
        }

        else if($this->resultsToReturn->result === "Declined")
        {
            if($this->receivedData->action === "topupDriverBalance")
            {
                return $this->resultsToReturn;
            }

            generic::errorToDisplayEnc("Sorry but your payment has been declined by your bank. This is the following message we have received:<br>".$this->resultsToReturn->message);
        }

        else
        {
            generic::errorCatch("Judopay Direct Payment", $this->curlResponse, 1);
        }

        return "";
    }
}
