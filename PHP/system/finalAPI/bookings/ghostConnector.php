<?php

class ghostConnector
{
    private $requestAPI = "";
    private $responseAPI = "";
    private $tries = 5;
    private $return;

    public function __construct($requestAPI)
    {
        $this->return = new stdClass();
        $this->requestAPI = $requestAPI;
    }

    public function makeRequest($requestXML)
    {
        // create both cURL resources
        $api_request = "https://cxs.autocab.net/api/agent";
        $ac = $response = [];
        $active = null;
        $loopMaxXML = count($requestXML);
        $mh = curl_multi_init();

        for($i=0; $i < $loopMaxXML; $i++)
        {
            $ac[$i] = curl_init();

            $headers = [
                "Content-type: text/xml",
                "Content-length: " . strlen($requestXML[$i]),
                "Connection: close",
            ];

            curl_setopt($ac[$i], CURLOPT_URL, $api_request);
            curl_setopt($ac[$i], CURLOPT_HEADER, 0);
            curl_setopt($ac[$i], CURLOPT_RETURNTRANSFER, 1);
            //curl_setopt($ac[$i], CURLOPT_TIMEOUT, 10);
            curl_setopt($ac[$i], CURLOPT_POST, true);
            curl_setopt($ac[$i], CURLOPT_POSTFIELDS, $requestXML[$i]);
            curl_setopt($ac[$i], CURLOPT_HTTPHEADER, $headers);
            curl_setopt($ac[$i], CURLINFO_HEADER_OUT, true);

            curl_multi_add_handle($mh,$ac[$i]);
        }

        $active = null;
        /* do {
            $mrc = curl_multi_exec($mh, $active);
            usleep(100); // Maybe needed to limit CPU load (See P.S.)
        } while ($active); */

        // Execute the multi handle
        do {
            $status = curl_multi_exec($mh, $active);
        } while ($status === CURLM_CALL_MULTI_PERFORM || $active);

        //print_r (curl_getinfo($ac[0]));


        for($k=0; $k < $loopMaxXML; $k++)
        {
            $response[$k] = curl_multi_getcontent($ac[$k]);

            curl_multi_remove_handle($mh, $ac[$k]);
        }

        curl_multi_close($mh);

        return $response;
    }

    public function __destruct()
    {
        unset($this->requestAPI);
    }
}