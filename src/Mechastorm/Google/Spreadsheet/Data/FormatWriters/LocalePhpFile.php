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

namespace Mechastorm\Google\Spreadsheet\Data\FormatWriters;

require_once('Interfaces/FormatWriterInterface.php');
use Mechastorm\Google\Spreadsheet\Data\FormatWriters\Interfaces\FormatWriterInterface;

/**
 * Write a PHP array into a PHP file in a desired format
 *
 * @package    Google
 * @subpackage Spreadsheet\Data\FormatWriters
 * @author     Shih Oon Liong <github@mechaloid.com>
 */
class LangPhpWriter implements FormatWriterInterface {

    private
        $defaultOptions = array(
            'separate' => false,
            'filename' => null,
            'template' => '',
            'meta'     => array(),
            'default_new_folder_permissions' => 0777,
        ),
        $options = array();

    /**
     * @param array $options This array must contain the following properties/index
     *                       string   'path'        : (required) The path to write the files to
     *                       string   'template'    : (optional) the format of the contents of the file will be like
     *                       array    'meta'        : (optional) Single dimensional array of metadata you want to add to the file
     *                       boolean  'separate'    : (optional) Default to false. Whether separate files should be written for each index of the array under each locale
     *                       string   'filename'    : (optional/required) If 'separate' is set to true, then this option must be set to decide what is the filename to write as
     *
     */
    public function __construct($options=array())
    {
        $this->options = array_merge($this->defaultOptions, $options);
    }

    /**
     * Write a PHP Lang Config File
     * @param array $copyData
     *
     * @return void
     */
    public function write($copyData)
    {
        if (array_key_exists('localized', $copyData)) {
            $copyData = $copyData['localized'];
        }

        foreach ($copyData AS $locale => $stringData) {
            $fileOptions['locale'] = $locale;

            foreach ($stringData AS $category => $strings) {
                $path = $this->buildWritePath($locale, $category);

                $fileOptions['filename'] = $category;
                $fileOptions['locale'] = $locale;
                $this->writeDataFile($path, $strings, $fileOptions);
            }
        }
    }

    /**
     *
     * @param string $locale
     * @param string $filename
     * @return string
     */
    protected function buildWritePath($locale, $filename) {
        $basePath = $this->options['path'];
        $path = $basePath . '/' . $locale . '/' . $filename . '.php';
        return $path;
    }

    public static function fileTemplateDefault($type='') {

        switch($type) {
            case 'config' :
                $code = '$config = {{array_content}} ;';
                break;
            default :
                $code = 'return {{array_content}} ;';
        }

        $template = <<<EOT
<?php
/****************************************************************************
* Locale '{{locale}}' PHP File for '{{filename}}.php'
* This file was automatically generated
* Location		: {{path}}
* Generated		: {{timestamp}}
****************************************************************************/

$code

// End of File
EOT;

        return $template;
    }

    /**
     * Write the data to a file
     *
     * @param string $path The full path of the file including filename to write the data to
     * @param array $data
     * @param array $options
     *
     * @return void
     */
    public function writeDataFile($path, $data, $options=array())
    {
        $templateVars = array();

        // Strings outputted as PHP Code
        $fileData = var_export($data, TRUE);
        $templateVars['array_content'] = $fileData;

        // Define the template
        if (empty($this->options['template'])) {
            $fileTemplate = self::fileTemplateDefault();
        } else {
            $fileTemplate = $this->options['template'];
        }

        // Build the template variables
        if (!empty($this->options['meta'])) {
            $templateVars = $this->options['meta'];
        }
        $templateVars['path'] = $path;
        $templateVars['timestamp'] = date('Y-m-d H:i:s', time());
        $templateVars = array_merge($templateVars, $options);

        // Generate the output of the final file
        $fileContents = str_replace(
            $this->getTemplateVarIds($templateVars), // search
            array_values($templateVars),  // replace,
            $fileTemplate   // subject
        );

        // Write the file
        $this->createFolderPath($path);
        file_put_contents($path, $fileContents);
    }

    /**
     * @param $templateVars
     * @return array
     */
    private function getTemplateVarIds($templateVars)
    {
        $templateVarIds = array();
        $keys = array_keys($templateVars);
        foreach($keys AS $key) {
            $templateVarIds[] = '{{'.$key.'}}';
        }

        return $templateVarIds;
    }

    /**
     * Create the folders to the path if it doesn't exist
     * @param string $path
     *
     * @return void
     */
    public function createFolderPath($path)
    {
        if( ! file_exists(dirname($path)) ) {
            mkdir(dirname($path), $this->options['default_new_folder_permissions'], true);
        } else {
        }
    }
}

?>
