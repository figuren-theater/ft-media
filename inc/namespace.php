<?php
/**
 * Figuren_Theater Media.
 *
 * @package figuren-theater/media
 */

namespace Figuren_Theater\Media;

use Altis;
use function Altis\register_module;

const ASSETS_URL = WPMU_PLUGIN_URL . '/FT/ft-media/assets/';


/**
 * Register module.
 */
function register() {

	$default_settings = [
		'enabled' => true, // needs to be set
	];
	
	$options = [
		'defaults' => $default_settings,
	];

	Altis\register_module(
		'media',
		DIRECTORY,
		'Media',
		$options,
		__NAMESPACE__ . '\\bootstrap'
	);
}

/**
 * Bootstrap module, when enabled.
 */
function bootstrap() {

	// Plugins
	Attachment_Taxonomies\bootstrap();
	Format_Media_Titles\bootstrap();
	Modern_Images_WP\bootstrap();
	
	// Best practices
	Auto_Featured_Image\bootstrap();
	Image_Optimzation\bootstrap();
}
