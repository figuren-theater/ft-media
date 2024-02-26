<?php
/**
 * Figuren_Theater Media.
 *
 * @package figuren-theater/ft-media
 */

namespace Figuren_Theater\Media;

use Altis;

use function is_admin;

/**
 * Register module.
 *
 * @return void
 */
function register(): void {

	$default_settings = [
		'enabled' => is_admin(), // Needs to be set.
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
 *
 * @return void
 */
function bootstrap(): void {

	// Plugins.
	Attachment_Taxonomies\bootstrap();
	Format_Media_Titles\bootstrap();
	Modern_Images_WP\bootstrap();

	// Best practices.
	Auto_Featured_Image\bootstrap();
	Image_Optimization\bootstrap();
}
