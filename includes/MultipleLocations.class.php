<?php
/**
 * Methods for handling multiple locations
 *
 * @package   RestaurantReservations
 * @copyright Copyright (c) 2016, Theme of the Crop
 * @license   GPL-2.0+
 * @since     1.6
 */
defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'rtbMultipleLocations', false ) ) {
	/**
	 * Class to handle custom post type and post meta fields
	 *
	 * @since 1.6
	 */
	class rtbMultipleLocations {

		/**
		 * Post type slug where locations can be found
		 *
		 * @since 1.6
		 */
		public $post_type = false;

		/**
		 * Taxonomy to use when assigning bookings to locations
		 *
		 * @since 1.6
		 */
		public $location_taxonomy = 'rtb_location';

		/**
		 * Set the loading hook
		 *
		 * @since 1.6
		 */
		public function __construct() {
			add_action( 'plugins_loaded', array( $this, 'load' ), 100 );
		}

		/**
		 * Load locations support
		 *
		 * @since 1.6
		 */
		public function load() {

			/**
			 * Allow third-party plugins to enable multiple locations
			 *
			 * Expects a post type slug pointing to the locations or false if
			 * multiple locations are not enabled.
			 *
			 * @since 1.6
			 */
			$this->post_type = apply_filters( 'rtb_set_locations_post_type', false );

			if ( !$this->post_type ) {
				return;
			}

			$this->hooks();
		}

		/**
		 * Set up hooks
		 *
		 * @since 1.6
		 */
		public function hooks() {
			add_action( 'init', array( $this, 'register_taxonomy' ), 1000 ); // after custom post types declared (hopefully!)
			add_action( 'save_post_' . $this->post_type, array( $this, 'save_location' ), 10, 3 );
			add_action( 'before_delete_post', array( $this, 'delete_location' ) );
		}

		/**
		 * Register the location taxonomy
		 *
		 * @since 1.6
		 */
		public function register_taxonomy() {

			$args = array(
				'label'        => _x( 'Location', 'Name for grouping bookings', 'restaurant-reservations' ),
				'hierarchical' => false,
		        'public'       => true,
				'rewrite'      => false,
			);

			/**
			 * Allow third-party plugins to modify the location taxonomy
			 * arguments.
			 *
			 * @since 1.6
			 */
			$args = apply_filters( 'rtb_locations_args', $args );

			register_taxonomy( $this->location_taxonomy, RTB_BOOKING_POST_TYPE, $args );
		}

		/**
		 * Generate taxonomy terms linked to locations and keep them sync'd
		 * with any changes
		 *
		 * @since 1.6
		 */
		public function save_location( $post_id, $post, $update ) {


			if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
				return $post_id;
			}

			if ( !current_user_can( 'edit_post', $post_id ) ) {
				return $post_id;
			}

			$term_id = get_post_meta( $post_id, $this->location_taxonomy, true );

			// Create a new term for this location
			if ( !$term_id ) {
				$term = wp_insert_term(
					sanitize_text_field( $post->post_title ),
					$this->location_taxonomy
				);
				if ( !is_a( $term, 'WP_Error' ) ) {
					update_post_meta( $post_id, $this->location_taxonomy, $term['term_id'] );
				}

			// Update the term for this location
			} else {
				wp_update_term(
					$term_id,
					$this->location_taxonomy,
					array(
						'name' => sanitize_text_field( $post->post_title ),
						'slug' => sanitize_text_field( $post->post_name ),
					)
				);
			}

			return $post_id;
		}

		/**
		 * Delete taxonomy terms linked to locations when a location is deleted
		 *
		 * Only does this when no bookings are associated with that term.
		 * Otherwise it may be important to keep the bookings grouped for
		 * historical data.
		 *
		 * @since 1.6
		 */
		public function delete_location( $post_id ) {

			if ( !current_user_can( 'delete_posts' ) ) {
				return;
			}

			$term_id = get_post_meta( $post_id, $this->location_taxonomy, true );

			$term = get_term( $term_id, $this->location_taxonomy );

			if ( !$term || is_a( $term, 'WP_Error' ) ) {
				return;
			}

			$query = new rtbQuery( array( 'location' => $term_id ), 'delete-location-term-check' );
			$query->prepare_args();
			$query->get_bookings();

			// Don't delete taxonomy terms if there are bookings assigned to
			// this location. It may be important to keep the bookings grouped
			// for historical data
			if ( count( $query->bookings ) ) {
				return;
			}

			wp_delete_term( $term_id, $this->location_taxonomy );
		}
	}
}
