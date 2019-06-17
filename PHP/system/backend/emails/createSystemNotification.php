<?php


class createSystemNotification extends createEmail
{
    public function __construct($details = "", $to = [])
    {
        parent::__construct("systemNotification", ["details" => $details]);

        parent::send([
            "to" => $to,
            "from" => "Liberty Cars System <system@driversinlondon.com>",
            "subject" => "System Notification for Cabadmin (".strtotime("now").")."
        ]);
    }
}