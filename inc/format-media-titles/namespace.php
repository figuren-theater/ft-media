<?php
/**
 * Figuren_Theater Media Format_Media_Titles.
 *
 * @package figuren-theater/ft-media
 */

namespace Figuren_Theater\Media\Format_Media_Titles;

use Figuren_Theater\Options;

use FT_VENDOR_DIR;

use function add_action;
use function is_admin;
use function is_network_admin;
use function is_user_admin;
use function remove_submenu_page;

const BASENAME   = 'format-media-titles/format-media-titles.php';
const PLUGINPATH = FT_VENDOR_DIR . '/wpackagist-plugin/' . BASENAME;

/**
 * Bootstrap module, when enabled.
 */
function bootstrap() {

	add_action( 'Figuren_Theater\loaded', __NAMESPACE__ . '\\filter_options', 11 );

	add_action( 'plugins_loaded', __NAMESPACE__ . '\\load_plugin', 9 );
}

function load_plugin() {

	// Do only load in "normal" admin view
	// Not for:
	// - public views
	// - network-admin views
	// - user-admin views
	if ( ! is_admin() || is_network_admin() || is_user_admin() )
		return;

	require_once PLUGINPATH;

	// unhook i18n
	remove_action( 'plugins_loaded', 'fmt_localize_plugin' );

	// Remove call to register_setting()
	remove_action( 'admin_init', 'fmt_init' );

	// Remove Submenu from 'Settings'
	remove_action( 'admin_menu', 'fmt_add_options_page' );
}

function filter_options() {

	$_options = [
		'chk_hyphen'             => '1',
		'chk_underscore'         => '1',
		'chk_period'             => '1',
		'chk_tilde'              => '1',
		'chk_plus'               => '1',
		'rdo_cap_options'        => 'cap_first',
		'chk_alt'                => '0',
		'chk_caption'            => '0',
		'chk_description'        => '0',
		'chk_default_options_db' => '1',
	];

	// gets added to the 'OptionsCollection'
	// from within itself on creation
	new Options\Option(
		'fmt_options',
		$_options,
		BASENAME
	);
}
