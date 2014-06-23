<?php

require 'vendor/autoload.php';

$client = new \Google_Client();
// Replace this with your application name.
$client->setApplicationName("Client_Library_Examples");
// Replace this with the service you are using.
$service = new \Google_Service_Books($client);

// This file location should point to the private key file.
$key = file_get_contents($key_file_location);
$cred = new \Google_Auth_AssertionCredentials(
// Replace this with the email address from the client.
    $service_account_name,
    // Replace this with the scopes you are requesting.
    array('https://www.googleapis.com/auth/books'),
    $key
);
$client->setAssertionCredentials($cred);