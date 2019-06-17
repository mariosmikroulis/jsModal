<?php
	
	class createDriverApplication extends MonoBehaviour
	{
		public function __construct()
		{
		    parent::__construct();

			$recordID = $this->recordDetails();
			
			if($recordID!==null)
			{
				$this->markFilesAsUsed();
                $this->sendEmail();

                $success = new stdClass();
                $success->id = $recordID;
                generic::successEncDisplay($success);
			}
		}
		
		public function __destruct()
		{
			parent::__destruct();
		}
		
		private function recordDetails()
		{
			try
			{
				$tmpDetails = $this->receivedData->driverDetails;
				$tmpDocs = $this->receivedData->driverDocs;
				$stmt = $this->dbCon->getDBCon()->prepare("INSERT INTO `newDriverApplications` (`fullName`, `emailAddress`, `mobileNumber`, `NINo`, `DLF`, `DLB`, `PHDL`, `PHVL`, `MOT`, `PHI`, `PSP`, `VHA`, `appSubDate`, `ipAddress`) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?)");

                $vha = null;

				if($tmpDocs->VHA !== "")
                {
                    $vha = $tmpDocs->VHA;
                }

                // set parameters and execute
                $uploadedDate = strtotime("now");
                $ipAddress = $_SERVER["REMOTE_ADDR"];

				$stmt->bind_param("ssssssssssssis", $tmpDetails->driverName, $tmpDetails->driverEmail, $tmpDetails->driverMobile, $tmpDetails->driverNINo, $tmpDocs->DLF, $tmpDocs->DLB, $tmpDocs->PHDL, $tmpDocs->PHVL, $tmpDocs->MOT,$tmpDocs->PHI,$tmpDocs->PSP, $vha, $uploadedDate, $ipAddress);

				$rc = $stmt->execute();

				
				if ($stmt->insert_id)
					return $stmt->insert_id;
				
				else
				{
					throw new RuntimeException("There was an error with your submission on our side. If you see this message, please come in contact with us!");
				}
				
				$stmt->close();
			}
			
			catch (RuntimeException $e)
			{
                generic::errorCatch("Driver application register", $e->getMessage(), 1);
			}
		}
		
		private function markFilesAsUsed()
		{
 			try
			{
				$tmpDetails = $this->receivedData->driverDetails;
				$tmpDocs = $this->receivedData->driverDocs;
				$stmt = $this->dbCon->getDBCon()->prepare("UPDATE `uplodatedFiles` SET `isFileUsed`='1' WHERE `md5Enc`=?");

				$md5Enc=null;

				$stmt->bind_param("s", $md5Enc);
				
				foreach($this->receivedData->driverDocs as $key=>$value)
				{
					// set parameters and execute
					$md5Enc = $value;
					$stmt->execute();
				}
				
				$stmt->close();
			}
			
			catch (RuntimeException $e)
			{
                generic::errorCatch("Driver application Mark as Used", $e->getMessage(), 1);
			}
		}
		
		private function sendEmail()
		{
		    if(!settings::getOption("enabledEmailSystem"))
            {
                return false;
            }

            $generateEmail = new createEmail("createDriverApplication", [
                "driverName" => $this->receivedData->driverDetails->driverName,
                "mobileNumber" =>  $this->receivedData->driverDetails->driverMobile
            ]);

            $generateEmail->send([
                "to" => [$this->receivedData->driverDetails->driverEmail],
                "reply" => "Liberty Cars Driver <driver@minicabsinlondon.com>",
                "subject" => "Confirmation Of Your New Driver Application"
            ]);

            new createSystemNotification("A new driver has applied to become a part of our company!", ["Liberty Cars Driver <driver@minicabsinlondon.com>"]);
		}
	}