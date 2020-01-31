<?php

$site = false;
$orderId = false;
$start = false;

foreach( $argv as $index => $arg ) {
    if ( stripos( $arg, "--" ) == 0 ) {
        $arr = explode( "=", $arg );

        if ( $arr[0] == "--start" ) {
            $start = $arr[1];
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

if ( !$site || !$orderId ) {
    die( "--site and --order are required.\n" );
}

date_default_timezone_set("UTC");
error_reporting(E_STRICT | E_ALL);

$path = '../../src';
set_include_path(get_include_path() . PATH_SEPARATOR . $path);

require '../../vendor/autoload.php';

use Google\AdsApi\Common\OAuth2TokenBuilder;
use Google\AdsApi\AdManager\AdManagerServices;
use Google\AdsApi\AdManager\AdManagerSession;
use Google\AdsApi\AdManager\AdManagerSessionBuilder;
use Google\AdsApi\AdManager\Util\v201905\StatementBuilder;
use Google\AdsApi\AdManager\v201905\LineItemService;
use Google\AdsApi\AdManager\v201905\LineItem;
use Google\AdsApi\AdManager\v201905\OrderService;
use Google\AdsApi\AdManager\v201905\CustomTargetingService;
use Google\AdsApi\AdManager\v201905\CustomCriteria;
use Google\AdsApi\AdManager\v201905\CustomCriteriaSet;
use Google\AdsApi\AdManager\v201905\Targeting;
use Google\AdsApi\AdManager\v201905\InventoryTargeting;
use Google\AdsApi\AdManager\v201905\AdUnitTargeting;

$oAuth2Credential = (new OAuth2TokenBuilder())
    ->fromFile()
    ->build();

// Construct an API session configured from a properties file and the OAuth2
// credentials above.
$session = (new AdManagerSessionBuilder())
    ->fromFile()
    ->withOAuth2Credential($oAuth2Credential)
    ->build();

try {
    $dfpServices = new AdManagerServices();

    // Get the LineItemService.
    $lineItemService =
        $dfpServices->get($session, LineItemService::class);

    $where = "orderId = '{$orderId}'";
    if ( $start ) {
        $where .= " AND id > {$start}";
    }

    // Create a statement to select all line items.
    $statementBuilder = new StatementBuilder();
    $statementBuilder->Where( $where );
    $statementBuilder->OrderBy('id ASC')
        ->Limit(StatementBuilder::SUGGESTED_PAGE_LIMIT);

    // Default for total result set size.
    $totalResultSetSize = 0;

    $toUpdate = array();
    $targetIds = array(
        "grunge" => array(
            "61229775", //OG
            "21773027999", //Grunge Gallery
            "21907503861", //Grunge Organic
        ),
        "looper" => array(
            "55678335", //OG
            "21773158155", //Looper Gallery
            "21907499016", //Looper Organic
        ),
        "mashed" => array(
            "69976935", //OG
            "21732487972", //Mashed Gallery
            "21907310302", //Mahsed Organic
        ),
        "nickiswift" => array(
            "62479695", //OG
            "21773152470", //NickiSwift Gallery
            "21907498647", //NickiSwift Organic
        ),
        "svg" => array(
            "21667607955", //OG
            "21773028419", //SVG Gallery
            "21907497321", //SVG Organic
        ),
        "thelist" => array(
            "65368935", //OG
            "21773014493", //TheList Gallery
            "21907497033", //TheList Organic
        ),

    );

    do {
        // Get line items by statement.
        $page = $lineItemService->getLineItemsByStatement(
            $statementBuilder->ToStatement());

        $totalResultSetSize = $page->getTotalResultSetSize();
        // Display results.
        if ($totalResultSetSize) {
            print "Total: {$totalResultSetSize}\n";
            $i = $page->getStartIndex();
            foreach ( $page->getResults() as $lineItem ) {
                if ( !$lineItem->getIsArchived() ) {
                    $targeting = $lineItem->getTargeting();
                    $inventoryTargeting = $targeting->getInventoryTargeting();
                    $targetedAdUnits = $inventoryTargeting->getTargetedAdUnits();
                    $missing = array();
                    foreach( $targetIds[$site] as $targetId ) {
                        $found = false;
                        foreach( $targetedAdUnits as $adUnit ) {
                            if ($adUnit->getAdUnitId() == $targetId) {
                                $found = true;
                            }
                        }
                        if ( !$found ) {
                            $missing[] = $targetId;
                        }
                    }
                    if( sizeof( $missing ) ) {
                        foreach( $missing as $targetId ) {
                            $adUnitTargeting = new AdUnitTargeting();
                            $adUnitTargeting->setAdUnitId($targetId);
                            $adUnitTargeting->setIncludeDescendants('TRUE');

                            $targetedAdUnits[] = $adUnitTargeting;
                        }
                        $inventoryTargeting->setTargetedAdUnits( $targetedAdUnits );
                        $targeting->setInventoryTargeting( $inventoryTargeting );
                        $lineItem->setTargeting( $targeting );

                        try {
                            $lineItemService->updateLineItems(array($lineItem));
                        } catch( Exception $e ) {
                            print $e->getMessage()."\n";
                        }
                        print "{$lineItem->getId()} updated\n";
                    } else {
                        print "{$lineItem->getId()}: NO CHANGE\n";
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
