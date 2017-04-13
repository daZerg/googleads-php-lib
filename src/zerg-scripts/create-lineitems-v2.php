<?php

$size = false;
$network = false;
$site = false;
$bid = false;
$orderId = false;
$excludeBid = array();
$orderObj = false;

foreach( $argv as $index => $arg ) {
	if ( stripos( $arg, "--" ) == 0 ) {
		$arr = explode( "=", $arg );

		if ( $arr[0] == "--size" ) {
			$size = $arr[1];
		} elseif ( $arr[0] == "--network" ) {
			$network = $arr[1];
		} elseif ( $arr[0] == "--site" ) {
			$site = $arr[1];
		} elseif ( $arr[0] == "--order" ) {
			$orderId = $arr[1];
		} elseif ( $arr[0] == "--bid" ) {
			$json = json_decode( $arg, true );
			if ( $json ) {
				$bid = $json;
			} else {
				$bid = explode( ",", $arr[1] );
			}
		} elseif ( $arr[0] == "exclude" || $arr[0] == "--exclude-bid" ) {
			$json = json_decode( $arr[1], true );
			if ( $json && is_array( $json ) ) {
				$excludeBid = $json;
			} else {
				$excludeBid = explode( ",", $arr[1] );
			}
		}
	}
}

date_default_timezone_set("UTC");
error_reporting(E_STRICT | E_ALL);

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
	$orderService = $user->GetService( 'OrderService', 'v201605' );

	// Create a statement to select all line items.
	$statementBuilder = new StatementBuilder();
	$statementBuilder->Where( "id = '{$orderId}'" );
	$statementBuilder->OrderBy( 'id ASC' )
		->Limit( StatementBuilder::SUGGESTED_PAGE_LIMIT );

	print ( "Searching for order {$orderId}\n" );

	// Default for total result set size.
	$totalResultSetSize = 0;

	do {
		// Get line items by statement.
		$page = $orderService->getOrdersByStatement(
			$statementBuilder->ToStatement() );

		// Display results.
		if ( isset( $page->results ) ) {
			$totalResultSetSize = $page->totalResultSetSize;
			$i = $page->startIndex;
			foreach ( $page->results as $order ) {
				$orderObj = $order;
			}
		}

		$statementBuilder->IncreaseOffsetBy( StatementBuilder::SUGGESTED_PAGE_LIMIT );
	} while ( $statementBuilder->GetOffset() < $totalResultSetSize );
} catch (OAuth2Exception $e) {
	ExampleUtils::CheckForOAuth2Errors($e);
} catch (ValidationException $e) {
	ExampleUtils::CheckForOAuth2Errors($e);
} catch (Exception $e) {
	printf("%s\n", $e->getMessage());
}

if ( !$orderObj ) {
	die( "Order Object Not Found.\n" );
}
$orderId = $orderObj->id;
list(
	$site,
	$network,
	$size
) = explode( "_", $orderObj->name );

$keySet = array(
	"rtb_bid" => "579975",
	"rtb_network" => "598335",
	"rtb_size" => "696135",
);

$rtbBids = array(
    "1" => "123975030015",
    "2" => "123975103215",
    "3" => "123975110655",
    "4" => "123975106575",
    "5" => "123975107055",
    "6" => "123975107535",
    "7" => "123975114975",

    "0.5" => "123570084015",
    "1.5" => "123570086415",
    "2.5" => "123975104415",
    "3.5" => "123975110895",
    "4.5" => "123975105855",
    "5.5" => "123975111135",
    "6.5" => "123975108015",

    "0.1" => "123570083055",
    "0.2" => "123570083295",
    "0.3" => "123570083535",
    "0.4" => "123570083775",
    "0.6" => "123570084255",
    "0.7" => "123570084495",
    "0.8" => "123570084735",
    "0.9" => "123570084975",
    "1.1" => "123570085455",
    "1.2" => "123570085695",
    "1.3" => "123570085935",
    "1.4" => "123570086175",
    "1.6" => "123975102255",
    "1.7" => "123975102495",
    "1.8" => "123975102735",
    "1.9" => "123975102975",
    "2.1" => "123975103455",
    "2.2" => "123975103695",
    "2.3" => "123975103935",
    "2.4" => "123975104175",
    "2.6" => "123975104655",
    "2.7" => "123975104895",
    "4.8" => "123975105135",
    "6.2" => "123975105375",
    "6.7" => "123975105615",
    "4.4" => "123975106095",
    "4.7" => "123975106335",
    "6.3" => "123975106815",
    "5.7" => "123975107295",
    "6.8" => "123975107775",
    "3.4" => "123975108255",
    "5.9" => "123975108495",
    "6.6" => "123975108735",
    "5.2" => "123975108975",
    "3.2" => "123975109215",
    "3.7" => "123975109455",
    "5.4" => "123975109695",
    "6.1" => "123975109935",
    "2.9" => "123975110175",
    "4.6" => "123975110415",
    "3.6" => "123975111375",
    "5.1" => "123975111615",
    "5.3" => "123975111855",
    "4.9" => "123975112095",
    "5.8" => "123975112335",
    "5.6" => "123975112575",
    "6.9" => "123975112815",
    "2.8" => "123975113055",
    "3.1" => "123975113295",
    "4.3" => "123975113535",
    "6.4" => "123975113775",
    "4.1" => "123975114015",
    "3.3" => "123975114255",
    "4.2" => "123975114495",
    "3.9" => "123975114735",
    "3.8" => "123975115215",
    "10.8" => "125819750415",
    "19.9" => "125819750655",
    "20" => "125819750895",
    "16.3" => "125819751135",
    "9.1" => "125819751375",
    "9.3" => "125819751615",
    "9.8" => "125819751855",
    "19.2" => "125819752095",
    "11.5" => "125819752335",
    "15.1" => "125819752575",
    "15.6" => "125819752815",
    "14.5" => "125819753055",
    "12.2" => "125819753295",
    "12.3" => "125819753535",
    "12.1" => "125819753775",
    "12.8" => "125819754015",
    "13.6" => "125819754255",
    "16.5" => "125819754495",
    "16.8" => "125819754735",
    "8.8" => "125819754975",
    "15.9" => "125819755215",
    "17.5" => "125819755455",
    "7.8" => "125819755695",
    "15.3" => "125819755935",
    "10.9" => "125819756175",
    "17.8" => "125819756415",
    "14.6" => "125819756655",
    "15.7" => "125819756895",
    "17.2" => "125819757135",
    "15.2" => "125819757375",
    "17.1" => "125819757615",
    "12" => "125819757855",
    "10.7" => "125819758095",
    "7.9" => "125819758335",
    "9.6" => "125819758575",
    "9.7" => "125819758815",
    "13.7" => "125819759055",
    "14.9" => "125819759295",
    "19" => "125819759535",
    "9.5" => "125819759775",
    "11" => "125819760015",
    "7.4" => "125819760255",
    "9" => "125819760495",
    "10.3" => "125819760735",
    "15.5" => "125819760975",
    "8" => "125819761215",
    "11.1" => "125819761455",
    "15.8" => "125819761695",
    "12.7" => "125819761935",
    "18.5" => "125819762175",
    "17.3" => "125819762415",
    "18.2" => "125819762655",
    "16.6" => "125819762895",
    "11.7" => "125819763135",
    "17.7" => "125819763375",
    "10.1" => "125819763615",
    "18.8" => "125819763855",
    "16.2" => "125819764095",
    "13" => "125819764335",
    "16.7" => "125819764575",
    "17.9" => "125819764815",
    "18" => "125819765055",
    "7.7" => "125819765295",
    "8.6" => "125819765535",
    "11.4" => "125819765775",
    "8.9" => "125819766015",
    "17" => "125819766255",
    "9.2" => "125819766495",
    "14.1" => "125819766735",
    "19.1" => "125819766975",
    "10" => "125819767215",
    "13.4" => "125819767455",
    "17.4" => "125819767695",
    "7.1" => "125819767935",
    "10.6" => "125819768175",
    "19.7" => "125819768415",
    "18.3" => "125819768655",
    "16.4" => "125819768895",
    "13.1" => "125819769135",
    "11.6" => "125819769375",
    "8.3" => "125819769615",
    "13.8" => "125819769855",
    "18.9" => "125819770095",
    "9.9" => "125819770335",
    "12.9" => "125819770575",
    "13.2" => "125819770815",
    "16.9" => "125819771055",
    "19.3" => "125819771295",
    "13.9" => "125819771535",
    "14" => "125819771775",
    "8.4" => "125819772015",
    "18.7" => "125819772255",
    "15" => "125819772495",
    "14.3" => "125819772735",
    "16" => "125819772975",
    "9.4" => "125819773215",
    "16.1" => "125819773455",
    "18.6" => "125819773695",
    "7.5" => "125819773935",
    "8.5" => "125819774175",
    "7.2" => "125819774415",
    "10.5" => "125819774655",
    "7.3" => "125819774895",
    "14.7" => "125819775135",
    "18.4" => "125819775375",
    "11.9" => "125819775615",
    "8.1" => "125819775855",
    "11.3" => "125819776095",
    "7.6" => "125819776335",
    "8.2" => "125819776575",
    "19.5" => "125819776815",
    "11.2" => "125819777055",
    "10.4" => "125819777295",
    "18.1" => "125819777535",
    "8.7" => "125819777775",
    "19.6" => "125819778015",
    "19.4" => "125819778255",
    "14.4" => "125819778495",
    "17.6" => "125819778735",
    "19.8" => "125819778975",
    "14.2" => "125819779215",
    "12.5" => "125819779455",
    "10.2" => "125819779695",
    "11.8" => "125819779935",
    "12.4" => "125819780175",
    "12.6" => "125819780415",
    "13.3" => "125819780655",
    "13.5" => "125819780895",
    "15.4" => "125819781135",
    "14.8" => "125819781375",
);

$rtbSizes = array(
	"300x250" => "198988831215",
	"300x600" => "198988831455",
	"320x50" => "198988831695",
//	"336x280" => "198988831935",
	"728x90" => "198988832175",
);

foreach( $excludeBid as $exclude ) {
	if ( isset( $rtbBids[$exclude] ) ) {
		unset( $rtbBids[$exclude] );
	}
}

$lineItemsToCreate = array_keys( $rtbBids );

if ( $bid ) {
	if ( is_array( $bid ) ) {
		$lineItemsToCreate = $bid;
	} else {
		$lineItemsToCreate = array( $bid );
	}
}

$rtbNetworks = array(
	"Amazon" => "136011652815",
	"AppNexus" => "136011653055",
	"Pubmatic" => "169782519375",
	"AOL" => "169782519615",
	"DistrictM" => "177944954895",
	"PulsePoint" => "186430343775",
	"CPXi" => "198988830975",
	"RTK" => "209380854735",
	"TripleLift" => "209461299135",
	"Defy" => "209470752975",
	"Conversant" => "209473300815",
);

$rtbSites = array(
	"Grunge" => "61229775",
	"Looper" => "55678335",
	"NickiSwift" => "62479695",
	"TheList" => "65368935",
	"Mashed" => "69976935",
);

foreach( $lineItemsToCreate as $bidPrice ) {
	if ( empty( $rtbBids[$bidPrice] ) ) {
		die( "Missing Bid definition: {$bidPrice}\n" );
	}
}
if ( empty( $rtbNetworks[$network] ) ) {
	die( "Unknown network: {$network}" );
}
if ( empty( $rtbSites[$site] ) ) {
	die( "Unknown site: {$site}" );
}
if ( empty( $rtbSizes[$size] ) ) {
	die( "Unknown size: {$size}" );
}

$prefix = "{$site}_{$network}_{$size}_%s";


$lineItems = array();

try {
    // Get DfpUser from credentials in "../auth.ini"
    // relative to the DfpUser.php file's directory.
    $user = new DfpUser();

    // Log SOAP XML request and response.
    $user->LogDefaults();

    // Get the LineItemService.
    $lineItemService = $user->GetService('LineItemService', 'v201605');

    // Get the CustomTargetingService.
    $customTargetingService = $user->GetService('CustomTargetingService', 'v201605');

    // Create an array to store local line item objects.
    $lineItems = array();

    foreach ( $lineItemsToCreate as $bidPrice ) {
        print "Preparing {$bidPrice}\n";
        $customCriteria = new CustomCriteria();
        $customCriteria->keyId = $keySet['rtb_bid'];
        $customCriteria->operator = 'IS';
        $customCriteria->valueIds = array( $rtbBids[$bidPrice] );

        $customCriteriaNetwork = new CustomCriteria();
        $customCriteriaNetwork->keyId = $keySet['rtb_network'];
        $customCriteriaNetwork->operator = 'IS';
        $customCriteriaNetwork->valueIds = array( $rtbNetworks[$network] );

        $customCriteriaSize = new CustomCriteria();
	    $customCriteriaSize->keyId = $keySet['rtb_size'];
	    $customCriteriaSize->operator = 'IS';
	    $customCriteriaSize->valueIds = array( $rtbSizes[$size] );

        $subCustomCriteriaSet = new CustomCriteriaSet();
        $subCustomCriteriaSet->logicalOperator = 'AND';
        $subCustomCriteriaSet->children = array($customCriteria, $customCriteriaNetwork, $customCriteriaSize);

        $customCriteriaSet = new CustomCriteriaSet();
        $customCriteriaSet->logicalOperator = "OR";
        $customCriteriaSet->children = array( $subCustomCriteriaSet );

        // Create targeting.
        $targeting = new Targeting();

        $inventoryTargeting = new InventoryTargeting();

        $adUnitTargeting = new AdUnitTargeting();
        $adUnitTargeting->adUnitId = $rtbSites[$site];
        $adUnitTargeting->includeDescendants = 'TRUE';

        $inventoryTargeting->targetedAdUnits = array( $adUnitTargeting );

        $targeting->inventoryTargeting = $inventoryTargeting;
        $targeting->customTargeting = $customCriteriaSet;

        $lineItem = new LineItem();
        $lineItem->name = sprintf( $prefix, $bidPrice );
        $lineItem->orderId = $orderId;
        $lineItem->targeting = $targeting;
        $lineItem->lineItemType = 'PRICE_PRIORITY';
        $lineItem->allowOverbook = 'FALSE';

	    $sizeArr = explode( "x", $size );
	    $placeholder = new CreativePlaceholder();
	    $placeholder->size = new Size( $sizeArr[0], $sizeArr[1], false );

        // Set the size of creatives that can be associated with this line item.
        $lineItem->creativePlaceholders = array(
            $placeholder,
        );

        // Set the creative rotation type to even.
        $lineItem->creativeRotationType = 'OPTIMIZED';
        $lineItem->deliveryRateType = 'FRONTLOADED';
        $lineItem->roadblockingType = 'ONE_OR_MORE';

        // Set the length of the line item to run.
        $lineItem->startDateTimeType = 'IMMEDIATELY';
        $lineItem->endDateTime = null;
        $lineItem->unlimitedEndDateTime = 'TRUE';
        $lineItem->autoExtensionDays = 0;
        $lineItem->priority = 12;
        $lineItem->targetPlatform = 'ANY';
        $lineItem->environmentType = 'BROWSER';
        $lineItem->companionDeliveryOption = 'UNKNOWN';
        $lineItem->creativePersistenceType = 'NOT_PERSISTENT';
        $lineItem->reserveAtCreation = false;

        // Set the cost per unit to $2.
        $lineItem->costType = 'CPM';
        $lineItem->costPerUnit = new Money('USD', $bidPrice*1000000);

        $lineItem->status = 'PAUSED';

        // Set the number of units bought to 500,000 so that the budget is
        // $1,000.
        $goal = new Goal();
        $goal->units = -1;
        $goal->unitType = 'IMPRESSIONS';
        $goal->goalType = 'NONE';
        $lineItem->primaryGoal = $goal;

        $lineItems[] = $lineItem;
    }

    // Create the line items on the server.
    $lineItems = $lineItemService->createLineItems($lineItems);

    // Display results.
    if (isset($lineItems)) {
        foreach ($lineItems as $lineItem) {
            printf("A line item with with ID %d, belonging to order ID %d, and name "
                . "%s was created\n", $lineItem->id, $lineItem->orderId,
                $lineItem->name);
        }
    } else {
        printf("No line items created.");
    }

} catch (OAuth2Exception $e) {
    ExampleUtils::CheckForOAuth2Errors($e);
} catch (ValidationException $e) {
    ExampleUtils::CheckForOAuth2Errors($e);
} catch (Exception $e) {
    printf("%s\n", $e->getMessage());
}


