<?php


class createEmail extends MonoBehaviour
{
    private $content;
    private $allowSending = true;

    public function __construct($fileName, $data = array())
    {
        parent::__construct();

        $path = "templates/emails/".$fileName.".php";
        if(file_exists($path))
        {
            /** @noinspection PhpIncludeInspection */
            $this->content = file_get_contents($path);

            if(count((array)$data)>0)
            {
                foreach((array)$data as $key => $val)
                {
                    $this->content = str_replace("{{".$key."}}",$val, $this->content);
                }
            }

            return true;
        }

        $this->allowSending = false;
        generic::errorCatch("displayLayout", "The filename '" . $fileName . "' does not exist on the templates folder!");
        return false;
    }


    public function send($headers, $priority = 5)
    {
        if(!settings::getOption("enabledEmailSystem"))
        {
            generic::errorCatch("Email Send", "The configuration of send has been disabled. Therefore, the email is not going to be sent!");
            return false;
        }

        $finalHeaders = [];

        if(!isset($headers["from"]))
        {
            $headers["from"] = settings::getOption("defaultEmailFrom");
        }

        if(count($headers["to"]) < 1 || !isset($headers["subject"]))
        {
            generic::errorCatch("Either from, to or subject is not set for executing the email", 2);
        }

        array_push($finalHeaders, "MIME-Version: 1.0");
        array_push($finalHeaders, "Content-Type: text/html; charset=utf-8");
        array_push($finalHeaders, "From: ".$headers["from"]);

        if(isset($headers["reply"]) && $headers["reply"] !== "")
        {
            array_push($finalHeaders, "Reply-To: " . $headers["reply"]);
        }

        else
        {
            array_push($finalHeaders, "Reply-To: " . $headers["from"]);
        }

        $to = implode(",", $headers["to"]);

        if(isset($headers["bcc"]) && count($headers["bcc"]) > 0)
        {
            array_push($finalHeaders, "Bcc: ". implode(",", $headers["bcc"]));
        }

        if(isset($headers["cc"]) && count($headers["cc"]) > 0)
        {
            array_push($finalHeaders, "Cc: ". implode(",", $headers["cc"]));
        }

        $subject = $headers["subject"];
        unset($headers["subject"]);

        $finalHeaders = implode("\r\n", $finalHeaders) . "\r\n";

        $this->dbCon->getDBCon()->query("INSERT INTO `emailPosting` (`sendTo`, `subject`, `content`, `headers`, `dateTime`, `priority`) VALUES 
        ('$to', '".$subject."', '".$this->content."', '$finalHeaders', '".strtotime("now")."', '$priority')");

        return true;

        /*if(mail($to, $headers["subject"], $this->content, $finalHeaders))
        {
            return true;
        }*/
    }

    public function __destruct()
    {
        parent::__destruct();
    }
}