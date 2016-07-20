<?php

$testId = false;
if ( !empty( $argv[1] ) ) {
	$testId = $argv[1];
}
if ( !$testId ) {
	die( "Usage: php test.php CREATIVEID\n" );
}

date_default_timezone_set("UTC");
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

	// Get the CreativeService.
	$creativeService = $user->GetService('CreativeService', 'v201605');

	// Create a statement to select all creatives.
	$statementBuilder = new StatementBuilder();
	$statementBuilder->Where( "id = '{$testId}'" );
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
				var_dump( $creative );
			}
		}

		$statementBuilder->IncreaseOffsetBy(StatementBuilder::SUGGESTED_PAGE_LIMIT);
	} while ($statementBuilder->GetOffset() < $totalResultSetSize);

	printf("Number of results found: %d\n", $totalResultSetSize);
} catch (OAuth2Exception $e) {
	ExampleUtils::CheckForOAuth2Errors($e);
} catch (ValidationException $e) {
	ExampleUtils::CheckForOAuth2Errors($e);
} catch (Exception $e) {
	printf("%s\n", $e->getMessage());
}