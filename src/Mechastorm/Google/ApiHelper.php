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

namespace Mechastorm\Google;

/**
 * Google API Helper
 *
 * @package    Google
 * @subpackage Spreadsheet
 * @author     Shih Oon Liong <github@mechaloid.com>
 */
class ApiHelper
{
    /**
     * Get the response from a service account authorization
     * This response will have the access token that can be
     * used for subsequent Google Api Calls
     * @param $params
     * @return json_object
     */
    public static function getAuthByServiceAccount($params)
    {
        $defaultParams = array(
            'application_name' => '',
            'client_id' => '',
            'client_email' => '',
            'key_file_location' => '',
            'scopes' => array(
                'https://www.googleapis.com/auth/drive',
                'https://spreadsheets.google.com/feeds'
            ),
        );
        $params = array_merge($defaultParams, $params);

        $client = new \Google_Client();
        $client->setApplicationName($params['application_name']);

        $keyContents = file_get_contents($params['key_file_location']);
        $client->setAssertionCredentials(new \Google_Auth_AssertionCredentials(
                $params['client_email'],
                $params['scopes'],
                $keyContents)
        );

        $client->setClientId($params['client_id']);
        if ($client->getAuth()->isAccessTokenExpired()) {
            $client->getAuth()->refreshTokenWithAssertion();
        }

        $authResponse = $client->getAccessToken();

        return json_decode($authResponse);
    }
}