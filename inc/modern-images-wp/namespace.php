<?php
/**
 * Figuren_Theater Media Modern_Images_WP.
 *
 * @package figuren-theater/ft-media
 */

namespace Figuren_Theater\Media\Modern_Images_WP;

use Figuren_Theater\Options;

use FT_VENDOR_DIR;

use DOING_AJAX;

use function add_action;
use function add_filter;
use function is_network_admin;
use function remove_submenu_page;

const BASENAME   = 'modern-images-wp/modern-images-wp.php';
const PLUGINPATH = FT_VENDOR_DIR . '/wpackagist-plugin/' . BASENAME;
const OPTION     = 'modern-images-wp-setting';

/**
 * Bootstrap module, when enabled.
 */
function bootstrap() {

	add_action( 'Figuren_Theater\loaded', __NAMESPACE__ . '\\filter_options', 11 );

	add_action( 'plugins_loaded', __NAMESPACE__ . '\\load_plugin' );
}

function load_plugin() {

	if ( is_network_admin() )
		return;

	require_once PLUGINPATH;

	// make sure this runs on ajax requests
	add_action( 'admin_init', __NAMESPACE__ . '\\wp_ajax_upload_attachment' );

	add_action( 'admin_menu', __NAMESPACE__ . '\\remove_menu', 11 );
}

function filter_options() {

	$_options = [
		'modern_image_output_format_for_jpeg' => 'image/webp',
		// 'modern_image_output_format_for_jpeg' => '', // DEBUG only
		'modern_image_output_format_for_gif'  => '',
		'modern_image_output_format_for_png'  => 'image/webp',
	];

	// gets added to the 'OptionsCollection'
	// from within itself on creation
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
	if (
		   ! defined( 'DOING_AJAX' )
		|| ! DOING_AJAX
		|| ! isset( $_REQUEST['action'] )
		|| 'crop-image' !== $_REQUEST['action']
		|| ! isset( $_REQUEST['context'] )
		|| 'site-icon' !== $_REQUEST['context']
	) {
		return;
	}

	// change option
	// for this use-case
	// keep png as png to stay with pwa standards for the siteicons
	#$this->options['modern_image_output_format_for_png'] = '';
	#$_option = \Figuren_Theater\API::get('Options')->get( 'option_'.$this->option_name );
	#$_option->set_value( $this->options );
	add_filter( 'pre_option_' . OPTION, __NAMESPACE__ . '\\no_webp_for_siteicons', 20 );
}


function no_webp_for_siteicons( array $options ) : array {
	$options['modern_image_output_format_for_png'] = '';
	return $options;
}

function remove_menu() : void {
	remove_submenu_page( 'options-general.php', BASENAME );
}
