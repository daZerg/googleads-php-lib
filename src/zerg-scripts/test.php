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

require '../../vendor/autoload.php';

use Google\AdsApi\Common\OAuth2TokenBuilder;
use Google\AdsApi\Dfp\DfpServices;
use Google\AdsApi\Dfp\DfpSession;
use Google\AdsApi\Dfp\DfpSessionBuilder;
use Google\AdsApi\Dfp\Util\v201702\StatementBuilder;
use Google\AdsApi\Dfp\v201702\LineItemService;

/**
 * A collection of utility methods for examples.
 * @package GoogleApiAdsCommon
 * @subpackage Util
 */
// Generate a refreshable OAuth2 credential for authentication.
$oAuth2Credential = (new OAuth2TokenBuilder())
	->fromFile()
	->build();

// Construct an API session configured from a properties file and the OAuth2
// credentials above.
$session = (new DfpSessionBuilder())
	->fromFile()
	->withOAuth2Credential($oAuth2Credential)
	->build();

try {
	$dfpServices = new DfpServices();
    // Get DfpUser from credentials in "../auth.ini"
    // relative to the DfpUser.php file's directory.
    // Get the LineItemService.
	$lineItemService =
		$dfpServices->get($session, LineItemService::class);

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

    // Get line items by statement.
	    $page = $lineItemService->getLineItemsByStatement(
		    $statementBuilder->toStatement());

	    // Print out some information for each line item.
	    if ($page->getResults() !== null) {
		    $totalResultSetSize = $page->getTotalResultSetSize();
		    $i = $page->getStartIndex();
		    foreach ( $page->getResults() as $lineItem ) {
//			    print "Line Item {$lineItem->name} CPM {$cpm}\n";//{$lineItem->costPerUnit}\n";
//                var_dump( $lineItem->targeting->customTargeting );
//			    $target = $lineItem->targeting->customTargeting->children[0]->children[0];
			    echo var_export( $lineItem, true );
			    print "\n";
//			    if ( $target->keyId == "598335" ) {
//				    $lineItemCustomTargeting[$lineItem->id] = $target->valueIds[0];
//			    }
//                print "\n";
//                printf("%d) Line item with ID %d, belonging to order %d, and name '%s' "
//                    . " with custom fields %s.\n", $i++, $lineItem->id, $lineItem->orderId,
//                    $lineItem->name, $lineItem->customFieldValues);

			    die();
		    }
	    }


} catch (OAuth2Exception $e) {
    ExampleUtils::CheckForOAuth2Errors($e);
} catch (ValidationException $e) {
    ExampleUtils::CheckForOAuth2Errors($e);
} catch (Exception $e) {
    printf("%s\n", $e->getMessage());
}


