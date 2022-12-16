<?php
/**
 * Figuren_Theater Media Image_Source_Control_ISC.
 *
 * @package figuren-theater/media/image_source_control_isc
 */

namespace Figuren_Theater\Media\Image_Source_Control_ISC;

use FT_VENDOR_DIR;

use Figuren_Theater\Media;
use Figuren_Theater\Options;

use ISC_Admin;
use ISCVERSION;

use WP_POST;

use function add_action;
use function add_filter;
use function do_blocks;
use function get_option;
use function is_network_admin;
use function remove_action;
use function remove_submenu_page;

const BASENAME   = 'image-source-control-isc/isc.php';
const PLUGINPATH = FT_VENDOR_DIR . '/wpackagist-plugin/' . BASENAME;
const OPTION     = 'isc_options';

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

	add_filter( 'pre_option_' . OPTION, __NAMESPACE__ . '\\re_set_dynamic_options', 20 );
	
	add_filter( 'attachment_fields_to_edit', __NAMESPACE__ . '\\remove_attachment_fields', 20, 2 );

	// fake a table-block, to load its styles
	add_filter( 'do_shortcode_tag', __NAMESPACE__ . '\\load_block_table_styles', 10, 3 );

	add_action( 'admin_notices', __NAMESPACE__ . '\\remove_notices', 0 );
	add_action( 'admin_menu', __NAMESPACE__ . '\\remove_menu', 11 );

	// remove "Additional Images" from "Sources" Page, 
	// because it was reduced to just a big ad.
	add_action( 'admin_footer-media_page_isc-sources', __NAMESPACE__ . '\\remove_part_of_sources' );
	
	// add_action( 'after_setup_theme', __NAMESPACE__ . '\\enqueue_css_fix' );
}

function filter_options() {
	
	$_options = [
		'display_type' => [
			// 0 => 'list',
		],
		'list_on_archives'          => false,
		'list_on_excerpts'          => false,
		'image_list_headline'       => false,
		'version'                   => '', // will be re-set after ENABLE // prevents auto-updates of this options-field
		'thumbnail_in_list'         => false,
		'thumbnail_size'            => 'thumbnail',
		'thumbnail_width'           => 150,
		'thumbnail_height'          => 150,
		// 'warning_onesource_missing' will be changed by Feature 'advanced-isc'
		'warning_onesource_missing' => false,
		'remove_on_uninstall'       => false,
		'hide_list'                 => false,
		'caption_position'          => 'bottom-right',
		'caption_style'             => true, // remove markup and css
		'source_pretext'            => '', // will be re-set after ENABLE
		'enable_licences'           => true,
		// 'licences' will be changed by Feature 'advanced-isc'
		'licences'                  => 'All Rights Reserved
		Public Domain Mark 1.0|https://creativecommons.org/publicdomain/mark/1.0/
		CC0 1.0 Universal|https://creativecommons.org/publicdomain/zero/1.0/
		CC BY 4.0 International|https://creativecommons.org/licenses/by/4.0/
		CC BY-SA 4.0 International|https://creativecommons.org/licenses/by-sa/4.0/
		CC BY-ND 4.0 International|https://creativecommons.org/licenses/by-nd/4.0/
		CC BY-NC 4.0 International|https://creativecommons.org/licenses/by-nc/4.0/
		CC BY-NC-SA 4.0 International|https://creativecommons.org/licenses/by-nc-sa/4.0/
		CC BY-NC-ND 4.0 International|https://creativecommons.org/licenses/by-nc-nd/4.0/',
		'list_included_images'      => '',
		'overlay_included_images'   => '',
		'enable_log'                => false,
		'standard_source'           => 'custom_text',
		'standard_source_text'      => '', // will be re-set after ENABLE

	];

	// gets added to the 'OptionsCollection' 
	// from within itself on creation
	new Options\Option(
		'isc_options',
		$_options,
		BASENAME
	);
}

/**
 * [re_set_dynamic_options description]
 *
 * @package [package]
 * @since   2.10
 *
 * @param   array|bool      $option [description] could be false on WP_INSTALLING
 * @return  [type]            [description]
 */
function re_set_dynamic_options( array|bool $option ) : array {

	$option = ( is_array( $option ) ) ? $option : [];

	$_blogname = get_option( 'blogname' );

	// $_has_multiple_authors = ( !\Figuren_Theater\FT::site()->has_feature(['einsamer-wolf']) ) ? true : '';
	// $_permalink_structure = \Figuren_Theater\API::get('Options')->get( 'permalink_structure' );

	// prevents auto-updates of this options-field;
	$option['version']              = ISCVERSION; 
	$option['standard_source_text'] = '© ' . $_blogname;
	// $option['image_list_headline']  = __('Image Sources','image-source-control-isc');
	$option['source_pretext']       = __( 'Source:', 'image-source-control-isc' );
	
	return $option;
}

function remove_attachment_fields( array $fields, WP_POST $attachment ) : array {
	unset( $fields['isc_image_source_pro'] );
	return $fields;
}

/**
 * Fake a 'table'-block to load its styles.
 *
 * Normally this filters the output created by a shortcode callback.
 * 
 * @uses    https://developer.wordpress.org/reference/hooks/do_shortcode_tag/
 *
 * @package figuren-theater/media/image_source_control_isc
 *
 * @param   string       $output Shortcode output.
 * @param   string       $tag    Shortcode name.
 * @param   string|array $attr   Shortcode attributes array or empty string.
 * 
 * @return  string               Totally unchanged Shortcode output.
 */
function load_block_table_styles( string $output, string $tag, array|string $attr ) : string {
	// make sure it is the right shortcode
	if ( 'isc_list_all' !== $tag)
		return $output;


	// this triggers the loading of 'table-block'
	// related scripts and styles
	do_blocks( '<!-- wp:table {"className":"is-style-stripes"} --><!-- /wp:table -->' );

	enqueue_css_fix();

	return $output;
}

function remove_notices() : void {
	// CLEAN UP
	remove_action( 'admin_notices', [ ISC_Admin::get_instance(), 'branded_admin_header' ] );
}

function remove_menu() : void {
	remove_submenu_page( 'options-general.php', 'isc-settings' );
}

function remove_part_of_sources() {

	?>
	<script type="text/javascript">
		//<![CDATA[
		jQuery( document ).ready( function( $ ) {
			$( 'body.media_page_isc-sources' ).find( '.isc-table-storage' ).prev().remove();
			$( 'body.media_page_isc-sources' ).find( '.isc-table-storage' ).prev().remove();
			$( 'body.media_page_isc-sources' ).find( '.isc-table-storage' ).remove();
		} );
		//]]>
	</script>
	<?php
}


function enqueue_css_fix() {
	// Same args used for wp_enqueue_style().
	$args = array(
		'handle' => 'image-source-control-isc-fix',
		'src'    => Media\ASSETS_URL .'image-source-control-isc/fix.css',
	);

	// Add "path" to allow inlining asset if the theme opts-in.
	$args['path'] = Media\DIRECTORY . 'assets/image-source-control-isc/fix.css';

	// Enqueue asset.
	wp_enqueue_block_style( 'core/table', $args );
}
