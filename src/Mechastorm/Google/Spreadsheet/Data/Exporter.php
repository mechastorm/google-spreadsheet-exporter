<?php
/**
 * Copyright 2014 Shih Oon Liong
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *    http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

namespace Mechastorm\Google\Spreadsheet\Data;

use Google\Spreadsheet\ServiceRequestFactory;

/**
 * Spreadsheet Service.
 *
 * @package    Google
 * @subpackage Spreadsheet\Data
 * @author     Shih Oon Liong <github@mechaloid.com>
 */
class Exporter
{
    private
        $defaultOptions = array(),
        $options = array(),
        $worksheetFeed;

    /**
     * @param array $options This array must contain the following properties/index
     *                       'access_token'     : (required)
     *                       'spreadsheet_name' : (require)
     *
     */
    public function __construct($options=array())
    {
        $this->options = array_merge($this->defaultOptions, $options);

        $this->worksheetFeed = $this->getWorksheets(
            $this->options['access_token'],
            $this->options['spreadsheet_name']
        );
    }

    public function processWorksheets($workSheets, $transformer, $writer)
    {


        foreach($workSheets AS $workSheetName) {
            $worksheet = $this->worksheetFeed->getByTitle($workSheetName);
            $listFeed = $worksheet->getListFeed();

            $dataRows = $this->getRows($listFeed);
            $transformedDataRow = $transformer->transform($dataRows);

            $writer->write($transformedDataRow);
        }
    }

    public function getRows($listFeed)
    {
        $dataRows = array();
        foreach ($listFeed->getEntries() as $entry) {
            $row = $entry->getValues();
            if ($row['category'] == '#') {
                continue;
            }
            $dataRows[] = $row;
        }

        return $dataRows;
    }

    public function getWorksheets($accessToken, $spreadsheetTitle)
    {
        $serviceRequest = new \Google\Spreadsheet\DefaultServiceRequest($accessToken);
        ServiceRequestFactory::setInstance($serviceRequest);

        $spreadsheetService = new \Google\Spreadsheet\SpreadsheetService();
        $spreadsheetFeed = $spreadsheetService->getSpreadsheets();


        $spreadsheet = $spreadsheetFeed->getByTitle($spreadsheetTitle);
        $worksheetFeed = $spreadsheet->getWorksheets();

        return $worksheetFeed;
    }
}