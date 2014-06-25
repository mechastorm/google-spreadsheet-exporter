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
    'spreadsheet_name' => 'name_of_spreadsheet'
));

$transformer = new LocalePhpArray(array(
    'locales' => array(
        'en_US', // Must match the columns on the spreadsheet
        'en_GB', // Must match the columns on the spreadsheet
    ),
));
$outputFolder = '../output';
$writer = new LangPhpWriter(array(
    'path' => $outputFolder,
));



$gSSExporter->processWorksheets(
    array(
        'name_of_worksheet',
    ),
    $transformer,
    $writer
);

echo "Done - please check {$outputFolder} for the files\n";