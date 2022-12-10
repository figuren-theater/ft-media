<?php
/**
 * Figuren_Theater Media Auto_Featured_Image.
 *
 * @package figuren-theater/media/auto_featured_image
 */

namespace Figuren_Theater\Media\Auto_Featured_Image;

use WP_Post;

use function add_action;
use function add_theme_support;
use function get_children;
use function has_post_thumbnail;
use function is_admin;
use function is_network_admin;
use function is_user_admin;
use function set_post_thumbnail;


/**
 * Set up hooks.
 *
 * @return void
 */
function bootstrap() {

	add_action( 'init', __NAMESPACE__ . '\\load' );
}

function load() {

	// Do only load in "normal" admin view
	// Not for:
	// - public views
	// - network-admin views
	// - user-admin views
	if ( ! is_admin() || is_network_admin() || is_user_admin() )
		return;
	
	// This should be in your theme. 
	// But we add this here because this way we can have featured images before switching to a theme that supports them.
	add_theme_support( 'post-thumbnails' ); 

	// Set featured image before post is displayed on the site front-end (for old posts published before enabling this plugin).
	// 
	// This line is used to generate featured images for all old
	// posts. Remove this once the default images get generated
	// for all of the old posts
	// 
	// DISABLED
	// add_action( 'the_post', __NAMESPACE__ . '\\auto_featured_image' );


	// For new upcoming posts, leave them permanently
	// add_action('save_post', __NAMESPACE__ . '\\auto_featured_image');
	
	/**
	 * Hooks added to set the thumbnail when publishing too.
	 * 
	 * An {old_status}_to_{new_status} action will execute 
	 * when a post transitions from {old_status} to {new_status}. 
	 * The action is accompanied by the $post object. 
	 * 
	 * In the add_action() function call, the action priority 
	 * may be set between 0 and 20 (default is 10) ...
	 * 
	 * !!!
	 * ... and it is necessary to specify the number of arguments
	 * do_action() should pass to the callback function. 
	 * 
	 * @see https://codex.wordpress.org/Post_Status_Transitions#.7Bold_status.7D_to_.7Bnew_status.7D_Hook
	 * !!!
	 */
	add_action( 'publish_to_publish', __NAMESPACE__ . '\\auto_featured_image', 1 );
	add_action( 'new_to_publish', __NAMESPACE__ . '\\auto_featured_image', 1 );
	add_action( 'draft_to_publish', __NAMESPACE__ . '\\auto_featured_image', 1 );
	add_action( 'pending_to_publish', __NAMESPACE__ . '\\auto_featured_image', 1 );
	add_action( 'future_to_publish', __NAMESPACE__ . '\\auto_featured_image', 1 );
}


/**
 * Automatically add first image in content as featured image if none is set.
 *
 * @param object|null $post Post Object or null if is called during trashing.
 * 
 * @link https://wordpress.org/plugins/easy-add-thumbnail/
 */
function auto_featured_image( WP_Post|null $post ) : void {

	// Do nothing if the post has already a featured image set.
	if ( has_post_thumbnail( $post ) ) {
		return;
	}

	// Get first attached image.
	$args = [
		'numberposts'    => 1,
		'order'          => 'ASC', // DESC for the last image
		'post_mime_type' => 'image',
		'post_parent'    => $post->ID,
		'post_status'    => null,
		'post_type'      => 'attachment',
	];
	$attached_image = get_children( $args );

	if ( $attached_image ) {

		$attachment_values = array_values( $attached_image );
		set_post_thumbnail( $post->ID, $attachment_values[0]->ID );
		return;
	} 

	// Use regex to find image blocks in the post content.
	$output = preg_match_all( '/<img.+src=[\'"]([^\'"]+)[\'"].*>/i', $post->post_content, $matches );
	$output = preg_match_all( '/wp:image {"id":[0-9]+/i', $post->post_content, $matches );

	// If there are any image blocks ... 
	if ( ! $output ) {
		return;
	}

	// ... set thumbnail to first image.
	$images_found = $matches;
	// Get the ID of first image.
	$first_img_id = preg_match_all( '/[0-9]+/i', $matches[0][0], $matches );
	$first_img_id = (int) $matches[0][0];

	set_post_thumbnail( $post->ID, $first_img_id );
}
