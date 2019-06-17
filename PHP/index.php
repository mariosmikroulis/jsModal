<?php

    header("Access-Control-Allow-Origin: *");
    date_default_timezone_set("Europe/London");
    set_time_limit(30);

    require_once ("server.php");
    $startServerSystem = new startSystem();
    unset($startServerSystem);