<?php


/**
 * Class ticketSystem
 */
class ticketSystem extends userBehaviour
{
    private $ticketCat = [];

    public function __construct()
    {
        parent::__construct();

        $this->assignTicketCat();
        $this->stopUnathTicketReq();
    }

    public function __destruct()
    {
        parent::__destruct();
    }

    private function assignTicketCat()
    {
        $this->ticketCat = [
            "driverGeneric" => [
                "driver", "staff"
            ],
            "lostFoundReport" => [
                "customer", "staff"
            ],
            "lostFoundAck" => [
                "staff"
            ],
            "complainReport" => [
                "customer", "staff"
            ],
            "complainAck" => [
                "staff"
            ]
        ];
    }

    /**
     * @return array
     */
    public function getTicketTypes()
    {
        return $this->ticketCat;
    }


    /**
     * @return bool
     */
    public function stopUnathTicketReq()
    {
        try
        {
            if(isset($this->receivedData->ticketType) && array_key_exists($this->receivedData->ticketType, $this->ticketCat))
            {
                if(in_array($this->userData["userType"], $this->ticketCat[$this->receivedData->ticketType])) {
                    return true;
                }
            }

            throw new RuntimeException();
        }

        catch(RuntimeException $e)
        {
            generic::errorToDisplayEnc("You are not authorised to place this type of ticket.");
            return false;
        }
    }
}