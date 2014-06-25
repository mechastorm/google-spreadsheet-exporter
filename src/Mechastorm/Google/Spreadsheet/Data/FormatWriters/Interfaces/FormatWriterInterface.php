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

namespace Mechastorm\Google\Spreadsheet\Data\FormatWriters\Interfaces;

/**
 * Write a PHP array into a PHP file in a desired format
 *
 * @package    Google
 * @subpackage Spreadsheet\Data\FormatWriters\Interface
 * @author     Shih Oon Liong <github@mechaloid.com>
 */
interface FormatWriterInterface {

    /**
     * Write an array to a file
     * @param array $copyData
     * @return void
     */
    public function write($copyData);
}
