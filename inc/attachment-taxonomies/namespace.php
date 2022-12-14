<?php
/**
 * Figuren_Theater Media Attachment_Taxonomies.
 *
 * @package figuren-theater/media/attachment_taxonomies
 */

namespace Figuren_Theater\Media\Attachment_Taxonomies;

use FT_VENDOR_DIR;

use function add_action;
use function is_network_admin;

const BASENAME   = 'attachment-taxonomies/attachment-taxonomies.php';
const PLUGINPATH = FT_VENDOR_DIR . '/wpackagist-plugin/' . BASENAME;

/**
 * Bootstrap module, when enabled.
 */
function bootstrap() {

	add_action( 'plugins_loaded', __NAMESPACE__ . '\\load_plugin', 0 );
}

function load_plugin() {

	if ( is_network_admin() )
		return;
	
	require_once PLUGINPATH;
}
