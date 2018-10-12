<?php

$network = "";
$order = false;
foreach( $argv as $index => $arg ) {
	if ( stripos( $arg, "--" ) == 0 ) {
		$arr = explode( "=", $arg );

		if ( $arr[0] == "--network" ) {
			$network = $arr[1];
		} elseif ( $arr[0] == "--order" ) {
			$order = $arr[1];
		}
	}
}

date_default_timezone_set("UTC");
$path = '../../src';
set_include_path(get_include_path() . PATH_SEPARATOR . $path);

require '../../vendor/autoload.php';

use Google\AdsApi\Common\OAuth2TokenBuilder;
use Google\AdsApi\Dfp\DfpServices;
use Google\AdsApi\Dfp\DfpSession;
use Google\AdsApi\Dfp\DfpSessionBuilder;
use Google\AdsApi\Dfp\v201805\CreativeService;
use Google\AdsApi\Dfp\v201805\OrderService;
use Google\AdsApi\Dfp\v201805\LineItemService;
use Google\AdsApi\Dfp\v201805\CustomTargetingService;
use Google\AdsApi\Dfp\v201805\LineItemCreativeAssociation;
use Google\AdsApi\Dfp\v201805\LineItemCreativeAssociationService;
use Google\AdsApi\Dfp\Util\v201805\StatementBuilder;

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

$advertiserId = false;
$sizes = array();

try {
	$dfpServices = new DfpServices();

	// Get the CreativeService.
	$orderService = $dfpServices->get($session, OrderService::class);

	$where = "name LIKE '{$network}%'";
	if ( $order ) {
		$where = "id = '{$order}'";
	}
	// Create a statement to select all creatives.
	$statementBuilder = new StatementBuilder();
	$statementBuilder->Where( $where );
	$statementBuilder->OrderBy('id ASC')
		->Limit(StatementBuilder::SUGGESTED_PAGE_LIMIT);

	// Default for total result set size.
	$totalResultSetSize = 0;

	do {
		// Get creatives by statement.
		$page = $orderService->getOrdersByStatement(
			$statementBuilder->ToStatement() );

		// Display results.
		if ($page->getResults() !== null) {
			$totalResultSetSize = $page->getTotalResultSetSize();
			$i = $page->getStartIndex();
			foreach ( $page->getResults() as $order ) {
				$advertiserId = $order->getAdvertiserId();
				list(
					$site,
					$x,
					$size
				) = explode( "_", $order->getName() );
				if ( !isset( $sizes[$order->getId()] ) && $size ) {
					$sizes[$order->getId()] = $size;
				}
			}
		}

		$statementBuilder->IncreaseOffsetBy(StatementBuilder::SUGGESTED_PAGE_LIMIT);
	} while ($statementBuilder->GetOffset() < $totalResultSetSize);

	// Get the CreativeService.
	$creativeService = $dfpServices->get($session, CreativeService::class);
	$lineItemService = $dfpServices->get($session, LineItemService::class);

	foreach( $sizes as $orderId => $size ) {
		$creativeIds = array();

		// Create a statement to select all creatives.
		$statementBuilder = new StatementBuilder();
		$statementBuilder->Where( "advertiserId = '{$advertiserId}' AND name LIKE '{$network}_{$size}%' and id != '66040807575'" );
		$statementBuilder->OrderBy('id ASC')
			->Limit(StatementBuilder::SUGGESTED_PAGE_LIMIT);

		// Get creatives by statement.
		$page = $creativeService->getCreativesByStatement(
			$statementBuilder->ToStatement());

		// Default for total result set size.
		$totalResultSetSize = 0;

		do {
			// Get creatives by statement.
			$page = $creativeService->getCreativesByStatement(
				$statementBuilder->ToStatement());

			// Display results.
			if ($page->getResults() !== null) {
				$totalResultSetSize = $page->getTotalResultSetSize();
				$i = $page->getStartIndex();
				foreach ( $page->getResults() as $creative ) {
					print "Found Creative: {$creative->getId()}\n";
					$creativeIds[] = $creative->getId();
				}
			}

			$statementBuilder->IncreaseOffsetBy(StatementBuilder::SUGGESTED_PAGE_LIMIT);
		} while ($statementBuilder->GetOffset() < $totalResultSetSize);

		if ( !sizeof( $creativeIds ) ) {
			die( "Could not find creatives\n" );
		}

		// Create a statement to select all line items.
		$statementBuilder = new StatementBuilder();
		$statementBuilder->Where( "orderId = '{$orderId}'" );
		$statementBuilder->Limit(StatementBuilder::SUGGESTED_PAGE_LIMIT);

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
			if ($page->getResults() !== null) {
				$totalResultSetSize = $page->getTotalResultSetSize();
				$i = $page->getStartIndex();
				foreach ( $page->getResults() as $lineItem ) {
					if (!$lineItem->getIsArchived()) {
						$bidLineItems[$lineItem->getId()] = $lineItem->getName();
						$lineItemCreatives[$lineItem->getId()] = array();
						$cpm = $lineItem->getCostPerUnit()->getMicroAmount() / 1000000;
						print "Line Item {$lineItem->getName()} CPM {$cpm}\n";//{$lineItem->costPerUnit}\n";
						$targetArr = $lineItem->getTargeting()->getCustomTargeting()->getChildren()[0]->getChildren();

						foreach( $targetArr as $target ) {
							if ( $target->getKeyId() == "579975" ) {
								$lineItemCustomTargeting[$lineItem->getId()] = $target->getValueIds()[0];
							}
						}
					}
				}
			}

			$statementBuilder->IncreaseOffsetBy(StatementBuilder::SUGGESTED_PAGE_LIMIT);
		} while ($statementBuilder->GetOffset() < $totalResultSetSize);

		$customTargetingService = $dfpServices->get($session, CustomTargetingService::class);

		if ( !sizeof( $lineItemCustomTargeting ) ) {
			continue;
		}

		print ( "Custom Targeting\n" );

		// Create a statement to get all custom targeting values for a custom
		// targeting key.
		$statementBuilder = new StatementBuilder();
		$statementBuilder->Where('customTargetingKeyId = 579975 AND id IN ('.implode( ",", $lineItemCustomTargeting ).")" )
			->OrderBy('id ASC')
			->Limit(StatementBuilder::SUGGESTED_PAGE_LIMIT);

		$totalResultsCounter = 0;


		do {
			// Get custom targeting values by statement.
			$page = $customTargetingService->getCustomTargetingValuesByStatement(
				$statementBuilder->ToStatement());

			// Display results.
			if ($page->getResults() !== null) {
				$totalResultSetSize = $page->getTotalResultSetSize();
				$i = $page->getStartIndex();
				foreach ( $page->getResults() as $customTargetingValue ) {
					$customTargetLabels[$customTargetingValue->getId()] = $customTargetingValue->getName();
				}
			}

			$statementBuilder->IncreaseOffsetBy(StatementBuilder::SUGGESTED_PAGE_LIMIT);
		} while (intval( $statementBuilder->GetOffset()) < $totalResultSetSize);

		// Get the LineItemCreativeAssociationService.
		$licaService = $dfpServices->get($session, LineItemCreativeAssociationService::class);

		// Create a statement to select all LICAs.
		$statementBuilder = new StatementBuilder();
		$statementBuilder->Where( "lineItemId IN (".implode( ", ", array_keys($bidLineItems) ).")" );
		$statementBuilder->OrderBy('lineItemId ASC, creativeId ASC')
			->Limit(StatementBuilder::SUGGESTED_PAGE_LIMIT);

		// Default for total result set size.
		$totalResultSetSize = 0;

		$page = $licaService->getLineItemCreativeAssociationsByStatement(
			$statementBuilder->ToStatement());

		do {
			// Get LICAs by statement.
			// Display results.
			if ($page->getResults() !== null) {
				$totalResultSetSize = $page->getTotalResultSetSize();
				$i = $page->getStartIndex();
				foreach ( $page->getResults() as $lica ) {
					if ($lica->getCreativeSetId()) {
						printf("%d) LICA with line item ID %d, and creative set ID %d was "
							. "found.\n", $i++, $lica->getLineItemId(), $lica->getCreativeSetId());
					} else {
                    printf("%d) LICA with line item ID %d, and creative ID %d was "
                        . "found.\n", $i++, $lica->getLineItemId(), $lica->getCreativeId());
						if ( ! isset( $lineItemCreatives[$lica->getLineItemId()] ) ) {
							$lineItemCreatives[$lica->getLineItemId()] = array();
						}
						$lineItemCreatives[$lica->getLineItemId()][] = $lica->getCreativeId();
					}
				}
			}

			$statementBuilder->IncreaseOffsetBy(StatementBuilder::SUGGESTED_PAGE_LIMIT);
		} while ($statementBuilder->GetOffset() < $totalResultSetSize);

		foreach( $lineItemCreatives as $lineItemId => $creativeArr ) {
			print( "Line Item {$bidLineItems[$lineItemId]} rtb_bid is {$customTargetLabels[$lineItemCustomTargeting[$lineItemId]]} has ".sizeof( $creativeArr )." creatives\n" );
			print( "\nCreating for line item $lineItemId:\n" );

			foreach( $creativeIds as $creativeId ) {
				if ( !in_array( $creativeId, $creativeArr ) ) {
					try {
						print "Adding creative {$creativeId} to {$lineItemId}\n";

						$lica = new LineItemCreativeAssociation();
						$lica->setCreativeId( $creativeId );
						$lica->setLineItemId( $lineItemId );

						$licas = array( $lica );

						// Create the LICAs on the server.
						$licas = $licaService->createLineItemCreativeAssociations( $licas );

						// Display results.
						if ( isset( $licas ) ) {
							foreach ( $licas as $lica ) {
								print 'A LICA with line item ID "' . $lica->getLineItemId()
									. '", creative ID "' . $lica->getCreativeId()
									. '", and status "' . $lica->getStatus()
									. "\" was created.\n";
							}
						} else {
							print "No LICAs created.";
						}
					} catch( Exception $e ) {
						print $e->getMessage()."\n";
					}
				} else {
					print "Skipping creative {$creativeId} for {$lineItemId}\n";
				}
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