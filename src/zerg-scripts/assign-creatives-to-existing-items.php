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
 * @subpackage v201505
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
require_once 'Google/Api/Ads/Dfp/Util/v201505/StatementBuilder.php';

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

$bidderCreatives = array(
    "68148258735",
    "68911686015",
    "68911515855",
    "68915210055",
    "68148263535",
    "68911515975",
    "68911686135",
    "68911710255",
    "68482319655",
    "68910760695",
    "68911515735",
    "68911685895",
    "68148264375",
    "68911516095",
    "68911686255",
    "68911740255",
);

$amazonCreatives = array(
    "76464837855",
    "76464839055",
    "76464839535",
    "76464935535",
    "76465976775",
    "76466035455",
    "76466090895",
    "76466091735",
    "76466096175",
    "76466096775",
    "76466157495",
    "76466181255",
    "76466243535",
    "76466246175",
    "76466246895",
    "76466247135",
    "76466249775",
    "76466375655",
    "76466375775",
    "76466375895",
);

$appnexusCreatives = array(
    "76464747615",
    "76464834375",
    "76464838095",
    "76464837975",
    "76464838335",
    "76464838215",
    "76464838575",
    "76464838455",
    "76464838935",
    "76464838815",
    "76465131495",
    "76465130655",
    "76465130775",
    "76465130895",
    "76465131015",
    "76465131135",
    "76465131255",
    "76465131375",
    "76465135935",
    "76465136055",
);

$pubmaticCreatives = array(
    "92512846935",
    "92512847055",
    "92512847175",
    "92512847295",
    "92512847415",
    "92512847535",
    "92512847655",
    "92512847775",
    "92512847895",
    "92512848015",
    "92512848135",
    "92512848255",
    "92512848375",
    "92512848495",
    "92512848615",
    "92512848735",
    "92512848855",
    "92512848975",
    "92512849095",
    "92512849215",

	"101554422375",
	"101554434615",
	"101554434855",
	"101554435095",
	"101554434975",
);

$aolCreatives = array(
    "92719249455",
    "92719249575",
    "92719249695",
    "92719249815",
    "92719249935",
    "92719248855",
    "92719248975",
    "92719249095",
    "92719249215",
    "92719249335",
    "92719250655",
    "92719250775",
    "92719250895",
    "92719251015",
    "92719251135",
    "93895619295",
    "93895619415",
    "93895619535",
    "93895619655",
    "93895619775",
);

$districtmCreatives = array(
    "96446797215",
    "96446797335",
    "96446797455",
    "96446797575",
    "96446797695",
    "96446797815",
    "96446797935",
    "96446798055",
    "96446798175",
    "96446798295",
    "96446798415",
    "96446798535",
    "96446798655",
    "96446798775",
    "96446798895",
    "96446799015",
    "96446799135",
    "96446799255",
    "96446799375",
    "96446799495",
);

$pulsepointCreatives = array(
    "99629001855",
    "99629000775",
    "99629000895",
    "99629001015",
    "99629001135",
    "99629001255",
    "99629001375",
    "99629001495",
    "99629001615",
    "99629001735",
    "99629001975",
    "99629002095",
    "99629002215",
    "99629002335",
    "99629002455",
    "99629002575",
);

$cpxiCreatives = array(
	"105788194095",
	"105785121375",
	"105785555535",
	"105788201895",
	"105788202015",
	"105788202135",
	"105788202255",
	"105788202375",
	"105788202495",
	"105788202615",
	"105788202735",
	"105788194215",
	"105788194335",
	"105788194455",
	"105788201775",
	"105788202855",
	"105788202975",
	"105788203095",
	"105788203215",
	"105788203335",
);

$assignCreatives = $cpxiCreatives;

try {
    // Get DfpUser from credentials in "../auth.ini"
    // relative to the DfpUser.php file's directory.
    $user = new DfpUser();

    // Log SOAP XML request and response.
    $user->LogDefaults();

    // Get the LineItemService.
    $lineItemService = $user->GetService('LineItemService', 'v201505');

    // Create a statement to select all line items.
    $statementBuilder = new StatementBuilder();
    $statementBuilder->Where( "name LIKE '%_CPXi_%' AND id != 221156775" );
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
                if ($lineItem->name != "AppNexus_Bidder_1.0") {
                    $bidLineItems[$lineItem->id] = $lineItem->name;
                    $lineItemCreatives[$lineItem->id] = array();
                    $cpm = $lineItem->costPerUnit->microAmount / 1000000;
                    print "Line Item {$lineItem->name} CPM {$cpm}\n";//{$lineItem->costPerUnit}\n";
//                var_dump( $lineItem->targeting->customTargeting );
                    $target = $lineItem->targeting->customTargeting->children[0]->children[0];
//                var_dump( $target );
//                print "\n";
                    if ($target->keyId == "579975") {
                        $lineItemCustomTargeting[$lineItem->id] = $target->valueIds[0];
                    }
//                print "\n";
//                printf("%d) Line item with ID %d, belonging to order %d, and name '%s' "
//                    . " with custom fields %s.\n", $i++, $lineItem->id, $lineItem->orderId,
//                    $lineItem->name, $lineItem->customFieldValues);
                }
            }
        }

        $statementBuilder->IncreaseOffsetBy(StatementBuilder::SUGGESTED_PAGE_LIMIT);
    } while ($statementBuilder->GetOffset() < $totalResultSetSize);

    $customTargetingService =
        $user->GetService('CustomTargetingService', 'v201505');

    // Create a statement to get all custom targeting values for a custom
    // targeting key.
    $statementBuilder = new StatementBuilder();
    $statementBuilder->Where('id IN ('.implode( ",", $lineItemCustomTargeting ).")" )
        ->OrderBy('id ASC')
        ->Limit(StatementBuilder::SUGGESTED_PAGE_LIMIT);

    $totalResultsCounter = 0;

    do {
        // Get custom targeting values by statement.
        $page = $customTargetingService->getCustomTargetingValuesByStatement(
            $statementBuilder->ToStatement());

        // Display results.
        if (isset($page->results)) {
            $totalResultSetSize = $page->totalResultSetSize;
            $i = $page->startIndex;
            foreach ($page->results as $customTargetingValue) {
//                var_dump( $customTargetingValue );
//                print "\n";
                $customTargetLabels[$customTargetingValue->id] = $customTargetingValue->name;
            }
        }

        $statementBuilder->IncreaseOffsetBy(StatementBuilder::SUGGESTED_PAGE_LIMIT);
    } while (intval( $statementBuilder->GetOffset()) < $totalResultSetSize);

    // Get the LineItemCreativeAssociationService.
    $licaService =
        $user->GetService('LineItemCreativeAssociationService', 'v201505');

    // Create a statement to select all LICAs.
    $statementBuilder = new StatementBuilder();
    $statementBuilder->Where( "lineItemId IN (".implode( ", ", array_keys($bidLineItems) ).")" );
    $statementBuilder->OrderBy('lineItemId ASC, creativeId ASC')
        ->Limit(StatementBuilder::SUGGESTED_PAGE_LIMIT);

    // Default for total result set size.
    $totalResultSetSize = 0;

    do {
        // Get LICAs by statement.
        $page = $licaService->getLineItemCreativeAssociationsByStatement(
            $statementBuilder->ToStatement());

        // Display results.
        if (isset($page->results)) {
            $totalResultSetSize = $page->totalResultSetSize;
            $i = $page->startIndex;
            foreach ($page->results as $lica) {
                if (isset($lica->creativeSetId)) {
                    printf("%d) LICA with line item ID %d, and creative set ID %d was "
                        . "found.\n", $i++, $lica->lineItemId, $lica->creativeSetId);
                } else {
//                    printf("%d) LICA with line item ID %d, and creative ID %d was "
//                        . "found.\n", $i++, $lica->lineItemId, $lica->creativeId);
                    if ( ! isset( $lineItemCreatives[$lica->lineItemId] ) ) {
                        $lineItemCreatives[$lica->lineItemId] = array();
                    }
                    $lineItemCreatives[$lica->lineItemId][] = $lica->creativeId;
                }
            }
        }

        $statementBuilder->IncreaseOffsetBy(StatementBuilder::SUGGESTED_PAGE_LIMIT);
    } while ($statementBuilder->GetOffset() < $totalResultSetSize);

    foreach( $lineItemCreatives as $lineItemId => $creativeArr ) {
        print( "Line Item {$bidLineItems[$lineItemId]} rtb_bid is {$customTargetLabels[$lineItemCustomTargeting[$lineItemId]]} has ".sizeof( $creativeArr )." creatives\n" );
//        print( implode( ",", $creativeArr )."\n" );
//
        print( "\nCreating for line item $lineItemId:\n" );

        foreach( $assignCreatives as $creativeId ) {
            if ( !in_array( $creativeId, $creativeArr ) ) {
                print "Adding creative {$creativeId} to {$lineItemId}\n";

                $lica = new LineItemCreativeAssociation();
                $lica->creativeId = $creativeId;
                $lica->lineItemId = $lineItemId;

                $licas = array($lica);

                // Create the LICAs on the server.
                $licas = $licaService->createLineItemCreativeAssociations($licas);

                // Display results.
                if (isset($licas)) {
                    foreach ($licas as $lica) {
                        print 'A LICA with line item ID "' . $lica->lineItemId
                            . '", creative ID "' . $lica->creativeId
                            . '", and status "' . $lica->status
                            . "\" was created.\n";
                    }
                } else {
                    print "No LICAs created.";
                }
            } else {
                print "Skipping creative {$creativeId} for {$lineItemId}\n";
            }
        }
    }

} catch (OAuth2Exception $e) {
    ExampleUtils::CheckForOAuth2Errors($e);
} catch (ValidationException $e) {
    ExampleUtils::CheckForOAuth2Errors($e);
} catch (Exception $e) {
    printf("%s\n", $e->getMessage());
}


