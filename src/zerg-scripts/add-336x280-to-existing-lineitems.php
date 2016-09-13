<?php

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
$path = dirname(__FILE__) . '/../../../../src';
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
    $statementBuilder->Where( "name LIKE '%_Pubmatic_Bidder_%'" );
    $statementBuilder->OrderBy('id ASC')
        ->Limit(StatementBuilder::SUGGESTED_PAGE_LIMIT);

    // Default for total result set size.
    $totalResultSetSize = 0;

	/**
	 object(CreativePlaceholder)#23 (7) {
	["size"]=>
	object(Size)#24 (3) {
	["width"]=>
	int(336)
	["height"]=>
	int(280)
	["isAspectRatio"]=>
	bool(false)
	}
	["companions"]=>
	NULL
	["appliedLabels"]=>
	NULL
	["effectiveAppliedLabels"]=>
	NULL
	["id"]=>
	string(1) "0"
	["expectedCreativeCount"]=>
	int(1)
	["creativeSizeType"]=>
	string(5) "PIXEL"
	}
	 */

    do {
        // Get line items by statement.
        $page = $lineItemService->getLineItemsByStatement(
            $statementBuilder->ToStatement());

        // Display results.
        if (isset($page->results)) {
            $totalResultSetSize = $page->totalResultSetSize;
            $i = $page->startIndex;
            foreach ($page->results as $lineItem) {
                $cpm = $lineItem->costPerUnit->microAmount / 1000000;
                print "Line Item {$lineItem->name} CPM {$cpm}\n";//{$lineItem->costPerUnit}\n";

	            $has336 = false;
	            foreach( $lineItem->creativePlaceholders as $holder ) {
		            if ( $holder->size->width == 336 ) {
			            $has336 = true;
		            }
	            }

	            if ( !$has336 ) {
		            // Create the creative placeholder.
		            $creativePlaceholder = new CreativePlaceholder();
		            $creativePlaceholder->size = new Size(336, 280, false);

		            // Set the size of creatives that can be associated with this line item.
		            $lineItem->creativePlaceholders[] = $creativePlaceholder;

		            // Update the line item on the server.
		            $lineItems = $lineItemService->updateLineItems(array($lineItem));

		            foreach ($lineItems as $updatedLineItem) {
			            printf("Line item with ID %d, name '%s' was updated.\n",
				            $updatedLineItem->id, $updatedLineItem->name);
		            }
	            }
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


