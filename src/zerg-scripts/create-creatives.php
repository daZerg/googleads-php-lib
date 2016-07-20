<?php
$network = false;

$creativeNetworks = array(
	"CPXi" => "110873415",
);

foreach( $argv as $index => $arg ) {
	if ( stripos( $arg, "--" ) == 0 ) {
		$arr = explode( "=", $arg );

		if ( $arr[0] == "--network" ) {
			$network = $arr[1];
		}
	}
}

if ( !isset( $creativeNetworks[$network] ) ) {
	die( "Invalid network provided.\n" );
}

error_reporting(E_STRICT | E_ALL);

// You can set the include path to src directory or reference
// DfpUser.php directly via require_once.
// $path = '/path/to/dfp_api_php_lib/src';
$path =  '../../src';
set_include_path(get_include_path() . PATH_SEPARATOR . $path);

require_once 'Google/Api/Ads/Common/Util/MediaUtils.php';
require_once 'Google/Api/Ads/Dfp/Lib/DfpUser.php';

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

// Set the ID of the advertiser (company) that all creatives will be assigned
// to.
$advertiserId = $creativeNetworks[$network];
$count = 5;

$sizes = array(
	"300x250" => array( 300, 250 ),
	"320x50" => array( 320, 50 ),
	"300x600" => array( 300, 600 ),
	"728x90" => array( 728, 90 ),
);

$size = "300x250";

try {
	// Get DfpUser from credentials in "../auth.ini"
	// relative to the DfpUser.php file's directory.
	$user = new DfpUser();

	// Log SOAP XML request and response.
	$user->LogDefaults();

	for ( $i = 1; $i <= $count; $i++ ) {

	}

	// Get the CreativeService.
	$creativeService = $user->GetService('CreativeService', 'v201605');

	$code = <<<HTML
<script language="javascript" type="text/javascript">
		window.parent.Looper.dfpCallback("%%PATTERN:ad_id%%")
</script>
HTML;

	$creatives = array();
	$n = "_2";

	// Create the local custom creative object.
	$customCreative = new ThirdPartyCreative();
	$customCreative->snippet = $code;
	$customCreative->expandedSnippet = $code;
	$customCreative->sslScanResult = "SCANNED_SSL";
	$customCreative->sslManualOverride = "NO_OVERRIDE";
	$customCreative->lockedOrientation = "FREE_ORIENTATION";
	$customCreative->advertiserId = $advertiserId;
	$customCreative->name = "{$network}_{$size}{$n}";
	$customCreative->size = new Size($sizes[$size][0], $sizes[$size][1], false);

	$creatives[] = $customCreative;

	// Create the custom creative on the server.
	$customCreatives = $creativeService->createCreatives(array($customCreative));

	foreach ($customCreatives as $customCreative) {
		printf("A custom creative with ID '%s', name '%s', and size '%sx%s' was "
			. "created and can be previewed at: %s\n", $customCreative->id,
			$customCreative->name, $customCreative->size->width,
			$customCreative->size->height, $customCreative->previewUrl);
	}
} catch (OAuth2Exception $e) {
	ExampleUtils::CheckForOAuth2Errors($e);
} catch (ValidationException $e) {
	ExampleUtils::CheckForOAuth2Errors($e);
} catch (Exception $e) {
	printf("%s\n", $e->getMessage());
}

