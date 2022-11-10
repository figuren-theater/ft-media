<?php
/**
 * Figuren_Theater Media Image_Optimzation.
 *
 * @package figuren-theater/media/image_optimization
 */

namespace Figuren_Theater\Media\Image_Optimzation;

use IMAGETYPE_JPEG;
use IMAGETYPE_PNG;
use IMAGETYPE_GIF;

use Imagick;

use function add_filter;


/**
 * Set up hooks.
 *
 * @return void
 */
function bootstrap() {
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
 * @param array $file {
 *     Reference to a single element from `$_FILES`.
 *
 *     @type string $name     The original name of the file on the client machine.
 *     @type string $type     The mime type of the file, if the browser provided this information.
 *     @type string $tmp_name The temporary filename of the file in which the uploaded file was stored on the server.
 *     @type int    $size     The size, in bytes, of the uploaded file.
 *     @type int    $error    The error code associated with this file upload.
 * }
 */
function compress( array $file ) : array {
	if (0!==$file['error'])
		return $file;

	$mime = explode('/', $file['type']);
	if ( 
		'image'!==$mime[0] 
		||
		! in_array($mime[1], ['jpeg','jpg','png','gif',])
	)
		return $file;

	$file_put_contents = replace( $file['tmp_name'] );

	if ( is_int( $file_put_contents ) && ! empty( $file_put_contents ) )
		$file['size'] = $file_put_contents;

	return $file;
}

function replace( string $path ) : int {
	
	// compress image
	$compressed_raw = optimize( $path );
	
	// if somethin went wrong
	// return, unchanged
	if ( ! is_string( $compressed_raw ) || empty( $compressed_raw ) )
		return 0;

	// replace image on the server
	return file_put_contents(
		$path,
		$compressed_raw
	);
	
}

/**
 * Optimize image
 * 
 * https://developers.google.com/speed/docs/insights/OptimizeImages
 * 
 * -sampling-factor 4:2:0 -strip -quality 85 -interlace JPEG -colorspace sRGB
 *
 * @access public
 * @param string $filePath Path of the file
 * @return string Raw image result from the process
 */
function optimize( string $filePath ) : string {
	/**
	 * Compress image
	 */
	$imagick        = new Imagick();

	$rawImage = file_get_contents($filePath);

	$imagick->readImageBlob($rawImage);
	$imagick->stripImage();

	// Define image
	$width      = $imagick->getImageWidth();
	$height     = $imagick->getImageHeight();

	// Compress image
	$imagick->setImageCompressionQuality(85);

	$image_types = getimagesize($filePath);

	// Get thumbnail image
	$imagick->thumbnailImage($width, $height);

	// Set image as based its own type
	if ($image_types[2] === IMAGETYPE_JPEG)
	{
		$imagick->setImageFormat('jpeg');

		$imagick->setSamplingFactors(array('2x2', '1x1', '1x1'));

		$profiles = $imagick->getImageProfiles("icc", true);

		$imagick->stripImage();

		if(!empty($profiles)) {
			$imagick->profileImage('icc', $profiles['icc']);
		}

		$imagick->setInterlaceScheme(Imagick::INTERLACE_JPEG);
		$imagick->setColorspace(Imagick::COLORSPACE_SRGB);
	}
	else if ($image_types[2] === IMAGETYPE_PNG) 
	{
		$imagick->setImageFormat('png');
	}
	else if ($image_types[2] === IMAGETYPE_GIF) 
	{
		$imagick->setImageFormat('gif');
	}

	// Get image raw data
	$rawData = $imagick->getImageBlob();

	// Destroy image from memory
	$imagick->destroy();

	return $rawData;
}
