<?php
/**
 * Misc Functions
 */

// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
	die;
}

/**
 * Retrieve a post/page by its title.
 * @since  1.0
 * @param string $title The title of the post/page to retrieve.
 * @return WP_Post|null The retrieved post/page object or null if not found.
 */
function hootimport_get_post_type_by_title( $title, $type='post' ) {
	if ( ! $title || !is_string( $title ) ) {
		return null;
	}
	$query = new \WP_Query(
		array(
			'post_type'              => $type,
			'title'                  => $title,
			'post_status'            => 'all',
			'posts_per_page'         => 1,
			'no_found_rows'          => true,
			'ignore_sticky_posts'    => true,
			'update_post_term_cache' => false,
			'update_post_meta_cache' => false,
		)
	);
	if ( ! $query->have_posts() ) {
		return null;
	}
	return current( $query->posts );
}

/**
 * Recursive wp_parse_args
 * Non Strict Mode: Use $args value if present ('', 0, '0', false allowed)
 * @since  1.0
 * @return mixed
 */
function hootimport_recursive_parse_args( $args, $defaults ) {
	$return = (array) $defaults;
	foreach ( $args as $key => $value ) {
		if ( is_array( $value ) && isset( $return[ $key ] ) ) {
			$return[ $key ] = hootimport_recursive_parse_args( $value, $return[ $key ] );
		} else {
			$return[ $key ] = $value;
		}
	}
	return $return;
}

/**
 * Cleanup
 * @since  1.0
 * @param  string $demopack_dir
 * @param  bool   $forcecleanup
 * @return void
 */
function hootimport_cleanup( $demopack_dir = '', $forcecleanup = false ) {
	if (
		empty( $demopack_dir ) || !is_string( $demopack_dir )
		|| !function_exists( 'current_user_can' )
		|| !is_dir( $demopack_dir )
	)
		return;

	// Cleanup demo pack directory
	$is_fresh = get_transient( 'hootimport_freshpack' );
	if ( empty( $is_fresh ) || $forcecleanup ) {
		if ( current_user_can( 'manage_options' ) ) {

			// Initialize the WP Filesystem API
			if ( ! function_exists( 'wp_filesystem' ) ) {
				require_once( ABSPATH . 'wp-admin/includes/file.php' );
			}
			global $wp_filesystem;
			WP_Filesystem();

			// Check if the directory exists and remove the directory and all its contents
			if ( $wp_filesystem->is_dir( $demopack_dir ) ) {
				$wp_filesystem->rmdir( $demopack_dir, true );
			}

		}
	}

}
