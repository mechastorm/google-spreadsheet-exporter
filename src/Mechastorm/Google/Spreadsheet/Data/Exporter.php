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

use Exception;
use Google\Spreadsheet\ServiceRequestFactory;
use Google\Spreadsheet\DefaultServiceRequest;
use Google\Spreadsheet\SpreadsheetService;

class ExporterException extends Exception { }

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
        $defaultOptions = array(
            'comment_symbol' => '#',
        ),
        $options = array(),
        $worksheetFeed = null;

    /**
     * @param array $options This array must contain the following properties/index
     *                       'access_token'     : (required)
     *                       'spreadsheet_name' : (required) The exact title of the spreadsheet
     *                       'comment_symbol'   : (optional) By default it will be '#'
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

    /**
     * Gets the current worksheets and processes the desired ones into specific file outputs
     * @param array $workSheets The list of worksheets to process. The names must match exactly.
     * @param class $transformer
     * @param class $writer
     * @throws ExporterException
     */
    public function processWorksheets($workSheets, $transformer, $writer)
    {
        foreach($workSheets AS $workSheetName) {
            $worksheet = $this->worksheetFeed->getByTitle($workSheetName);
            if (is_null($worksheet)) {
                throw new ExporterException("No worksheet called '{$workSheetName}' was found!");
            }

            $listFeed = $worksheet->getListFeed();

            $dataRows = $this->getRows($listFeed);
            $transformedDataRow = $transformer->transform($dataRows);

            $writer->write($transformedDataRow);
        }
    }

    /**
     * @param \Google\Spreadsheet\ListFeed $listFeed
     * @return array
     */
    protected function getRows($listFeed)
    {
        $dataRows = array();
        foreach ($listFeed->getEntries() as $entry) {
            $row = $entry->getValues();
            if ($this->isCommentRow($row)) {
                continue;
            }
            $dataRows[] = $row;
        }

        return $dataRows;
    }

    /**
     * @param $rowData
     * @return bool
     */
    protected function isCommentRow($rowData)
    {
        reset($rowData);
        $valueFirstColumn = current($rowData);
        return ($valueFirstColumn == $this->options['comment_symbol']);
    }

    /**
     * Check if worksheets were found based on a given spreadsheet of this instance
     * @return bool
     */
    protected function isWorksheetsFromSpreadsheetFound()
    {
        return $this->worksheetFeed !== null;
    }

    /**
     * Sets the worksheets based on a given spreadsheet and access token.
     * If the spreadsheet is not found, then the `worksheetFeed` property will be null
     */
    public function setWorksheets()
    {
        $this->worksheetFeed = $this->getWorksheets(
            $this->options['access_token'],
            $this->options['spreadsheet_name']
        );

        if (!$this->isWorksheetsFromSpreadsheetFound()) {
            throw new ExporterException("No spreadsheet called '{$this->options['spreadsheet_name']}' found!");
        }
    }

    /**
     * @param string $accessToken The access token
     * @param string $spreadsheetTitle The exact title of the spreadsheet
     * @return null|\Google\Spreadsheet\WorksheetFeed
     */
    protected function getWorksheets($accessToken, $spreadsheetTitle)
    {
        $serviceRequest = new \Google\Spreadsheet\DefaultServiceRequest($accessToken);
        ServiceRequestFactory::setInstance($serviceRequest);

        $spreadsheetService = new \Google\Spreadsheet\SpreadsheetService();
        $spreadsheetFeed = $spreadsheetService->getSpreadsheets();

        $spreadsheet = $spreadsheetFeed->getByTitle($spreadsheetTitle);
        if (!$spreadsheet || $spreadsheet === null) {
            return null;
        }

        $worksheetFeed = $spreadsheet->getWorksheets();

        return $worksheetFeed;
    }
}