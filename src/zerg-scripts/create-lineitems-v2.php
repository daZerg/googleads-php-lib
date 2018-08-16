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
		} elseif ( $arr[0] == "--exclude" || $arr[0] == "--exclude-bid" ) {
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
list(
	$site,
	$network,
	$size
) = explode( "_", $orderObj->getName() );

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


$criteoRtbBids = array(
	'0.6' => "123570084255",
	'0.7' => "123570084495",
	'0.8' => "123570084735",
	'0.9' => "123570084975",
	'1' => "123975030015",
	'1.1' => "123570085455",
	'1.2' => "123570085695",
	'1.3' => "123570085935",
	'1.4' => "123570086175",
	'1.5' => "123570086415",
	'1.6' => "123975102255",
	'1.7' => "123975102495",
	'1.8' => "123975102735",
	'1.9' => "123975102975",
	'2' => "123975103215",
	'2.1' => "123975103455",
	'2.2' => "123975103695",
	'2.3' => "123975103935",
	'2.4' => "123975104175",
	'2.5' => "123975104415",
	'2.6' => "123975104655",
	'2.7' => "123975104895",
	'2.8' => "123975113055",
	'2.9' => "123975110175",
	'3' => "123975110655",
	'3.1' => "123975113295",
	'3.2' => "123975109215",
	'3.3' => "123975114255",
	'3.4' => "123975108255",
	'3.5' => "123975110895",
	'3.6' => "123975111375",
	'3.7' => "123975109455",
	'3.8' => "123975115215",
	'3.9' => "123975114735",
	'4' => "123975106575",
	'4.1' => "123975114015",
	'4.2' => "123975114495",
	'4.3' => "123975113535",
	'4.4' => "123975106095",
	'4.5' => "123975105855",
	'4.6' => "123975110415",
	'4.7' => "123975106335",
	'4.8' => "123975105135",
	'4.9' => "123975112095",
	'5' => "123975107055",
	'5.1' => "123975111615",
	'5.2' => "123975108975",
	'5.3' => "123975111855",
	'5.4' => "123975109695",
	'5.5' => "123975111135",
	'5.6' => "123975112575",
	'5.7' => "123975107295",
	'5.8' => "123975112335",
	'5.9' => "123975108495",
	'6' => "123975107535",
	'6.1' => "123975109935",
	'6.2' => "123975105375",
	'6.3' => "123975106815",
	'6.4' => "123975113775",
	'6.5' => "123975108015",
	'6.6' => "123975108735",
	'6.7' => "123975105615",
	'6.8' => "123975107775",
	'6.9' => "123975112815",
	'7' => "123975114975",
	'7.1' => "125819767935",
	'7.2' => "125819774415",
	'7.3' => "125819774895",
	'7.4' => "125819760255",
	'7.5' => "125819773935",
	'7.6' => "125819776335",
	'7.7' => "125819765295",
	'7.8' => "125819755695",
	'7.9' => "125819758335",
	'8' => "125819761215",
	'8.1' => "125819775855",
	'8.2' => "125819776575",
	'8.3' => "125819769615",
	'8.4' => "125819772015",
	'8.5' => "125819774175",
	'8.6' => "125819765535",
	'8.7' => "125819777775",
	'8.8' => "125819754975",
	'8.9' => "125819766015",
	'9' => "125819760495",
	'9.1' => "125819751375",
	'9.2' => "125819766495",
	'9.3' => "125819751615",
	'9.4' => "125819773215",
	'9.5' => "125819759775",
	'9.6' => "125819758575",
	'9.7' => "125819758815",
	'9.8' => "125819751855",
	'9.9' => "125819770335",
	'10' => "125819767215",
	'10.1' => "125819763615",
	'10.2' => "125819779695",
	'10.3' => "125819760735",
	'10.4' => "125819777295",
	'10.5' => "125819774655",
	'10.6' => "125819768175",
	'10.7' => "125819758095",
	'10.8' => "125819750415",
	'10.9' => "125819756175",
	'11' => "125819760015",
	'11.1' => "125819761455",
	'11.2' => "125819777055",
	'11.3' => "125819776095",
	'11.4' => "125819765775",
	'11.5' => "125819752335",
	'11.6' => "125819769375",
	'11.7' => "125819763135",
	'11.8' => "125819779935",
	'11.9' => "125819775615",
	'12' => "125819757855",
	'12.1' => "125819753775",
	'12.2' => "125819753295",
	'12.3' => "125819753535",
	'12.4' => "125819780175",
	'12.5' => "125819779455",
	'12.6' => "125819780415",
	'12.7' => "125819761935",
	'12.8' => "125819754015",
	'12.9' => "125819770575",
	'13' => "125819764335",
	'13.1' => "125819769135",
	'13.2' => "125819770815",
	'13.3' => "125819780655",
	'13.4' => "125819767455",
	'13.5' => "125819780895",
	'13.6' => "125819754255",
	'13.7' => "125819759055",
	'13.8' => "125819769855",
	'13.9' => "125819771535",
	'14' => "125819771775",
	'14.1' => "125819766735",
	'14.2' => "125819779215",
	'14.3' => "125819772735",
	'14.4' => "125819778495",
	'14.5' => "125819753055",
	'14.6' => "125819756655",
	'14.7' => "125819775135",
	'14.8' => "125819781375",
	'14.9' => "125819759295",
	'15' => "125819772495",
	'15.1' => "125819752575",
	'15.2' => "125819757375",
	'15.3' => "125819755935",
	'15.4' => "125819781135",
	'15.5' => "125819760975",
	'15.6' => "125819752815",
	'15.7' => "125819756895",
	'15.8' => "125819761695",
	'15.9' => "125819755215",
	'16' => "125819772975",
	'16.1' => "125819773455",
	'16.2' => "125819764095",
	'16.3' => "125819751135",
	'16.4' => "125819768895",
	'16.5' => "125819754495",
	'16.6' => "125819762895",
	'16.7' => "125819764575",
	'16.8' => "125819754735",
	'16.9' => "125819771055",
	'17' => "125819766255",
	'17.1' => "125819757615",
	'17.2' => "125819757135",
	'17.3' => "125819762415",
	'17.4' => "125819767695",
	'17.5' => "125819755455",
	'17.6' => "125819778735",
	'17.7' => "125819763375",
	'17.8' => "125819756415",
	'17.9' => "125819764815",
	'18' => "125819765055",
	'18.1' => "125819777535",
	'18.2' => "125819762655",
	'18.3' => "125819768655",
	'18.4' => "125819775375",
	'18.5' => "125819762175",
	'18.6' => "125819773695",
	'18.7' => "125819772255",
	'18.8' => "125819763855",
	'18.9' => "125819770095",
	'19' => "125819759535",
	'19.1' => "125819766975",
	'19.2' => "125819752095",
	'19.3' => "125819771295",
	'19.4' => "125819778255",
	'19.5' => "125819776815",
	'19.6' => "125819778015",
	'19.7' => "125819768415",
	'19.8' => "125819778975",
	'19.9' => "125819750655",
	'20' => "125819750895",
	'20.2' => "447963408413",
	'20.4' => "447963408419",
	'20.6' => "447963408425",
	'20.8' => "447963408431",
	'21' => "447963408434",
	'21.2' => "447963408440",
	'21.4' => "447963408446",
	'21.6' => "447963408452",
	'21.8' => "447963408458",
	'22' => "447963408464",
	'22.2' => "447963408470",
	'22.4' => "447963408476",
	'22.6' => "447963408482",
	'22.8' => "447963408488",
	'23' => "447963408494",
	'23.2' => "447963408500",
	'23.4' => "447963408506",
	'23.6' => "447963408512",
	'23.8' => "447963366456",
	'24' => "447963408521",
	'24.2' => "447963408527",
	'24.4' => "447963408533",
	'24.6' => "447963408539",
	'24.8' => "447963408545",
	'25' => "447963408551",
	'25.2' => "447963408557",
	'25.4' => "447963408563",
	'25.6' => "447963408566",
	'25.8' => "447963408572",
	'26' => "447963408578",
	'26.2' => "447963408584",
	'26.4' => "447963408590",
	'26.6' => "447963408596",
	'26.8' => "447963408602",
	'27' => "447963408605",
	'27.2' => "447963408611",
	'27.4' => "447963408617",
	'27.6' => "447963408623",
	'27.8' => "447963408629",
	'28' => "447963408635",
	'28.2' => "447963408641",
	'28.4' => "447963408647",
	'28.6' => "447963408653",
	'28.8' => "447963408659",
	'29' => "447963408665",
	'29.2' => "447963408671",
	'29.4' => "447963408677",
	'29.6' => "447963408683",
	'29.8' => "447963408689",
	'30' => "447963408692",
	'30.2' => "447963408698",
	'30.4' => "447963408704",
	'30.6' => "447963408710",
	'30.8' => "447963408716",
	'31' => "447963408725",
	'31.2' => "447963408731",
	'31.4' => "447963408737",
	'31.6' => "447963408239",
	'31.8' => "447963408746",
	'32' => "447963408752",
	'32.2' => "447963408758",
	'32.4' => "447963041611",
	'32.6' => "447963041617",
	'32.8' => "447963041623",
	'33' => "447963366465",
	'33.2' => "447963041632",
	'33.4' => "447963041638",
	'33.6' => "447963041644",
	'33.8' => "447963041650",
	'34' => "447963041656",
	'34.2' => "447963041662",
	'34.4' => "447963041668",
	'34.6' => "447963041674",
	'34.8' => "447963041680",
	'35' => "447963041686",
	'35.2' => "447963041692",
	'35.4' => "447963041698",
	'35.6' => "447963041704",
	'35.8' => "447963041710",
	'36' => "447963041713",
	'36.2' => "447963041719",
	'36.4' => "447963041725",
	'36.6' => "447963041731",
	'36.8' => "447963041737",
	'37' => "447963041743",
	'37.2' => "447963041749",
	'37.4' => "447963041755",
	'37.6' => "447963408242",
	'37.8' => "447963041764",
	'38' => "447963041770",
	'38.2' => "447963041776",
	'38.4' => "447963041782",
	'38.6' => "447963041788",
	'38.8' => "447963041794",
	'39' => "447963366471",
	'39.2' => "447963041803",
	'39.4' => "447963041809",
	'39.6' => "447963041815",
	'39.8' => "447963041821",
	'40' => "447963041827",
	'40.2' => "447963041833",
	'40.4' => "447963041839",
	'40.6' => "447963041845",
	'40.8' => "447963041851",
	'41' => "447963041857",
	'41.2' => "447963041863",
	'41.4' => "447963041869",
	'41.6' => "447963041875",
	'41.8' => "447963041881",
	'42' => "447963366474",
	'42.2' => "447963041890",
	'42.4' => "447963041896",
	'42.6' => "447963041902",
	'42.8' => "447963041908",
	'43' => "447963367815",
	'43.2' => "447963367821",
	'43.4' => "447963367827",
	'43.6' => "447963367833",
	'43.8' => "447963367836",
	'44' => "447963367842",
	'44.2' => "447963367848",
	'44.4' => "447963367854",
	'44.6' => "447963367860",
	'44.8' => "447963367866",
	'45' => "447963366477",
	'45.2' => "447963367875",
	'45.4' => "447963367881",
	'45.6' => "447963367887",
	'45.8' => "447963367893",
	'46' => "447963367899",
	'46.2' => "447963367905",
	'46.4' => "447963367911",
	'46.6' => "447963367917",
	'46.8' => "447963367923",
	'47' => "447963367929",
	'47.2' => "447963367935",
	'47.4' => "447963367941",
	'47.6' => "447963367947",
	'47.8' => "447963367953",
	'48' => "447963366480",
	'48.2' => "447963367962",
	'48.4' => "447963367968",
	'48.6' => "447963367974",
	'48.8' => "447963367980",
	'49' => "447963367986",
	'49.2' => "447963367992",
	'49.4' => "447963367998",
	'49.6' => "447963368004",
	'49.8' => "447963368007",
	'50' => "447963368013",
	'50.5' => "447963368028",
	'51' => "447963368043",
	'51.5' => "447963368055",
	'52' => "447963368070",
	'52.5' => "447963368085",
	'53' => "447963368100",
	'53.5' => "447963368115",
	'54' => "447963368130",
	'54.5' => "447963368142",
	'55' => "447963368157",
	'55.5' => "447963368172",
	'56' => "447963368184",
	'56.5' => "447963368199",
	'57' => "447963368214",
	'57.5' => "447963368226",
	'58' => "447963368241",
	'58.5' => "447963368256",
	'59' => "447963408773",
	'59.5' => "447963408788",
	'60' => "447963408803",
	'60.5' => "447963408815",
	'61' => "447963408830",
	'61.5' => "447963408845",
	'62' => "447963408857",
	'62.5' => "447963408872",
	'63' => "447963408887",
	'63.5' => "447963408899",
	'64' => "447963408914",
	'64.5' => "447963408929",
	'65' => "447963408944",
	'65.5' => "447963366498",
	'66' => "447963408971",
	'66.5' => "447963408983",
	'67' => "447963409001",
	'67.5' => "447963409016",
	'68' => "447963409028",
	'68.5' => "447963409043",
	'69' => "447963409058",
	'69.5' => "447963041920",
	'70' => "447963041932",
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

if ( $network == "Criteo" ) {
	$rtbBids = $criteoRtbBids;
}

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
	"SmartAdserver" => "447855337384",
	"AdYouLike" => "447871550194",
	"33Across" => "447944510875",
	"Criteo" => "447963035779",
);

$rtbSites = array(
	"Grunge" => "61229775",
	"Looper" => "55678335",
	"NickiSwift" => "62479695",
	"TheList" => "65368935",
	"Mashed" => "69976935",
	"SVG" => "21667607955",
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
    // Get the LineItemService.
	$lineItemService =
		$dfpServices->get($session, LineItemService::class);

    // Get the CustomTargetingService.
    $customTargetingService =
	    $dfpServices->get($session, CustomTargetingService::class);

    // Create an array to store local line item objects.
    $lineItems = array();

    foreach ( $lineItemsToCreate as $bidPrice ) {
        print "Preparing {$bidPrice}\n";
        $customCriteria = new CustomCriteria();
        $customCriteria->setKeyId( $keySet['rtb_bid'] );
        $customCriteria->setOperator( 'IS' );
        $customCriteria->setValueIds( array( $rtbBids[$bidPrice] ) );

        $customCriteriaNetwork = new CustomCriteria();
        $customCriteriaNetwork->setKeyId( $keySet['rtb_network'] );
        $customCriteriaNetwork->setOperator( 'IS' );
        $customCriteriaNetwork->setValueIds( array( $rtbNetworks[$network] ) );

        $customCriteriaSize = new CustomCriteria();
	    $customCriteriaSize->setKeyId( $keySet['rtb_size'] );
	    $customCriteriaSize->setOperator( 'IS' );
	    $customCriteriaSize->setValueIds( array( $rtbSizes[$size] ) );

        $subCustomCriteriaSet = new CustomCriteriaSet();
        $subCustomCriteriaSet->setLogicalOperator( 'AND' );
        $subCustomCriteriaSet->setChildren( array($customCriteria, $customCriteriaNetwork, $customCriteriaSize) );

        $customCriteriaSet = new CustomCriteriaSet();
        $customCriteriaSet->setLogicalOperator( "OR" );
        $customCriteriaSet->setChildren( array( $subCustomCriteriaSet ) );

        // Create targeting.
        $targeting = new Targeting();

        $inventoryTargeting = new InventoryTargeting();

        $adUnitTargeting = new AdUnitTargeting();
        $adUnitTargeting->setAdUnitId( $rtbSites[$site] );
        $adUnitTargeting->setIncludeDescendants( 'TRUE' );

        $inventoryTargeting->setTargetedAdUnits( array( $adUnitTargeting ) );

        $targeting->setInventoryTargeting( $inventoryTargeting );
        $targeting->setCustomTargeting( $customCriteriaSet );

        $lineItem = new LineItem();
        $lineItem->setName( sprintf( $prefix, $bidPrice ) );
        $lineItem->setOrderId( $orderId );
        $lineItem->setTargeting( $targeting );
        $lineItem->setLineItemType( 'PRICE_PRIORITY' );
        $lineItem->setAllowOverbook( 'FALSE' );

	    $sizeArr = explode( "x", $size );
	    $placeholder = new CreativePlaceholder();
	    $placeholder->setSize( new Size( $sizeArr[0], $sizeArr[1], false ) );

        // Set the size of creatives that can be associated with this line item.
        $lineItem->setCreativePlaceholders( array(
            $placeholder,
        ) );

        // Set the creative rotation type to even.
        $lineItem->setCreativeRotationType( 'OPTIMIZED' );
        $lineItem->setDeliveryRateType( 'FRONTLOADED' );
        $lineItem->setRoadblockingType( 'ONE_OR_MORE' );

        // Set the length of the line item to run.
        $lineItem->setStartDateTimeType( 'IMMEDIATELY');
        $lineItem->setEndDateTime( null);
        $lineItem->setUnlimitedEndDateTime( 'TRUE');
        $lineItem->setAutoExtensionDays( 0);
        $lineItem->setPriority( 12);
//        $lineItem->setTargetPlatform( 'ANY');
        $lineItem->setEnvironmentType( 'BROWSER');
        $lineItem->setCompanionDeliveryOption( 'UNKNOWN');
        $lineItem->setReserveAtCreation( false);

        // Set the cost per unit to $2.
        $lineItem->setCostType( 'CPM');
        $lineItem->setCostPerUnit( new Money('USD', $bidPrice*1000000));

        $lineItem->setStatus( 'PAUSED');

        // Set the number of units bought to 500,000 so that the budget is
        // $1,000.
        $goal = new Goal();
        $goal->setUnits( -1);
        $goal->setUnitType( 'IMPRESSIONS');
        $goal->setGoalType( 'NONE');
        $lineItem->setPrimaryGoal( $goal);

	    $lineItems[] = $lineItem;
	    if ( sizeof( $lineItems ) > 10 ) {
		    $lineItems = $lineItemService->createLineItems($lineItems);

		    if (isset($lineItems)) {
			    foreach ($lineItems as $lineItem) {
				    printf("A line item with with ID %d, belonging to order ID %d, and name "
					    . "%s was created\n", $lineItem->getId(), $lineItem->getOrderId(),
					    $lineItem->getName());
			    }
		    } else {
			    printf("No line items created.");
		    }

		    $lineItems = array();
	    }
    }

	if ( sizeof( $lineItems ) ) {
		$lineItems = $lineItemService->createLineItems($lineItems);

		if (isset($lineItems)) {
			foreach ($lineItems as $lineItem) {
				printf("A line item with with ID %d, belonging to order ID %d, and name "
					. "%s was created\n", $lineItem->getId(), $lineItem->getOrderId(),
					$lineItem->getName());
			}
		} else {
			printf("No line items created.");
		}
	}

} catch (OAuth2Exception $e) {
    ExampleUtils::CheckForOAuth2Errors($e);
} catch (ValidationException $e) {
    ExampleUtils::CheckForOAuth2Errors($e);
} catch (Exception $e) {
    printf("%s\n", $e->getMessage());
}


