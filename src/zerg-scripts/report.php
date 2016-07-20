<?php
/**
 * This example runs a report that includes a custom field. To determine which
 * custom fields exist, run GetAllCustomFields.php.
 *
 * PHP version 5
 *
 * Copyright 2014, Google Inc. All Rights Reserved.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 *
 * @package    GoogleApiAdsDfp
 * @subpackage v201605
 * @category   WebServices
 * @copyright  2014, Google Inc. All Rights Reserved.
 * @license    http://www.apache.org/licenses/LICENSE-2.0 Apache License,
 *             Version 2.0
 */
error_reporting(E_STRICT | E_ALL);

// You can set the include path to src directory or reference
// DfpUser.php directly via require_once.
// $path = '/path/to/dfp_api_php_lib/src';
$path = dirname(__FILE__) . '/../../../../src';
set_include_path(get_include_path() . PATH_SEPARATOR . $path);

require_once 'Google/Api/Ads/Dfp/Lib/DfpUser.php';
require_once 'Google/Api/Ads/Dfp/Util/v201605/ReportDownloader.php';
require_once 'Google/Api/Ads/Dfp/Util/v201605/StatementBuilder.php';

require_once 'Google/Api/Ads/Common/Lib/ValidationException.php';
require_once 'Google/Api/Ads/Common/Util/OAuth2Handler.php';

/**
 * A collection of utility methods for examples.
 * @package GoogleApiAdsCommon
 * @subpackage Util
 */
abstract class ExampleUtils {

    /**
     * Checks for any OAuth2 Errors with relevant info. Otherwise, provide a
     * relevant error message.
     * @param Exception $raisedException is the exception to inspect
     */
    public static function CheckForOAuth2Errors(Exception $raisedException) {
        $errorMessage = "An error has occured:";
        if ($raisedException instanceof OAuth2Exception) {
            $errorMessage = "Your OAuth2 Credentials are incorrect.\nPlease see the"
                . " GetRefreshToken.php example.";
        } elseif ($raisedException instanceof ValidationException) {
            $requiredAuthFields =
                array('client_id', 'client_secret', 'refresh_token');
            $trigger = $raisedException->GetTrigger();
            if (in_array($trigger, $requiredAuthFields)) {
                $errorMessage = sprintf(
                    "Your OAuth2 Credentials are missing the '%s'. Please see"
                    . " GetRefreshToken.php for further information.",
                    $trigger
                );
            }
        }
        printf("%s\n%s\n", $errorMessage, $raisedException->getMessage());
    }
}

try {
    // Get DfpUser from credentials in "../auth.ini"
    // relative to the DfpUser.php file's directory.
    $user = new DfpUser();

    // Log SOAP XML request and response.
    $user->LogDefaults();

    // Get the ReportService.
    $reportService = $user->GetService('ReportService', 'v201605');

    // Set the ID of the custom field to include in the report.
    $CUSTOM_FIELD_ID = "INSERT_CUSTOM_FIELD_ID_HERE";

    // Create report query.
    $reportQuery = new ReportQuery();
    $reportQuery->dimensions = array('ADVERTISER_NAME', 'AD_UNIT_NAME', 'COUNTRY_NAME', 'DEVICE_CATEGORY_NAME');
    $reportQuery->columns = array('AD_SERVER_IMPRESSIONS','AD_SERVER_CPM_AND_CPC_REVENUE');

    // Set the dynamic date range type or a custom start and end date.
    $reportQuery->dateRangeType = 'YESTERDAY';

    // Set the custom field IDs.
//    $reportQuery->customFieldIds = array($CUSTOM_FIELD_ID);

    // Create report job.
    $reportJob = new ReportJob();
    $reportJob->reportQuery = $reportQuery;

    // Run report job.
    $reportJob = $reportService->runReportJob($reportJob);

    // Create report downloader.
    $reportDownloader = new ReportDownloader($reportService, $reportJob->id);

    // Wait for the report to be ready.
    $reportDownloader->waitForReportReady();

    // Change to your file location.
    $filePath = sprintf('%s.csv', tempnam("/tmp/",'custom-field-report-'));
    print $filePath;

    // Download the report.
    $reportDownloader->downloadReport('CSV_DUMP', $filePath.".gz");
    `gunzip $filePath.gz`;

    $advertisers = array();
    $totalImpressions = 0;
    $totalRevenue = 0;

    $revDivisor = 1000000;

    $header = false;
    /**
     * SHOULD LOOK LIKE
     * array(8) {
    [0]=>
    string(25) "Dimension.ADVERTISER_NAME"
    [1]=>
    string(22) "Dimension.COUNTRY_NAME"
    [2]=>
    string(22) "Dimension.AD_UNIT_NAME"
    [3]=>
    string(30) "Dimension.DEVICE_CATEGORY_NAME"
    [4]=>
    string(23) "Dimension.ADVERTISER_ID"
    [5]=>
    string(29) "Dimension.COUNTRY_CRITERIA_ID"
    [6]=>
    string(28) "Dimension.DEVICE_CATEGORY_ID"
    [7]=>
    string(28) "Column.AD_SERVER_IMPRESSIONS"
    [8]=>
    string(36) "Column.AD_SERVER_CPM_AND_CPC_REVENUE"
    }
     */

    $fp = fopen( $filePath, "r" );
    while( !feof( $fp ) ) {
        $line = fgetcsv( $fp );
        if ( !$header ) {
            $header = array_flip( $line );
        } else {
            $network = $line[$header['Dimension.ADVERTISER_NAME']];

            if ( $network ) {
                $site = $line[$header['Dimension.AD_UNIT_NAME']];
                if ( $site == "NickiSwift" ) {
                    $site = "Nickiswift";
                }

                $region = ($line[$header['Dimension.COUNTRY_NAME']] == "United States") ? "US" : "Non-US";
                $rawDevice = strtolower($line[$header['Dimension.DEVICE_CATEGORY_NAME']]);
                if ($rawDevice !== "desktop" && $rawDevice != "tablet") {
                    $device = "mobile";
                } else {
                    $device = $rawDevice;
                }
                $impressions = intval($line[$header['Column.AD_SERVER_IMPRESSIONS']]);
                $revenue = intval($line[$header['Column.AD_SERVER_CPM_AND_CPC_REVENUE']]) / $revDivisor;

                if (!isset($advertisers[$network])) {
                    $advertisers[$network] = array();
                }
                if (!isset($advertisers[$network][$site])) {
                    $advertisers[$network][$site] = array(
                        array(
                            "US" => array(
                                "desktop" => array(
                                    "impressions" => 0,
                                    "revenue" => 0,
                                ),
                                "tablet" => array(
                                    "impressions" => 0,
                                    "revenue" => 0,
                                ),
                                "mobile" => array(
                                    "impressions" => 0,
                                    "revenue" => 0,
                                ),
                            ),
                            "Non-US" => array(
                                "desktop" => array(
                                    "impressions" => 0,
                                    "revenue" => 0,
                                ),
                                "tablet" => array(
                                    "impressions" => 0,
                                    "revenue" => 0,
                                ),
                                "mobile" => array(
                                    "impressions" => 0,
                                    "revenue" => 0,
                                ),
                            ),
                        )
                    );
                }

                $advertisers[$network][$site][$region][$device]['impressions'] += $impressions;
                $advertisers[$network][$site][$region][$device]['revenue'] += $revenue;
            }
        }
    }

    var_dump( $advertisers );

    $query = "INSERT INTO google_dfp_assumptions ".
                "( gda_day, gda_network, gda_site, gda_region, gda_device, gda_impressions, gda_revenue ) ".
             "VALUES ";
    $queryInsert = "";
    foreach( $advertisers as $network => $networkArr ) {
        foreach( $networkArr as $site => $siteArr ) {
            foreach( $siteArr as $region => $regionArr ) {
                foreach( $regionArr as $device => $deviceArr ) {
                    if ( $deviceArr['impressions'] > 0 ) {
                        if ( $queryInsert ) {
                            $queryInsert .= ", ";
                        }

                        $queryInsert .= sprintf("('%s','%s','%s','%s','%s','%d','%s')",
                            date("Y-m-d", strtotime("yesterday")),
                            $network,
                            $site,
                            $region,
                            $device,
                            $deviceArr['impressions'],
                            $deviceArr['revenue']
                        );
                    }
                }
            }
        }
    }

    $query .= $queryInsert;

    print $query;


    printf("done.\n");
} catch (OAuth2Exception $e) {
    ExampleUtils::CheckForOAuth2Errors($e);
} catch (ValidationException $e) {
    ExampleUtils::CheckForOAuth2Errors($e);
} catch (Exception $e) {
    printf("%s\n", $e->getMessage());
}

