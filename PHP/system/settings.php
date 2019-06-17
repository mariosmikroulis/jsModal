<?php

class settings
{
    // Database settings.
    private static $dbDetails = ["host" => "127.0.0.1", "user" => "root", "pass" => "", "name" => "n/a"];

    private static $serverOnline = true;
    private static $serverShutdownMessage = "We are on undergoing maintenance, which can last up to 48 hours. Please try again.";


    // Reseller Login settings
    private static $enabledResellerLogin = true;
    private static $resellerLoginDisableMessage = "You are unable to login, as we are on undergoing maintenance of our system. Please try again later!";


    // Driver Portal Settings.
    private static $enabledDriverPortalLogin = true;
    private static $driverPortalLoginDisableMessage = "You are unable to login, as we are on undergoing maintenance of our system. Please try again later!";

    // Driver Portal Settings.
    private static $enabledAccountsLogin = true;
    private static $accountsLoginDisableMessage = "You are unable to login, as we are on undergoing maintenance of our system. Please try again later!";

    private static $encPasswordKey = "LibertyCars";


    // Text file, where try/catch will be storing the issue.
    private static $errorCatchFileName = "errorCatch.txt";


    // Ghost Server URL System.
    private static $ghostServerURL = "...";
    private static $ghostUser = "none";
    private static $ghostPass = "none";

    private static $defaultEmailFrom = "Liberty Cars System <system@driversinlondon.com>";
    private static $defaultEmailReplyTo = "Liberty Cars System <system@driversinlondon.com>";


    // This is if we wish the email system to be on.
    private static $enabledEmailSystem = true;


    // General file locations for web management.
    private static $locations = [
        "serverMainUrl" => "https://www.driversinlondon.com",
        "serverMainUrlPath" => "https://server.driversinlondon.com/",
        "serverMainPath" => "/home/minicabs/server.driversinlondon.com",
        "serverSystemPath" => "/home/minicabs/server.driversinlondon.com/system",
        "serverHtmlPath" => "/home/minicabs/server.driversinlondon.com/html",
        "serverStartUrl" => "https://server.driversinlondon.com/",
        "driverportalUrl" => "https://driverportal.driversinlondon.com",
        "resellerUrl" => "https://reseller.driversinlondon.com",
        "cabadminUrl" => "https://cabadmin.minicabsinlondon.com",
        "mainBookingsUrl" => "https://www.minicabsinlondon.com"
    ];


    // JudoPay Settings
    private static $judoLiveURL = "https://gw1.judopay.com/transactions/";
    private static $judoSandURL = "https://gw1.judopay-sandbox.com/transactions/";
    private static $judoID = "123";
    private static $judoLiveToken = "...";
    private static $judoSandToken = "...";


    public static function getOption($option)
    {
        try
        {
            return self::$$option;
        }

        catch(RuntimeException $e)
        {
            generic::errorCatch("Settings", $option."\n".$e->getMessage());
        }

        return null;
    }

    public static function getJudo()
    {
        $return = array("id"=> self::$judoID);

        $return["token"] =  self::$judoLiveToken;
        $return["url"] =  self::$judoLiveURL;

        if(isset($_GET["testMode"]))
        {
            $return["token"] =  self::$judoSandToken;
            $return["url"] =  self::$judoSandURL;
        }

        return $return;
    }

    public static function getPathLoc($pathLocReq)
    {
        // It checks if the requested path exists.
        if (array_key_exists($pathLocReq, self::$locations)) {
            // When that key exists, it returns the value within.
            return self::$locations[$pathLocReq];
        }

        // When the if statement above becomes false, the function will return Undefined results.
        return "Undefined";
    }
}