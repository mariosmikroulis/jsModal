<?php


/**
 * Class startSystem
 */
class startSystem
{
    private $task;
    private $dbCon;

    public function __construct()
    {
        $this->require_all("system");
        $this->recordRequests();

        if(settings::getOption("serverOnline"))
        {
            $this->dbCon = new mysqlConnection();
            $this->startActionService();
        }

        else
        {
            generic::errorToDisplayEnc(settings::getOption("serverShutdownMessage"));
        }
    }

    public function startActionService()
    {
        $action = $_GET["action"] ?? $action = generic::getReceivedData()->action ?? "";

        if($this->dbCon->count("serverActions", ["actionRef" => $action, "isItEnabled" => 1]) > 0)
        {
            if($action === "uploadImg" || $action === "uploadImgDemo")
            {
                header('Content-Type: text/plain; charset=utf-8');
            }

            $this->task = new $action();
        }

        else {
            generic::errorToDisplayEnc("Unknown this action.");
        }
    }


    /**
     * @param $dir
     */
    private function require_all($dir)
    {

        // require all php files
        $scan = glob($dir . DIRECTORY_SEPARATOR . "*");
        $folders = [];

        foreach ($scan as $path)
        {
            if (preg_match('/\.php$/', $path))
            {
                /** @noinspection PhpIncludeInspection */
                require_once($path);
                continue;
            }

            $folders[] = $path;
        }

        for($i=0; $i<count($folders); $i++)
        {
            $this->require_all($folders[$i]);
        }
    }

    // This records all the incoming data from the client-sides.
    // It allows to monitor everything in a better way, to detect quick issues that might appear.
    private function recordRequests()
    {
        if(generic::getReceivedData()->action === "postEmails")
        {
            return false;
        }

        // This is the name of the file, where the information will be recorded.
        $file = "fileData/request.txt";
        $lineCount = 0;
        $handle = fopen($file, "r") or die("Unable to open file!");

        while(!feof($handle))
        {
            $line = fgets($handle);
            $lineCount++;
        }

        fclose($handle);

        // Check if the lines recorded is more than 30k lines. This will ensure that the stored data will not delay the server,
        // and the browser is not forced to just download the txt file because of its size.
        if($lineCount<30000) {
            // Picks up the old and existing data of request.txt file
            $recordedData = file_get_contents($file);
        }

        // If the old data has more than 30k lines, it will not pick the stored data.
        else {
            $recordedData = "";
        }

        // Picks up the received data that was sent by the client.
        $receivedData = file_get_contents('php://input');

        if(generic::toBase64JSON($receivedData))
        {
            $receivedData .= "\n".generic::toJSON(generic::getReceivedData());
        }

        // Sets the new data within the file.
        $newData = "Date & Time ".date("d-m-Y H:i").": ".$receivedData."\n\n";

        $finalData = $newData.$recordedData;

        // Records all the upcoming requests and stores it in the request.txt together with the old data.
        file_put_contents($file, $finalData);

        unset($finalData);
        unset($recordedData);
        unset($receivedData);
        unset($newData);

         return true;
    }


    public function __destruct()
    {
        if(gettype($this->task)==="object")
            unset($this->task);

        unset($this->dbCon);

        exit;
    }
}