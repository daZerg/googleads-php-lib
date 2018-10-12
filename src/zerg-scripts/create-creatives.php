<?php
$network = false;

$creativeNetworks = array(
	"CPXi" => "110873415",
	"RTK" => "112944015",
	"Conversant" => "83959695",
	"SmartAdserver" => "4415639943",
	"AdYouLike" => "4451034716",
	"DistrictM" => "106940655",
	"AppNexus" => "96418335",
	"33Across" => "111612855",
	"Criteo" => "50444535",
	"Undertone" => "94257015",
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

require '../../vendor/autoload.php';

use Google\AdsApi\Common\OAuth2TokenBuilder;
use Google\AdsApi\Dfp\DfpServices;
use Google\AdsApi\Dfp\DfpSession;
use Google\AdsApi\Dfp\DfpSessionBuilder;
use Google\AdsApi\Dfp\v201805\CreativeService;
use Google\AdsApi\Dfp\v201805\ThirdPartyCreative;
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

// Set the ID of the advertiser (company) that all creatives will be assigned
// to.
$advertiserId = $creativeNetworks[$network];
$count = 4;

$sizes = array(
//	"1x1" => array( 1, 1 ),
//	"1x3" => array( 1, 3 ),
	"300x250" => array( 300, 250 ),
//	"320x50" => array( 320, 50 ),
//	"320x100" => array( 320, 100 ),
//	"300x600" => array( 300, 600 ),
//	"728x90" => array( 728, 90 ),
//	"970x90" => array( 970, 90 ),
//	"970x250" => array( 970, 250 ),
);

try {
	$dfpServices = new DfpServices();
	// Get DfpUser from credentials in "../auth.ini"

	// Get the CreativeService.
	$creativeService = $dfpServices->get($session, CreativeService::class);

	$code = <<<HTML
<script language="javascript" type="text/javascript">
		window.parent.Looper.dfpCallback("%%PATTERN:ad_id%%")
</script>
HTML;

	for ( $i = 1; $i <= $count; $i++ ) {
		foreach( $sizes as $size => $arr ) {
			$n = "_$i";
			if ( $i == 1 ) {
				$n = "";
			}

			// Create the local custom creative object.
			$customCreative = new ThirdPartyCreative();
			$customCreative->setSnippet( $code );
			$customCreative->setExpandedSnippet( $code );
			$customCreative->setSslScanResult( "SCANNED_SSL" );
			$customCreative->setSslManualOverride( "NO_OVERRIDE" );
			$customCreative->setLockedOrientation( "FREE_ORIENTATION" );
			$customCreative->setAdvertiserId( $advertiserId );
			$customCreative->setIsSafeFrameCompatible( false );
			$customCreative->setName( "{$network}_{$size}{$n}" );
			$customCreative->setSize( new Size($sizes[$size][0], $sizes[$size][1], false) );

			// Create the custom creative on the server.
			$customCreatives = $creativeService->createCreatives(array($customCreative));

			foreach ($customCreatives as $customCreative) {
				printf("A custom creative with ID '%s', name '%s', and size '%sx%s' was "
					. "created and can be previewed at: %s\n", $customCreative->getId(),
					$customCreative->getName(), $sizes[$size][0], $sizes[$size][1],
					$customCreative->getPreviewUrl() );
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

