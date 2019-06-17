<?php

/**
 * Class generic
 */
class generic
{
    /**
     * @param $str
     */
    public static function debugging($str)
    {
        file_put_contents("fileData/debugging.txt", file_get_contents("fileData/debugging.txt")."\n\n\n".date("d/m/Y H:i:s")."\n".$str."\n".self::get_calling_function());
    }

    /**
     * @param $category
     * @param string $details
     * @param int $throwError
     */
    public static function errorCatch($category, $details = "", $throwError = 0)
    {
        $errorToRecord = "=========================================================================\n";
        $errorToRecord .= "Subject: \n".$category."\n\n";
        $errorToRecord .= "Date: \n".date("d-m-Y H:i:ss")."\n\n";
        $errorToRecord .= "Request: \n".json_encode(self::getReceivedData())."\n\n";
        $errorToRecord .= "Error Details: \n". $details."\n\n";
        $errorToRecord .= "Tracing: \n". self::get_calling_function()."\n\n";
        $errorToRecord .= "\n\n\n";

        file_put_contents("fileData/".settings::getOption("errorCatchFileName"), $errorToRecord.file_get_contents("fileData/".settings::getOption("errorCatchFileName")));

        if($throwError > 0)
        {
            if($throwError === 1)
            {
                $text = "There is an issue with our system, and we are currently trying to resolve it, as soon as possible. Please, try again another time!";
            }

            else if($throwError === 2)
            {
                $text = $details;
            }

            else
            {
                $text = $throwError;
            }

            self::errorToDisplayEnc($text);
        }
    }


    /**
     * @return string
     */
    public static function get_calling_function()
    {
        // a function x has called a function y which called this
        $debug = debug_backtrace();
        $finalResult = "";

        for ($i = count($debug)-1; $i > 1; $i--) {
            $caller = $debug[$i];
            $r = $caller['function'] . '()';

            if (isset($caller['class'])) {
                $r .= ' in ' . $caller['class'];
            }

            if (isset($caller['object'])) {
                $r .= ' (' . get_class($caller['object']) . ')';
            }

            $finalResult .= $r . ", ";
        }

        substr($finalResult, 0, -2);

        return $finalResult;
    }

    /**
     * @param $passData
     * @return array
     */
    public static function toPostArray($passData)
    {
        $newData = [];

        if (strpos($passData, '=') !== false)
        {
            if (strpos($passData, '&') !== false)
            {
                $splitData = explode("&", $passData);

                for($i = 0; $i < count($splitData) -1; $i++)
                {
                    $temp = explode("=", $passData);
                    $newData[$temp[0]] = $temp[1];
                }
            }

            else
            {
                $temp = explode("=", $passData);
                $newData = [$temp[0], $temp[1]];
            }
        }


        self::errorToDisplayEnc("This is not a POST Array format.");
        return $newData;
    }


    /**
     * @param $passData
     * @return bool|mixed|SimpleXMLElement|stdClass|string
     */
    public static function toObject($passData)
    {
        if(self::isJSON($passData)) {
            return json_decode($passData);
        }

        else if(self::isXML($passData))
        {
            $results = simplexml_load_string($passData);
            //unset($results());
            return $results;
        }

        else if(self::isBase64($passData))
        {
            $results = base64_decode(substr($passData, 2));

            if(self::isJSON($results) || self::isXML($passData))
            {
                return self::toObject($results);
            }
        }

        else if(gettype($passData) === "array")
        {
            return json_decode(json_encode($passData), FALSE);
        }

        else if(self::isPostArray($passData))
        {

            $object = new stdClass();
            foreach ($passData as $key => $value)
            {
                $object->$key = $value;
            }

            return $object;
        }

        else if(is_object($passData))
        {
            return $passData;
        }

        self::errorToDisplayEnc("Unavailable to transform this data to Object.");
        return "";
    }


    /**
     * @param $passData
     * @return false|mixed|string|null
     */
    public static function toJSON ($passData)
    {
        if(gettype($passData) == "object" || gettype($passData) == "array")
            return json_encode($passData);

        if(self::isXML($passData))
            return json_decode(json_encode(simplexml_load_string($passData)),TRUE);

        self::errorToDisplayEnc("The parameter passing has failed during to a miss configuration.");
        return null;
    }


    /**
     * @param $passData
     * @return bool
     */
    public static function isJSON($passData)
    {

        if(gettype($passData)==="string") {
            $results = json_decode($passData);

            if (json_last_error() === JSON_ERROR_NONE) {
                return true;
            }
        }

        return false;
        //return (json_last_error() == JSON_ERROR_NONE);
    }


    /**
     * @param $passData
     * @return bool
     */
    public static function isXML($passData)
    {
        if(gettype($passData)==="string") {
            libxml_use_internal_errors(true);

            $doc = simplexml_load_string($passData);

            if (!$doc) {
                libxml_get_errors();

                libxml_clear_errors();
                return false;
            }
            return true;
        }

        return false;
    }

    /**
     * @param $passData
     * @return bool
     */
    public static function isBase64($passData)
    {
        if(gettype($passData)==="string")
        {
            if (substr($passData, 0, 2) === 'bs') {
                return true;
            }
        }

        return false;
    }

    /**
     * @param $passData
     * @return string
     */
    public static function toBase64JSON($passData)
    {
        return base64_encode(json_encode($passData));
    }


    /**
     * @param $passData
     * @return bool
     */
    public static function isPostArray($passData)
    {
        if (strpos($passData, '=') !== false)
        {
            return true;
        }

        return false;
    }

    /**
     * @param string $phrase
     * @return bool|string
     */
    public static function getUniqueID($phrase = "")
    {
        return substr(md5($phrase.date("dmYHisv").rand()), 16);
    }

    /**
     * @param $reason
     */
    public static function errorToDisplay($reason)
    {
        if(gettype($reason)==="string")
            $errorMessage = $reason;

        else
            $errorMessage = "Unknown";

        $errorMsg = new stdClass();
        $errorMsg->results = "fail";
        $errorMsg->reason = $errorMessage;
        //if(isset($_GET["testMode"]) && $_GET["testMode"] === date("Y"))
        $errorMsg->locationCalled = self::get_calling_function();

        die(json_encode($errorMsg));
    }


    /**
     * @param $reason
     */
    public static function errorToDisplayEnc($reason)
    {
        if(gettype($reason)==="string")
            $errorMessage = $reason;

        else
            $errorMessage = "Unknown";

        $errorMsg = new stdClass();
        $errorMsg->results = "fail";
        $errorMsg->reason = $errorMessage;
        $errorMsg->dateTime = date("d/m/Y H:i:s");
        //if(isset($_GET["testMode"]) && $_GET["testMode"] === date("Y"))
        $errorMsg->locationCalled = self::get_calling_function();

        die(generic::toBase64JSON($errorMsg));
    }

    /**
     * @param $data
     */
    public static function successEncDisplay($data)
    {
        $successMsg = new stdClass();
        $successMsg->results = "Success";

        if(gettype($data)==="string")
            $successMsg->message = $data;

        else if(gettype($data) === "object" || gettype($data) === "array")
        {
            $successMsg->data = $data;
        }

        else
            $successMsg->message = "Unknown";


        $successMsg->dateTime = date("d/m/Y H:i:s");

        if(isset($_GET["testMode"]) && $_GET["testMode"] === date("Y"))
            $successMsg->locationCalled = self::get_calling_function();

        die(self::toBase64JSON($successMsg));
    }

    /**
     * @param $results
     * @param $data
     */
    public static function iframeEncDisplay($results, $data)
    {
        $successMsg = new stdClass();
        if($results == 1) {
            $successMsg->results = "Success";
        }

        else
        {
            $successMsg->results = "Fail";
        }

        if(gettype($data)==="string")
            $successMsg->message = $data;

        else if(gettype($data) === "object" || gettype($data) === "array")
        {
            $successMsg->data = $data;
        }

        else
            $successMsg->message = "Unknown";


        $successMsg->dateTime = date("d/m/Y H:i:s");

        if(isset($_GET["testMode"]) && $_GET["testMode"] === date("Y"))
            $successMsg->locationCalled = self::get_calling_function();

        self::displayLayout("iFrameReply", ["iFrameData" => self::toBase64JSON($successMsg)]);
    }

    /**
     * @param $fileName
     * @param array $data
     */
    public static function displayLayout($fileName, $data = [])
    {
        if(file_exists("templates"."/".$fileName.".php"))
        {
            /** @noinspection PhpIncludeInspection */
            $fileContent = file_get_contents("templates"."/"."/".$fileName.".php");

            if(count((array)$data)>0)
            {
                foreach((array)$data as $key => $val)
                {
                    $fileContent = str_replace("{{".$key."}}",$val, $fileContent);
                }
            }

            die($fileContent);
        }

        self::errorCatch("displayLayout", "The filename '" . $fileName . "' does not exist on the templates folder!");
    }

    /**
     * @param $passData
     * @return string
     */
    public static function filter_input($passData)
    {
        try {
            if(gettype($passData) === "boolean" || gettype($passData) === "integer" || gettype($passData) === "double" || gettype($passData) === "string")
            {
                return htmlspecialchars(stripslashes(trim($passData)));
            }
        }

        catch (RuntimeException $e)
        {
            return "";
        }

        return "";
    }

    /**
     * @return bool
     */
    private static function allowSpecificGetActions()
    {
        $allowance = ["topupDriverBalance", "uploadImg", "uploadImgDemo"];

        if(isset($_GET["action"]) && array_search($_GET["action"], $allowance) !== FALSE)
        {
            return true;
        }

        return false;
    }

    /**
     * @return bool|mixed|SimpleXMLElement|stdClass|string
     */
    public static function getReceivedData()
    {
        $receivedData = file_get_contents('php://input');

        // Checks if client has sent any data whatsoever.
        if($receivedData!=="") {
            // This checks if the received Data is JSON, and returns it into Object.
            if (self::isJSON($receivedData)) {
                $dataToReturn = self::toObject($receivedData);
            }

            // This checks if the received Data is XML, and returns it into Object.
            else if (self::isXML($receivedData)) {
                $dataToReturn = self::toObject($receivedData);
            }

            else if (self::isBase64($receivedData))
            {
                $dataToReturn = self::toObject($receivedData);
            }

            // This checks if the received Data is POST, and returns it into Object.
            else if(isset($_POST["action"]))
            {
                $dataToReturn = self::toObject($_POST);
            }

            else if(self::allowSpecificGetActions())
            {
                if(count($_POST)>1)
                {
                    $dataToReturn = self::toObject($_POST);
                    $dataToReturn->action = $_GET["action"];
                }

                else
                {
                    $dataToReturn = new stdClass();
                    $dataToReturn->action = $_GET["action"];
                }
            }
        }

        else
        {
            if(self::allowSpecificGetActions())
            {
                if(count($_POST)>0)
                {
                    $dataToReturn = self::toObject($_POST);
                    $dataToReturn->action = $_GET["action"];
                }

                else
                {
                    $dataToReturn = new stdClass();
                    $dataToReturn->action = $_GET["action"];
                }
            }

            else if(isset($_POST["action"]))
            {
                $dataToReturn = self::toObject($_POST);
            }
        }


        if(isset($dataToReturn) && gettype($dataToReturn)!=="NULL")
        {
            if(!isset($dataToReturn->action) && isset($_GET["action"]) && $_GET["action"] !=="")
            {
                $dataToReturn->action = $_GET["action"];
            }

            return $dataToReturn;
        }

        self::errorToDisplayEnc("Our service is not accepting GET data.");
        return "";
    }
}