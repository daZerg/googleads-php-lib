<?php

$size = false;
$network = false;
$site = false;
$bid = false;
$orderId = false;
$excludeBid = array();
$orderObj = false;
$start = false;

$configVars = array();
foreach( $argv as $index => $arg ) {
	if ( stripos( $arg, "--" ) == 0 ) {
		$arr = explode( "=", $arg );

		if ( $arr[0] == "--size" ) {
			$size = $arr[1];
			$configVars['size'] = $size;
		} elseif ( $arr[0] == "--network" ) {
			$network = $arr[1];
			$configVars['network'] = $network;
		} elseif ( $arr[0] == "--site" ) {
			$site = $arr[1];
			$configVars['site'] = $site;
		} elseif ( $arr[0] == "--order" ) {
			$orderId = $arr[1];
			$configVars['order'] = $orderId;
		} elseif ( $arr[0] == "--start" ) {
			$start = $arr[1];
			$configVars['start'] = $start;
		}
	}
}

date_default_timezone_set("UTC");
error_reporting(E_STRICT | E_ALL);

$path = '../../src';
set_include_path(get_include_path() . PATH_SEPARATOR . $path);

require '../../vendor/autoload.php';

use Google\AdsApi\Common\OAuth2TokenBuilder;
use Google\AdsApi\Dfp\DfpServices;
use Google\AdsApi\Dfp\DfpSession;
use Google\AdsApi\Dfp\DfpSessionBuilder;
use Google\AdsApi\Dfp\Util\v201805\StatementBuilder;
use Google\AdsApi\Dfp\v201805\LineItemService;
use Google\AdsApi\Dfp\v201805\LineItem;
use Google\AdsApi\Dfp\v201805\OrderService;
use Google\AdsApi\Dfp\v201805\CustomTargetingService;
use Google\AdsApi\Dfp\v201805\CustomCriteria;
use Google\AdsApi\Dfp\v201805\CustomCriteriaSet;
use Google\AdsApi\Dfp\v201805\Targeting;
use Google\AdsApi\Dfp\v201805\InventoryTargeting;
use Google\AdsApi\Dfp\v201805\AdUnitTargeting;
use Google\AdsApi\Dfp\v201805\Money;
use Google\AdsApi\Dfp\v201805\Goal;
use Google\AdsApi\Dfp\v201805\CreativePlaceholder;
use Google\AdsApi\Dfp\v201805\Size;

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

	// Get the LineItemService.
	$orderService = $dfpServices->get($session, OrderService::class);

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
		if ($page->getResults() !== null) {
			$totalResultSetSize = $page->getTotalResultSetSize();
			$i = $page->getStartIndex();
			foreach ( $page->getResults() as $order ) {
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
$orderId = $orderObj->getId();
$orderName = $orderObj->getName();
//list(
//	$site,
//	$network,
//	$size
//) = explode( "_", $orderName );

$keySet = array(
	"rtb_bid" => "579975",
	"rtb_network" => "598335",
	"rtb_size" => "696135",
);

$rtbSizes = array(
	"300x250" => "198988831215",
	"300x600" => "198988831455",
	"320x50" => "198988831695",
	"320x100" => "447903539994",
//	"336x280" => "198988831935",
	"728x90" => "198988832175",
	"970x90" => "447903729080",
	"970x250" => "447903727706",
);

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
	"SmartAdserver" => "447855337384",
	"AdYouLike" => "447871550194",
	"33Across" => "447944510875",
	"Criteo" => "447963035779",
);

$adUnits = array(
	"Mashed" => array(
		"base" => "69976935",
		"gallery" => "21732487972",
		"infinite" => "21732488875"
	),
	"TheList" => array(
		"base" => "65368935",
	),
	"Grunge" => array(
		"base" => "61229775",
	),

);

//if ( $configVars['site'] != $site && stripos( $orderName, $configVars['site'] ) !== false ) {
//	$site = $configVars['site'];
//}

$lineItems = array();

try {
    // Get the LineItemService.
	$lineItemService =
		$dfpServices->get($session, LineItemService::class);

    // Get the CustomTargetingService.
    $customTargetingService =
	    $dfpServices->get($session, CustomTargetingService::class);

	$where = "orderId = $orderId";
	if ( !empty( $start ) ) {
		$where .= " AND id >= {$start}";
	}

	// Create a statement to select all line items.
	$statementBuilder = new StatementBuilder();
	$statementBuilder->Where( $where );
	$statementBuilder->OrderBy('id ASC')
		->Limit(StatementBuilder::SUGGESTED_PAGE_LIMIT);

	// Default for total result set size.
	$totalResultSetSize = 0;

	do {
		// Get line items by statement.
		$page = $lineItemService->getLineItemsByStatement(
			$statementBuilder->ToStatement());

		// Display results.
		if ($page->getResults() !== null && !empty( $adUnits[$site] ) ) {
			$totalResultSetSize = $page->getTotalResultSetSize();
			$i = $page->getStartIndex();
			foreach ( $page->getResults() as $lineItem ) {
				$targeting = $lineItem->getTargeting();
				$inventoryTargeting = $targeting->getInventoryTargeting();

				$currentTargets = $inventoryTargeting->getTargetedAdUnits();

				$adUnitTargetingGallery = new AdUnitTargeting();
				$adUnitTargetingGallery->setAdUnitId( $adUnits[$site]['gallery'] );
				$adUnitTargetingGallery->setIncludeDescendants( 'TRUE' );

				$adUnitTargetingInfinite = new AdUnitTargeting();
				$adUnitTargetingInfinite->setAdUnitId( $adUnits[$site]['infinite'] );
				$adUnitTargetingInfinite->setIncludeDescendants( 'TRUE' );

				$inventoryTargeting->setTargetedAdUnits( array_merge( $currentTargets, array( $adUnitTargetingGallery, $adUnitTargetingInfinite ) ) );
				$targeting->setInventoryTargeting( $inventoryTargeting );

				echo "Updating Line Item {$lineItem->getId()}\n";
				$lineItemService->updateLineItems(array($lineItem));
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


