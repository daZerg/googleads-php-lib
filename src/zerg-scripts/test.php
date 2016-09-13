<?php

$testId = false;
if ( !empty( $argv[1] ) ) {
	$testId = $argv[1];
}
if ( !$testId ) {
	die( "Usage: php test.php LINEITEMID\n" );
}

date_default_timezone_set("UTC");
/**
 * This example gets all line items. To create line items, run
 * CreateLineItems.php.
 *
 * Tags: LineItemService.getLineItemsByStatement
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
 * @author     Vincent Tsao
 */
error_reporting(E_STRICT | E_ALL);

// You can set the include path to src directory or reference
// DfpUser.php directly via require_once.
// $path = '/path/to/dfp_api_php_lib/src';
$path = '../../src';
set_include_path(get_include_path() . PATH_SEPARATOR . $path);

require_once 'Google/Api/Ads/Dfp/Lib/DfpUser.php';
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

    // Get the LineItemService.
    $lineItemService = $user->GetService('LineItemService', 'v201605');

    // Create a statement to select all line items.
    $statementBuilder = new StatementBuilder();
    $statementBuilder->Where( "id = '{$testId}'" );
    $statementBuilder->OrderBy('id ASC')
        ->Limit(StatementBuilder::SUGGESTED_PAGE_LIMIT);

    // Default for total result set size.
    $totalResultSetSize = 0;

    $bidLineItems = array();
    $lineItemCreatives = array();
    $lineItemCustomTargeting = array();
    $customTargetLabels = array();

    do {
        // Get line items by statement.
        $page = $lineItemService->getLineItemsByStatement(
            $statementBuilder->ToStatement());

        // Display results.
        if (isset($page->results)) {
            $totalResultSetSize = $page->totalResultSetSize;
            $i = $page->startIndex;
            foreach ($page->results as $lineItem) {
                $bidLineItems[$lineItem->id] = $lineItem->name;
                $cpm = $lineItem->costPerUnit->microAmount/1000000;
                print "Line Item {$lineItem->name} CPM {$cpm}\n";//{$lineItem->costPerUnit}\n";
//                var_dump( $lineItem->targeting->customTargeting );
                $target = $lineItem->targeting->customTargeting->children[0]->children[0];
                var_dump( $lineItem );
                print "\n";
                if ( $target->keyId == "598335" ) {
                    $lineItemCustomTargeting[$lineItem->id] = $target->valueIds[0];
                }
//                print "\n";
//                printf("%d) Line item with ID %d, belonging to order %d, and name '%s' "
//                    . " with custom fields %s.\n", $i++, $lineItem->id, $lineItem->orderId,
//                    $lineItem->name, $lineItem->customFieldValues);

                die();
            }
        }

        $statementBuilder->IncreaseOffsetBy(StatementBuilder::SUGGESTED_PAGE_LIMIT);
    } while ($statementBuilder->GetOffset() < $totalResultSetSize);

} catch (OAuth2Exception $e) {
    ExampleUtils::CheckForOAuth2Errors($e);
} catch (ValidationException $e) {
    ExampleUtils::CheckForOAuth2Errors($e);
} catch (Exception $e) {
    printf("%s\n", $e->getMessage());
}


