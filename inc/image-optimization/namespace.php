<?php
/**
 * Figuren_Theater Media Image_Optimzation.
 *
 * @package figuren-theater/ft-media
 */

namespace Figuren_Theater\Media\Image_Optimzation;

use function add_filter;
use IMAGETYPE_GIF;
use IMAGETYPE_JPEG;

use IMAGETYPE_PNG;

use Imagick;

/**
 * Bootstrap module, when enabled.
 *
 * @return void
 */
function bootstrap() :void {
	add_filter( 'wp_handle_sideload_prefilter', __NAMESPACE__ . '\\compress' );
	add_filter( 'wp_handle_upload_prefilter', __NAMESPACE__ . '\\compress' );
}

/**
 * Filters the data for a file before it is uploaded to WordPress.
 *
 * The dynamic portion of the hook name, `$action`, refers to the post action.
 *
 * Possible hook names include:
 *
 *  - `wp_handle_sideload_prefilter`
 *  - `wp_handle_upload_prefilter`
 *
 * @since 2.9.0 as 'wp_handle_upload_prefilter'.
 * @since 4.0.0 Converted to a dynamic hook with `$action`.
 *
 * @param array<string, int|string> $file {
 *     Reference to a single element from `$_FILES`.
 *
 *     @type string $name     The original name of the file on the client machine.
 *     @type string $type     The mime type of the file, if the browser provided this information.
 *     @type string $tmp_name The temporary filename of the file in which the uploaded file was stored on the server.
 *     @type int    $size     The size, in bytes, of the uploaded file.
 *     @type int    $error    The error code associated with this file upload.
 * }
 *
 * @return array<string, int|string>
 */
function compress( array $file ) : array {
	if ( isset( $file['error'] ) && 0 !== $file['error'] ) {
		return $file;
	}

	$mime = explode( '/', (string) $file['type'] );
	if (
		'image' !== $mime[0]
		||
		! in_array( $mime[1], [ 'jpeg', 'jpg', 'png', 'gif' ], true )
	) {
		return $file;
	}

	$file_put_contents = replace( (string) $file['tmp_name'] );

	if ( is_int( $file_put_contents ) && ! empty( $file_put_contents ) ) {
		$file['size'] = $file_put_contents;
	}

	return $file;
}

/**
 * Replace an image on the server with a compressed version of the same image.
 *
 * @param  string $path Absolute path to the image to replace compressed.
 *
 * @return int|false The function returns the number of bytes that were written to the file, or false on failure.
 */
function replace( string $path ) : int|false {

	// Compress image.
	$compressed_raw = optimize( $path );

	// If somethin went wrong
	// return, unchanged.
	if ( empty( $compressed_raw ) ) {
		return false;
	}

	// Replace image on the server.
	return file_put_contents(
		$path,
		$compressed_raw
	);

}

/**
 * Optimize image
 *
 * -sampling-factor 4:2:0 -strip -quality 85 -interlace JPEG -colorspace sRGB
 *
 * @see https://developers.google.com/speed/docs/insights/OptimizeImages
 *
 * @access public
 *
 * @param string $file_path Path of the file
 *
 * @return string Raw image result from the process
 */
function optimize( string $file_path ) : string {
	/**
	 * Compress image
	 */
	$imagick = new Imagick();

	$raw_image = file_get_contents( $file_path );

	if ( ! is_string( $raw_image ) ) {
		return '';
	}

	$imagick->readImageBlob( $raw_image );
	$imagick->stripImage();

	// Define image.
	$width      = $imagick->getImageWidth();
	$height     = $imagick->getImageHeight();

	// Compress image.
	$imagick->setImageCompressionQuality( 85 );

	$image_types = getimagesize( $file_path );

	// Get thumbnail image.
	$imagick->thumbnailImage( $width, $height );

	if ( ! isset( $image_types[2] ) ) {
		return '';
	}

	// Set image as based its own type.
	if ( $image_types[2] === IMAGETYPE_JPEG ) {
		$imagick->setImageFormat( 'jpeg' );

		$imagick->setSamplingFactors( [ '2x2', '1x1', '1x1' ] );

		$profiles = $imagick->getImageProfiles( 'icc', true );

		$imagick->stripImage();

		if ( ! empty( $profiles ) ) {
			$imagick->profileImage( 'icc', $profiles['icc'] );
		}

		$imagick->setInterlaceScheme( Imagick::INTERLACE_JPEG );
		$imagick->setColorspace( Imagick::COLORSPACE_SRGB );
	} elseif ( $image_types[2] === IMAGETYPE_PNG ) {
		$imagick->setImageFormat( 'png' );
	} elseif ( $image_types[2] === IMAGETYPE_GIF ) {
		$imagick->setImageFormat( 'gif' );
	}

	// Get image raw data.
	$raw_data = $imagick->getImageBlob();

	// Destroy image from memory.
	$imagick->destroy();

	return $raw_data;
}
