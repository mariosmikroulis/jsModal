<?php
/**
 * Created by PhpStorm.
 * User: xfran
 * Date: 05/04/2019
 * Time: 18:19
 */

$ch = curl_init();

curl_setopt($ch, CURLOPT_URL,"https://server.driversinlondon.com/");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(["action" => "postEmails"]));
curl_setopt($ch, CURLOPT_HTTPHEADER, ["Content-Type: application/json"]);

curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

$server_output = curl_exec($ch);

file_put_contents("replyTest.txt", $server_output);
echo $server_output;

curl_close ($ch);