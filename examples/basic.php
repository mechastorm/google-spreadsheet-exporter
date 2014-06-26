<?php

require '../vendor/autoload.php';
require '../src/Mechastorm/Google/ApiHelper.php';
require '../src/Mechastorm/Google/Spreadsheet/Data/Exporter.php';
require '../src/Mechastorm/Google/Spreadsheet/Data/FormatWriters/LocalePhpFile.php';
require '../src/Mechastorm/Google/Spreadsheet/Data/Transformers/LocalePhpArray.php';

use Mechastorm\Google\ApiHelper;
use Mechastorm\Google\Spreadsheet\Data\Exporter;
use Mechastorm\Google\Spreadsheet\Data\FormatWriters\LangPhpWriter;
use Mechastorm\Google\Spreadsheet\Data\Transformers\LocalePhpArray;

// Get the json encoded access token.
$authResponse = ApiHelper::getAuthByServiceAccount(
    array(
        'application_name' => 'name_of_application',
        'client_id' => 'service_account_client_id',
        'client_email' => 'service_account_client_email',
        'key_file_location' => 'location-to-your-private-key-p12-file',
    )
);
$accessToken = $authResponse->access_token;

echo 'Service Account Access Token: ' . $accessToken;

$gSSExporter = new Exporter(array(
    'access_token' => $accessToken,
    'spreadsheet_name' => 'google-spreadsheet-exporter Sample Spreadsheet' // Must Match Exactly
));
$gSSExporter->setWorksheets();

$transformer = new LocalePhpArray(array(
    'locales' => array(
        'en_GB', // Must match the columns on the spreadsheet
        'fr_CA',
        'my_MY',
        'ja_JP',
    ),
));
$outputFolder = '../output';
$writer = new LangPhpWriter(array(
    'path' => $outputFolder,
));



$gSSExporter->processWorksheets(
    array(
        'Web Copy', // Must match exactly!
    ),
    $transformer,
    $writer
);

echo "Done - please check {$outputFolder} for the files\n";