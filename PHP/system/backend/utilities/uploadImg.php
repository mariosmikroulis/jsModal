<?php

	class uploadImg
	{		
		private $fileExtentions;
		private $errorMessages;
		private $uploadLimit = 300;
		private $fileSizeLimit = 200000000;
		private $dbCon;
		
		public function __construct()
		{
			$this->dbCon = new mysqlConnection();
			
			$this->fileExtentions = $fileExtentions = [
				'jpg' => 'image/jpeg',
				'jpeg' => 'image/jpeg',
				'png' => 'image/png',
				'gif' => 'image/gif',
				'bmp' => 'image/x-ms-bmp',
				'pdf' => 'application/pdf'
            ];
			
			$this->errorMessages = array(
				'Invalid parameters.',
				'No file sent.',
				'Exceeded filesize limit.',
				'Unknown errors.',
				'Exceeded filesize limit.',
				'Invalid file format.',
				'Failed to move uploaded file.',
				'You have used the daily times of uploading a day.',
				'There was a problem while uploading your file. Please forward this error 224'
			);
			
			if($this->checkAllowance() && "" !== $fileName = $this->uploadFileToDirectory())
			{
				$display = new stdClass();
                $display->fileID = $this->insertFileDetails($fileName);

				generic::successEncDisplay($display);
			}
		}
		
		public function __destruct()
		{
			unset($this->dbCon);
		}
		
		private function checkAllowance()
		{
			try
			{
				$stmt = $this->dbCon->getDBCon()->prepare("SELECT * FROM `uplodatedFiles` WHERE (`uploadedDate` BETWEEN ? AND ?) AND `ipAddress`=?");

				// set parameters and execute
				$ipAddress = $_SERVER["REMOTE_ADDR"];
				$startDate = strtotime(date("d-m-Y"));
				$finishDate = strtotime(date('Y-m-d', strtotime('+1 day')));
                $stmt->bind_param("sii", $ipAddress, $startDate, $finishDate);
				
				$stmt->execute();
				$stmt->store_result();
				
				if ($stmt->num_rows < $this->uploadLimit)
					return true;
				
				else
				{
					throw new RuntimeException(fixedError(7));
				}

				$stmt->close();
			}
			
			catch (RuntimeException $e)
			{
				generic::errorToDisplayEnc($e->getMessage());
				return false;
			}
		}
		
		private function fixedError($errorCode)
		{
			if(gettype($errorCode)==="integer")
				$errorMessage = $this->errorMessages[$errorCode];
			
			else
				$errorMessage = "Unknown";
			
			return $errorMessage;
		}
		
		private function uploadFileToDirectory()
		{
			try
			{
                generic::debugging(json_encode($_FILES["upfile"]));
				// Undefined | Multiple Files | $_FILES Corruption Attack
				// If this request falls under any of them, treat it invalid.
				if(!isset($_FILES['upfile']['error']) || is_array($_FILES['upfile']['error']))
				{
					throw new RuntimeException($this->fixedError(0));
				}

				// Check $_FILES['upfile']['error'] value.
				switch ($_FILES['upfile']['error'])
				{
					case UPLOAD_ERR_OK:
						break;
					case UPLOAD_ERR_NO_FILE:
						throw new RuntimeException($this->fixedError(1));
					case UPLOAD_ERR_INI_SIZE:
					case UPLOAD_ERR_FORM_SIZE:
						throw new RuntimeException($this->fixedError(2));
					default:
						throw new RuntimeException($this->fixedError(3));
				}

				// You should also check filesize here. 
				if ($_FILES['upfile']['size'] > $this->fileSizeLimit)
				{
					throw new RuntimeException($this->fixedError(4));
				}

				// DO NOT TRUST $_FILES['upfile']['mime'] VALUE !!
				// Check MIME Type by yourself.
				$finfo = new finfo(FILEINFO_MIME_TYPE);

				
				if (false === $ext = array_search($finfo->file($_FILES['upfile']['tmp_name']), $this->fileExtentions, true))
				{
					throw new RuntimeException($this->fixedError(5));
				}

				// You should name it uniquely.
				// DO NOT USE $_FILES['upfile']['name'] WITHOUT ANY VALIDATION !!
				// On this example, obtain safe unique name from its binary data.
				$newName = sha1($_FILES['upfile']['tmp_name'].date("dmYHism"));
				if (!move_uploaded_file($_FILES['upfile']['tmp_name'], sprintf('./uploads/%s.%s', $newName, $ext)))
				{
					throw new RuntimeException($this->fixedError(6));
				}
			}

			catch (RuntimeException $e)
			{
				generic::errorToDisplayEnc($e->getMessage());
				return "";
			}
			
			return $newName.".".$ext;
		}
		
		private function insertFileDetails($fileName)
		{
			try
			{
				$stmt = $this->dbCon->getDBCon()->prepare("INSERT INTO `uplodatedFiles` (md5Enc, fileName, uploadedDate, ipAddress) VALUES (?,?,?,?)");
				


				// set parameters and execute
				$md5Enc = md5($fileName);
				$uploadedDate = strtotime("now");
				$ipAddress = $_SERVER["REMOTE_ADDR"];
                $stmt->bind_param("ssis", $md5Enc, $fileName, $uploadedDate, $ipAddress);
				
				//$stmt->execute();
				if ($stmt->execute())
				{
					return $md5Enc;
				}
				
				else
				{
					throw new RuntimeException(fixedError(7));
					$md5Enc = "";
				}
				
				$stmt->close();
				return $md5Enc;
				
			}
			
			catch (RuntimeException $e)
			{
                generic::errorCatch("DB uploading mage", $e->getMessage());
			}
		}
	}
?>