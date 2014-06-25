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

namespace Mechastorm\Google\Spreadsheet\Data\Transformers;

require_once('Interfaces/TransformerInterface.php');
use Mechastorm\Google\Spreadsheet\Data\Transformer\Interfaces\TransformerInterface;

/**
 * Data Transformer to PHP array for cales
 *
 * @package    Google
 * @subpackage Spreadsheet\Data\Transformers
 * @author     Shih Oon Liong <github@mechaloid.com>
 */
class LocalePhpArray implements TransformerInterface {

    private
        $defaultOptions = array(
            'column_terminator_id' => 'id',
            'column_notes_id'      => 'notes',
        ),
        $options = array();

    /**
     * @param array $options This array must contain the following properties/index
     *                       'locales' : (required) an array of locales to read for
     *
     */
    public function __construct($options=array())
    {
        $this->options = array_merge($this->defaultOptions, $options);
    }

    /**
     * Transform a single dimensional array of data into a mutli-level array
     *
     * @param type $data An array with each item representing a row in the spreadsheet. Example
     * array(2) {
     *   [0]=>
     *       array(9) {
     *          ["category"]=>
     *              string(4) "meta"
     *          ["sub-category"]=>
     *              string(6) "common"
     *          ["subcategory2"]=>
     *              string(0) ""
     *          ["subcategory3"]=>
     *              string(0) ""
     *          ["id"]=>
     *              string(5) "title"
     *          ["notes"]=>
     *              string(10) "Site Title"
     *           ["enus"]=>
     *               string(76) "This is America!"
     *           ["engb"]=>
     *               string(80) "This is Great Britain!"
     *       }
     *   [1]=>
     *       array(9) {
     *          ["category"]=>
     *              string(4) "meta"
     *          ["sub-category"]=>
     *              string(6) "common"
     *          ["subcategory2"]=>
     *              string(0) ""
     *          ["subcategory3"]=>
     *              string(0) ""
     *          ["id"]=>
     *              string(5) "head_of_state"
     *          ["notes"]=>
     *              string(10) "Title of Head of State"
     *           ["enus"]=>
     *               string(76) "President"
     *           ["engb"]=>
     *               string(80) "Prime Minister"
     *       }
     * }
     *
     * @return array This returns an array containing two indexes
     *							 'notes' - The notes associated with each copy
     *							 'localized' - The copy for each locale
     *													   ie
     *														 array (
     *															 'en_US' => array ( // copy ),
     *														   'en_GB' => array ( // copy ),
     *														 );
     */
    public function transform($data)
    {
        $copyNotes = $this->prepNotesData($data);
        $copyLocalized = $this->prepLangData($data);

        $copy = array (
            'notes' => $copyNotes,
            'localized'	=> $copyLocalized,
        );

        return $copy;
    }

    /**
     * Go through each locale and build our copy string per locale
     * @param type $locales
     * @param type $dataRows
     * @return type
     */
    protected function prepLangData($dataRows)
    {
        $copyLocalized = array();
        foreach ($this->options['locales'] AS $localeId) {
            $copyLocalized[$localeId] = $this->buildLangArray($dataRows, $localeId);
        }
        return $copyLocalized;
    }

    /**
     *
     * @param array|strings $spreadsheetData
     * @return type
     */
    public function reindexRows($spreadsheetData, $columns)
    {
        $dataRows = array();
        $rowsTemp = $spreadsheetData;
        foreach ($rowsTemp AS $index => $singleRow) {
            foreach ($singleRow AS $colId => $cell) {
                $dataRows[$index][$columns[$colId]] = $cell;
            }
        }

        return $dataRows;
    }

    /**
     *
     * @param type $dataRows
     * @param type $noteColId
     * @return type
     */
    protected function prepNotesData($dataRows)
    {
        $noteColId = $this->options['column_notes_id'];
        $copyNotes = $this->buildLangArray($dataRows, $noteColId);
        return $copyNotes;
    }

    /**
     *
     * Solution based on alternative answer
     * - http://stackoverflow.com/questions/10923018/how-do-i-transform-this-array-into-a-multi-dimensional-array-via-recursion
     * @param array|string $spreadsheetData
     * @param string $locale The name of the locale. This must be in the format of ll_CC (ie en_US, en_GB)
     * @return array
     */
    protected function buildLangArray($spreadsheetData, $locale)
    {
        $localIdText = strtolower(str_replace('_', '', $locale));

        $result = array();
        foreach ($spreadsheetData AS $rowId => $item) {
            $arrayStack = &$result;

            foreach ( $item as $colId => $val ) {
                if ( $colId == $this->options['column_terminator_id'] ) {
                    // Final section - we stop when the column name is 'id' or whatever was defined as the column_terminator_id
                    // @TODO Can be done via Regex instead

                    // Check if the 'id' value matches 'foo[]'
                    if (strstr($val, '[]') !== FALSE) {
                        $val = str_replace('[]', '', $val);
                        $arrayStack[$val][] = $item[$localIdText];
                        break;
                    }

                    // Check if the 'id' value matches 'foo[bar]'
                    if (strstr($val, '[') !== FALSE && strstr($val, ']') !== FALSE) {
                        $parts = explode('[', $val);
                        $parts[1] = str_replace(']', '', $parts[1]);

                        $arrayStack[$parts[0]][$parts[1]] = $item[$localIdText];
                        break;
                    }

                    $arrayStack[$val] = $item[$localIdText];
                    break;
                } elseif (empty ($val)) {
                    // If the value of the column is empty, skip to the next column
                    // This is to take into account columns that are like this
                    // | body | header |       | title |
                    continue;
                } else {
                    // Normal operation
                    if ( empty($arrayStack[$val]) ) {
                        $arrayStack[$val] = array();
                    }
                    $arrayStack = &$arrayStack[$val];
                }
            }
        }

        return $result;
    }
}

?>
