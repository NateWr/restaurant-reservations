<?php
/**
 * Class to handle all custom post type definitions for Restaurant Table Bookings
 */

if ( !defined( 'ABSPATH' ) )
	exit;

if ( !class_exists( 'rtbCustomPostTypes' ) ) {
class rtbCustomPostTypes {

	public function __construct() {

		// Call when plugin is initialized on every page load
		add_action( 'init', array( $this, 'load_cpts' ) );

		// Register new post status
		add_action( 'init', array( $this, 'register_post_status' ) );

	}

	/**
	 * Initialize custom post types
	 * @since 0.1
	 */
	public function load_cpts() {

		// Define the booking custom post type
		$args = array(
			'has_archive' => __( RTB_BOOKING_ARCHIVE_SLUG, RTB_TEXTDOMAIN ),
			'labels' => array(
				'name'               => __( 'Bookings',                   RTB_TEXTDOMAIN ),
				'singular_name'      => __( 'Booking',                    RTB_TEXTDOMAIN ),
				'menu_name'          => __( 'Bookings',                   RTB_TEXTDOMAIN ),
				'name_admin_bar'     => __( 'Bookings',                   RTB_TEXTDOMAIN ),
				'add_new'            => __( 'Add New',                 	  RTB_TEXTDOMAIN ),
				'add_new_item'       => __( 'Add New Booking',            RTB_TEXTDOMAIN ),
				'edit_item'          => __( 'Edit Booking',               RTB_TEXTDOMAIN ),
				'new_item'           => __( 'New Booking',                RTB_TEXTDOMAIN ),
				'view_item'          => __( 'View Booking',               RTB_TEXTDOMAIN ),
				'search_items'       => __( 'Search Bookings',            RTB_TEXTDOMAIN ),
				'not_found'          => __( 'No bookings found',          RTB_TEXTDOMAIN ),
				'not_found_in_trash' => __( 'No bookings found in trash', RTB_TEXTDOMAIN ),
				'all_items'          => __( 'All Bookings',               RTB_TEXTDOMAIN ),
			),
			'menu_icon' => 'dashicons-calendar',
			'public' => false,
			'show_in_admin_bar' => false,
			'show_ui' => true,
			'supports' => array(
				'title',
				'revisions'
			)
		);

		// Create filter so addons can modify the arguments
		$args = apply_filters( 'rtb_booking_args', $args );

		// Add an action so addons can hook in before the post type is registered
		do_action( 'rtb_booking_pre_register' );

		// Register the post type
		register_post_type( RTB_BOOKING_POST_TYPE, $args );

		// Add an action so addons can hook in after the post type is registered
		do_action( 'rtb_booking_post_register' );

	}

	/**
	 * Register custom post status
	 * @since 0.0.1
	 */
	public function register_post_status() {

		register_post_status(
			'confirmed',
			array(
				'label'                     => _x( 'Confirmed', 'Confirmed bookings', RTB_TEXTDOMAIN ),
				'public'                    => false,
				'exclude_from_search'       => true,
				'show_in_admin_all_list'    => true,
				'show_in_admin_status_list' => true,
				'label_count'               => _n_noop( 'Confirmed <span class="count">(%s)</span>', 'Confirmed <span class="count">(%s)</span>', RTB_TEXTDOMAIN )
			)
		);

		register_post_status(
			'closed',
			array(
				'label'                     => _x( 'Closed', 'Closed bookings', RTB_TEXTDOMAIN ),
				'public'                    => false,
				'exclude_from_search'       => true,
				'show_in_admin_all_list'    => true,
				'show_in_admin_status_list' => true,
				'label_count'               => _n_noop( 'Closed <span class="count">(%s)</span>', 'Closed <span class="count">(%s)</span>', RTB_TEXTDOMAIN )
			)
		);

	}

}
} // endif;
