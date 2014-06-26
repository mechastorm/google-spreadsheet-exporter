# Introduction

The main functionality of this library focuses on the reading data from a Google Spreadsheet and outputting it into a readable file. Mostly for the purpose of localization and copy management, but can also be utilized for any other cases where one needs to transform data from Google Spreadsheet into another file format.

A sample of the sort spreadsheet it was made to read from can be seen [here](https://docs.google.com/a/mechaloid.com/spreadsheets/d/1GFQQ0clQRrYEM8_N0vyHeIIWqQdxJlbDe588uf_vlkU/edit#gid=0)

The libraries help with

- Read content from a Google Spreadsheet via a [service account](https://developers.google.com/drive/web/service-accounts)
- Transform that data. Currently only format is a hierarchical php array
- Writes that data into a file format. Currently supports a simple PHP file. Sample outputs can be seen at `examples/sample_ouput`

# Framework Integrations

The library itself has been built to be framework agnostic (no framework dependencies). But some integration with existing framework examples

- On another package that I made, [Laravel Language Google Spreadsheet Importer](https://github.com/mechastorm/laravel-lang-google-spreadsheet-importer).

# Background

The aim is to

* Avoid hard coding copy text
* Reduce the dev cycle required when doing edits to copy text
* Nicely handle multiple translations of a copy text

This functionality was originally used at Nudge Social Media lead by @iskandar). This has proven to be really quick and handy way to let non-tech users write out some data into the ever familiar spreadsheet and for developers to extract that data in a readable format.

The original library heavily used [Zend_Gdata](https://github.com/zendframework/ZendGData) to gain access to the Google Spreadsheet. Zend_Gdata is no longer maintained and Google Apis are changing over to a new OAuth.

I wanted to use the Google Spreadsheet primarily for copy management and translations but had to use the newer Google APIs.

# Todo

- PhpUnit Tests
- Documentation
    - Contributing

# Setup & Installation

## Step 1. Get Your Google API Credentials

Go to [https://console.developers.google.com/project](https://console.developers.google.com/project) and create a new project.

Go into your project settings and click on left menu, "APIS & AUTH" > "APIS". Enable "Drive API".

Then click on left menu, "APIS & AUTH" > "Credentials", and click on "Create new Client ID". Select "Service Account" and generate your client ID.

![Image](docs/google_api_credentials_screen.png?raw=true)

Your credentials would look something like

```shell
Client ID	            11111111111-abcdef.apps.googleusercontent.com
Email address	        11111111111-abcdef@developer.gserviceaccount.com
Public key fingerprints	1234567890

```

You will also have been prompted to download a p12 private key certificate (ie. 1234567890-privatekey.p12)

## Step 2. Give Spreadsheet Access

We assume you have already set up your spreadsheet just like the [sample](https://docs.google.com/a/mechaloid.com/spreadsheets/d/1GFQQ0clQRrYEM8_N0vyHeIIWqQdxJlbDe588uf_vlkU/edit#gid=0)

__IMPORTANT__

At this point, the client email address should be the on you use to share your spreadsheet with.

You can give it view only access. Future updates may include updating the spreadsheet, which by that point, you will need to give edit access.

## Configure Composer

Installation is primary via composer.

Create a composer.json file in your project and add the following:

```json
{
    "repositories": [
        {
            "type": "vcs",
            "url": "https://github.com/mechastorm/google-spreadsheet-exporter.git"
        },
        {
            "type": "vcs",
            "url": "https://github.com/asimlqt/php-google-spreadsheet-client"
        }
    ],
    "require": {
        "mechastorm/google-spreadsheet-extractor": "dev-master"
    }
}
```

## Usage Example

If you are not using a PHP Framework or one that does not support autoloading, make sure to include the autoloader from composer.

A sample of this file can be seen at `examples/basic.php`. Let us assume you were using this [sample spreadsheet](https://docs.google.com/a/mechaloid.com/spreadsheets/d/1GFQQ0clQRrYEM8_N0vyHeIIWqQdxJlbDe588uf_vlkU/edit#gid=0)

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
        'key_file_location' => 'location-to-your-private-key-p12-file', // This is the location of the P12 private key file you had donwloaded
    )
);
$accessToken = $authResponse->access_token;

echo 'Service Account Access Token: ' . $accessToken;

// Connect to the Google Spreadsheet
$gSSExporter = new Exporter(array(
    'access_token' => $accessToken,
    'spreadsheet_name' => 'google-spreadsheet-exporter Sample Spreadsheet' // It must match EXACTLY the name
));
$gSSExporter->setWorksheets();

// Instantiate a transformer
$transformer = new LocalePhpArray(array(
    'locales' => array(
        'en_GB', // Must match the columns on the spreadsheet
        'fr_CA',
        'my_MY',
        'ja_JP',
    ),
));

// Instantiate a write
$outputFolder = 'sample_output';
$writer = new LangPhpWriter(array(
    'path' => $outputFolder,
));

// Process the worksheets and output them to the desired format
$gSSExporter->processWorksheets(
    array(
        'Web Copy', // It must match EXACTLY the name
    ),
    $transformer,
    $writer
);

echo "Done - please check {$outputFolder} for the files\n";

```

Again sample outputs can be seen at `examples/sample_ouput`

# Tests

Coming Up!

# Contributors

- Shih Oon Liong (@mechastorm)


## Credit

- Iskandar Najmuddin([@iskandar](https://github.com/iskandar)) & Matthew Long ([@matthewongithub](https://github.com/matthewongithub)) on the development of the original Kohana library that inspired this version

## License

Released under the [Apache 2.0 license](http://choosealicense.com/licenses/apache-2.0/).
