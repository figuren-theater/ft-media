<?php
/**
 * Plugin Name:     figuren.theater | Media
 * Plugin URI:      https://github.com/figuren-theater/ft-media
 * Description:     Media & attachment related optimization packed for WordPress Multisite like figuren.theater
 * Author:          figuren.theater
 * Author URI:      https://figuren.theater
 * Text Domain:     figurentheater
 * Domain Path:     /languages
 * Version:         1.0.5
 *
 * @package         figuren-theater/media
 */

namespace Figuren_Theater\Media;

const DIRECTORY = __DIR__;

add_action( 'altis.modules.init', __NAMESPACE__ . '\\register' );
