<?php
/**
 * Plugin Name:     figuren.theater | Media
 * Plugin URI:      https://github.com/figuren-theater/ft-media
 * Description:     Optimizations related to media & attachments, packed for a WordPress multisite network like figuren.theater
 * Author:          figuren.theater
 * Author URI:      https://figuren.theater
 * Text Domain:     figurentheater
 * Domain Path:     /languages
 * Version:         1.3.1
 *
 * @package         figuren-theater/ft-media
 */

namespace Figuren_Theater\Media;

const DIRECTORY = __DIR__;

add_action( 'altis.modules.init', __NAMESPACE__ . '\\register' );
