<?php

class getUploadedFiles extends userBehaviour
{
    public function __construct()
    {
        parent::__construct();

        $this->restrictActionTo(["driver"]);
        $this->returnFileSubmission();
        generic::successEncDisplay("The file has been uploaded successfully.");
    }


    public function returnFileSubmission()
    {
        $getUploadHistory = $this->dbCon->fetch("driverFileUploadHistory", ["driverID"=>$this->userData["driverID"]], true, "ORDER BY `fileHistoryID` DESC LIMIT 20");

        $dataDisplay = [];

        for($index = 0; $index < count($getUploadHistory); $index++)
        {
            $getFileDetails = $this->dbCon->fetch("uplodatedFiles", ["fileID" => $getUploadHistory[$index]["fileID"]]);

            $tempData = new stdClass();
            $tempData->row = $index;
            $tempData->fileName = $getFileDetails["fileName"];
            $tempData->uploadedDate = "Unknown";

            if($getFileDetails["uploadedDate"] !== 0)
            {
                $tempData->uploadedDate = date("d/m/Y H:i", $getFileDetails["uploadedDate"]);
            }

            $tempData->docType = $getUploadHistory[$index]["docType"];
            $tempData->fileStatus = $getUploadHistory[$index]["fileStatus"];
            $tempData->description = $getUploadHistory[$index]["decisionDescription"];

            $dataDisplay[] = $tempData;
        }

        generic::successEncDisplay($dataDisplay);
    }


    public function __destruct()
    {
        parent::__destruct();
    }
}
