<?php

foreach( $argv as $index => $arg ) {
	if ( stripos( $arg, "--" ) == 0 ) {
		$arr = explode( "=", $arg );

		if ( $arr[0] == "--network" ) {
			$network = $arr[1];
		}
	}
}

date_default_timezone_set("UTC");
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

$advertiserId = false;
$sizes = array();

try {
	// Get DfpUser from credentials in "../auth.ini"
	// relative to the DfpUser.php file's directory.
	$user = new DfpUser();

	// Log SOAP XML request and response.
	$user->LogDefaults();

	// Get the CreativeService.
	$orderService = $user->GetService('OrderService', 'v201605');

	// Create a statement to select all creatives.
	$statementBuilder = new StatementBuilder();
	$statementBuilder->Where( "name LIKE '%{$network}%'" );
	$statementBuilder->OrderBy('id ASC')
		->Limit(StatementBuilder::SUGGESTED_PAGE_LIMIT);

	// Default for total result set size.
	$totalResultSetSize = 0;

	do {
		// Get creatives by statement.
		$page = $orderService->getOrdersByStatement(
			$statementBuilder->ToStatement());

		// Display results.
		if (isset($page->results)) {
			$totalResultSetSize = $page->totalResultSetSize;
			$i = $page->startIndex;
			foreach ($page->results as $order) {
				$advertiserId = $order->advertiserId;
				list(
					$site,
					$x,
					$size
				) = explode( "_", $order->name );
				if ( !isset( $sizes[$order->id] ) ) {
					$sizes[$order->id] = $size;
				}
			}
		}

		$statementBuilder->IncreaseOffsetBy(StatementBuilder::SUGGESTED_PAGE_LIMIT);
	} while ($statementBuilder->GetOffset() < $totalResultSetSize);

	// Get the CreativeService.
	$creativeService = $user->GetService('CreativeService', 'v201605');
	$lineItemService = $user->GetService('LineItemService', 'v201605');

	foreach( $sizes as $orderId => $size ) {
		$creativeIds = array();

		// Get the CreativeService.
		$creativeService = $user->GetService('CreativeService', 'v201605');

		// Create a statement to select all creatives.
		$statementBuilder = new StatementBuilder();
		$statementBuilder->Where( "advertiserId = '{$advertiserId}' AND name LIKE '%{$size}%'" );
		$statementBuilder->OrderBy('id ASC')
			->Limit(StatementBuilder::SUGGESTED_PAGE_LIMIT);

		// Default for total result set size.
		$totalResultSetSize = 0;

		do {
			// Get creatives by statement.
			$page = $creativeService->getCreativesByStatement(
				$statementBuilder->ToStatement());

			// Display results.
			if (isset($page->results)) {
				$totalResultSetSize = $page->totalResultSetSize;
				$i = $page->startIndex;
				foreach ($page->results as $creative) {
					$creativeIds[] = $creative->id;
				}
			}

			$statementBuilder->IncreaseOffsetBy(StatementBuilder::SUGGESTED_PAGE_LIMIT);
		} while ($statementBuilder->GetOffset() < $totalResultSetSize);

		// Create a statement to select all line items.
		$statementBuilder = new StatementBuilder();
		$statementBuilder->Where( "orderId = '{$orderId}' AND id != 262883895" );
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
					if (!$lineItem->isArchived) {
						$bidLineItems[$lineItem->id] = $lineItem->name;
						$lineItemCreatives[$lineItem->id] = array();
						$cpm = $lineItem->costPerUnit->microAmount / 1000000;
						print "Line Item {$lineItem->name} CPM {$cpm}\n";//{$lineItem->costPerUnit}\n";
						$target = $lineItem->targeting->customTargeting->children[0]->children[0];

						if ( $target->keyId == "579975" ) {
							$lineItemCustomTargeting[$lineItem->id] = $target->valueIds[0];
						}
					}
				}
			}

			$statementBuilder->IncreaseOffsetBy(StatementBuilder::SUGGESTED_PAGE_LIMIT);
		} while ($statementBuilder->GetOffset() < $totalResultSetSize);

		$customTargetingService =
			$user->GetService('CustomTargetingService', 'v201605');

		if ( !sizeof( $lineItemCustomTargeting ) ) {
			continue;
		}

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
					$customTargetLabels[$customTargetingValue->id] = $customTargetingValue->name;
				}
			}

			$statementBuilder->IncreaseOffsetBy(StatementBuilder::SUGGESTED_PAGE_LIMIT);
		} while (intval( $statementBuilder->GetOffset()) < $totalResultSetSize);

		// Get the LineItemCreativeAssociationService.
		$licaService =
			$user->GetService('LineItemCreativeAssociationService', 'v201605');

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
                    printf("%d) LICA with line item ID %d, and creative ID %d was "
                        . "found.\n", $i++, $lica->lineItemId, $lica->creativeId);
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
			print( "\nCreating for line item $lineItemId:\n" );

			foreach( $creativeIds as $creativeId ) {
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



	}
} catch (OAuth2Exception $e) {
	ExampleUtils::CheckForOAuth2Errors($e);
} catch (ValidationException $e) {
	ExampleUtils::CheckForOAuth2Errors($e);
} catch (Exception $e) {
	printf("%s\n", $e->getMessage());
}