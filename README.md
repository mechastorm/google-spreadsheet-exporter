# Introduction

The main functionality of this library focuses on the reading data from a Google Spreadsheet and outputting it into a readable file. Mostly for the purpose of localization and copy management, but can also be utilized for any other cases where one needs to transform data from Google Spreadsheet into another file format.


# Background

The aim is to

* Avoid hard coding copy text
* Reduce the dev cycle required when doing edits to copy text
* Nicely handle multiple translations of a copy text

This functionality was originally used at Nudge Social Media lead by @iskandar). This has proven to be really quick and handy way to let non-tech users write out some data into the ever familiar spreadsheet and for developers to extract that data in a readable format.

The original library heavily used [Zend_Gdata](https://github.com/zendframework/ZendGData) to gain access to the Google Spreadsheet. Zend_Gdata is no longer maintained and Google Apis are changing over to a new OAuth.

I wanted to use the Google Spreadsheet primarily for copy management and translations but had to use the newer Google APIs.

# Todo

- Finalize codebase via usage on a Laravel Application
- Documentaion
    - How to setup Google API credentials
    - Usage
    - Contributing
- PhpUnit Tests

# Installation

Installation is primary via composer.

Create a composer.json file in your project and add the following:

```json
{
    "repositories": [
        {
            "type": "vcs",
            "url": "https://github.com/mechastorm/google-spreadsheet-exporter.git"
        }
    ],
    "require": {
        "mechastorm/google-spreadsheet-extractor": "dev-master"
    }
}
```

## Getting API Access

Coming Soon!

## Usage Example

If you are not using a PHP Framework or one that does not support autoloading, make sure to include the autoloader from composer.

A sample of this file can be seen at `examples/basic.php`

```php

require 'vendor/autoload.php';

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

// Connect to the Google Spreadsheet
$gSSExporter = new Exporter(array(
    'access_token' => $accessToken,
    'spreadsheet_name' => 'name_of_spreadsheet'
));

// Instantiate a transformer
$transformer = new LocalePhpArray(array(
    'locales' => array(
        'en_US', // Must match the columns on the spreadsheet
        'en_GB', // Must match the columns on the spreadsheet
    ),
));

// Instantiate a write
$outputFolder = '../output';
$writer = new LangPhpWriter(array(
    'path' => $outputFolder,
));

// Process the worksheets and output them to the desired format
$gSSExporter->processWorksheets(
    array(
        'name_of_worksheet',
    ),
    $transformer,
    $writer
);

echo "Done - please check {$outputFolder} for the files\n";

```

# Tests

Coming Soon!

# Contributors

- Shih Oon Liong (@mechastorm)


## Credit

- Iskandar Najmuddin(@iskandar) & Matthew Long (@matthewongithub) on the development of the original library.

## License

Released under the [Apache 2.0 license](http://choosealicense.com/licenses/apache-2.0/).
