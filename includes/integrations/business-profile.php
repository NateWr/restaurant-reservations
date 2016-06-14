<?php if ( !defined( 'ABSPATH' ) ) exit;
/**
 * Business Profile: Enable multi-location support in Restaurant Reservations
 * when locations are activated in Business Profile
 *
 * @param string $post_type The post type to use for locations
 * @since 1.6
 */
function rtb_maybe_enable_bp_locations( $post_type ) {

	// Don't override a post type that's already been set
	if ( $post_type ) {
		return $post_type;
	}

	global $bpfwp_controller;
	if ( isset( $bpfwp_controller ) && isset( $bpfwp_controller->cpts ) ) {
		return $bpfwp_controller->cpts->location_cpt_slug;
	}

	return $post_type;
}
add_filter( 'rtb_set_locations_post_type', 'rtb_maybe_enable_bp_locations' );
