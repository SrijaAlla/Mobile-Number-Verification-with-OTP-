<?php
require_once '/path/to/vendor/autoload.php'; // Loads the library

 // DONT FORGET TO CHANGE THE PATH TO VENDOR IN THE ABOVE LINE
use Twilio\Rest\Client;

// Your Account Sid and Auth Token from twilio.com/user/account
$sid = "your_sid";
$token = "your_token";
$client = new Client($sid, $token);

$call = $client->calls->create(
    "+receipient_number", "+your_number(number should be verified in twilio)",
    ["url" => "http://demo.twilio.com/docs/voice.xml"]
);

echo $call->sid;
