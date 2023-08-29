<?php
/**
 * Figuren_Theater Media Modern_Images_WP.
 *
 * @package figuren-theater/ft-media
 */

namespace Figuren_Theater\Media\Modern_Images_WP;

use DOING_AJAX;

use Figuren_Theater\Options;

use FT_VENDOR_DIR;

use function add_action;
use function add_filter;
use function is_network_admin;
use function remove_submenu_page;

const BASENAME   = 'modern-images-wp/modern-images-wp.php';
const PLUGINPATH = '/wpackagist-plugin/' . BASENAME;
const OPTION     = 'modern-images-wp-setting';

/**
 * Bootstrap module, when enabled.
 *
 * @return void
 */
function bootstrap() :void {

	add_action( 'Figuren_Theater\loaded', __NAMESPACE__ . '\\filter_options', 11 );

	add_action( 'plugins_loaded', __NAMESPACE__ . '\\load_plugin' );
}

/**
 * Conditionally load the plugin itself and its modifications.
 *
 * @return void
 */
function load_plugin() :void {

	if ( is_network_admin() ) {
		return;
	}

	require_once FT_VENDOR_DIR . PLUGINPATH; // phpcs:ignore WordPressVIPMinimum.Files.IncludingFile.UsingCustomConstant

	// Make sure this runs on ajax requests.
	add_action( 'admin_init', __NAMESPACE__ . '\\wp_ajax_upload_attachment' );

	add_action( 'admin_menu', __NAMESPACE__ . '\\remove_menu', 11 );
}

/**
 * Handle options
 *
 * @return void
 */
function filter_options() :void {

	$_options = [
		'modern_image_output_format_for_jpeg' => 'image/webp', // '' // Leave empty for DEBUG only.
		'modern_image_output_format_for_gif'  => '',
		'modern_image_output_format_for_png'  => 'image/webp',
	];

	/*
	 * Gets added to the 'OptionsCollection'
	 * from within itself on creation.
	 */
	new Options\Option(
		OPTION,
		$_options,
		BASENAME
	);
}

/**
 * Nice jump-in-point to prevent our site-icon upload
 * from getting proccessed as webp.
 * Because '.png' is must-have for a nice PWA.
 *
 * This is originally used to: ...
 *
 * [wp_ajax_upload_attachment description]
 *
 * @package project_name
 * @version version
 * @author  Carsten Bach
 *
 * @return  [type]       [description]
 */
function wp_ajax_upload_attachment() {
	/**
	 * If we're not performing our AJAX request, return early.
	 */
	if ( ! defined( 'DOING_AJAX' )
		|| ! DOING_AJAX
		|| ! isset( $_REQUEST['action'] )
		|| 'crop-image' !== $_REQUEST['action']
		|| ! isset( $_REQUEST['context'] )
		|| 'site-icon' !== $_REQUEST['context']
	) {
		return;
	}

	// Change option for this use-case.
	// Keep png as png to stay with pwa standards for the siteicons.
	add_filter( 'pre_option_' . OPTION, __NAMESPACE__ . '\\no_webp_for_siteicons', 20 );
}

/**
 * Filter the output format of PNG files
 *
 * Keep png as png to stay with pwa standards for the siteicons.
 *
 * @param  array<string, string> $options Array of 'modern-images-wp-setting' options.
 *
 * @return array<string, string>
 */
function no_webp_for_siteicons( array $options ) : array {
	$options['modern_image_output_format_for_png'] = '';
	return $options;
}

/**
 * Remove the plugins admin-menu.
 *
 * @return void
 */
function remove_menu() : void {
	remove_submenu_page( 'options-general.php', BASENAME );
}
