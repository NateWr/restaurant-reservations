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
			add_action( 'init',                            array( $this, 'register_taxonomy' ), 1000 ); // after custom post types declared (hopefully!)
			add_action( 'save_post_' . $this->post_type,   array( $this, 'save_location' ), 10, 3 );
			add_action( 'before_delete_post',              array( $this, 'delete_location' ) );
			add_action( 'rtb_booking_form_fields',         array( $this, 'add_location_field' ), 10, 3 );
			add_action( 'rtb_validate_booking_submission', array( $this, 'validate_location' ) );
			add_action( 'rtb_insert_booking',              array( $this, 'save_booking_location' ) );
			add_action( 'rtb_update_booking',              array( $this, 'save_booking_location' ) );
			add_action( 'rtb_booking_load_post_data',      array( $this, 'load_booking_location' ), 10, 2 );
			add_filter( 'rtb_bookings_table_columns',      array( $this, 'add_location_column' ) );
			add_filter( 'rtb_bookings_table_column',       array( $this, 'print_location_column' ), 10, 3 );
			add_filter( 'rtb_query_args',                  array( $this, 'modify_query' ), 10, 2 );
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

		/**
		 * Add the location selection field to the booking form
		 *
		 * @since 1.6
		 */
		public function add_location_field( $fields, $request = null, $args = array() ) {

			if ( $request === null ) {
				global $rtb_controller;
				$request = $rtb_controller->request;
			}

			// Select a fieldset in which to place the field
			$placement = false;
			if ( isset( $fields['reservation'] ) && isset( $fields['reservation']['fields'] ) ) {
				$placement = &$fields['reservation']['fields'];
			} else {
				$key = key( reset( $fields  ) );
				if ( isset( $fields[$key]['fields'] ) ) {
					$placement = &$fields[$key]['fields'];
				}
			}

			// If we couldn't find any working fieldset, then something odd is
			// going on. Just pretend we were never here.
			if ( $placement === false ) {
				return $fields;
			}

			// If the location is specified, don't add a field.
			// A hidden field is added automatically in rtb_print_booking_form()
			if ( !empty( $args['location'] ) && term_exists( $args['location'], $this->location_taxonomy ) ) {
				return $fields;
			}

			$placement = array_merge(
				array(
					'location' => array(
						'title'			=> __( 'Location', 'restaurant-reservations' ),
						'request_input'	=> empty( $request->location ) ? '' : $request->location,
						'callback'		=> 'rtb_print_form_select_field',
						'callback_args'	=> array(
							'options'	=> $this->get_location_options(),
						),
						'required'		=> true,
					)
				),
				$placement
			);

			return $fields;
		}

		/**
		 * Retrieve a key/value array of location terms and names
		 *
		 * @since 1.6
		 */
		public function get_location_options() {

			$terms = get_terms(
				array(
					'taxonomy'   => $this->location_taxonomy,
					'hide_empty' => false,
				)
			);

			$options = array();
			foreach( $terms as $term ) {
				$options[$term->term_id] = $term->name;
			}

			return $options;
		}

		/**
		 * Validate location in post data
		 *
		 * @since 1.6
		 */
		public function validate_location( $booking ) {

			$booking->location = empty( $_POST['rtb-location'] ) ? '' : absint( $_POST['rtb-location'] );
			if ( empty( $booking->location ) ) {
				$booking->validation_errors[] = array(
					'field'			=> 'location',
					'post_variable'	=> $booking->location,
					'message'	=> __( 'Please select a location for your booking.', 'restaurant-reservations' ),
				);

			} elseif ( !term_exists( $booking->location, $this->location_taxonomy ) ) {
				$booking->validation_errors[] = array(
					'field'			=> 'location',
					'post_variable'	=> $booking->location,
					'message'	=> __( 'The location you selected is not valid. Please select another location.', 'restaurant-reservations' ),
				);
			}
		}

		/**
		 * Save the booking location when the booking is created or updated.
		 *
		 * @since 1.6
		 */
		public function save_booking_location( $booking ) {

			if ( !empty( $booking->location ) ) {
				wp_set_object_terms( $booking->ID, $booking->location, $this->location_taxonomy );
			}
		}

		/**
		 * Load the booking location when teh booking is loaded
		 *
		 * @since 1.6
		 */
		public function load_booking_location( $booking, $post ) {

			$terms = wp_get_object_terms( $booking->ID, $this->location_taxonomy, array( 'fields' => 'ids' ) );

			if ( is_a( $terms, 'WP_Error' ) ) {
				return;
			}

			$booking->location = current( $terms );
		}

		/**
		 * Add location column to the list table
		 *
		 * @since 1.6
		 */
		public function add_location_column( $columns ) {

			$first = array_splice( $columns, 0, 2 );
			$first['location'] = __( 'Location', 'restaurant-reservations' );

			return array_merge( $first, $columns );
		}

		/**
		 * Print the value in the location column for the list table
		 *
		 * @since 1.6
		 */
		public function print_location_column( $value, $booking, $column_name ) {

			if ( $column_name !== 'location' ) {
				return $value;
			}

			$terms = wp_get_object_terms( $booking->ID, $this->location_taxonomy );

			if ( empty( $terms ) || is_a( $terms, 'WP_Error' ) ) {
				return '';
			}

			$location = current( $terms );

			return $location->name;
		}

		/**
		 * Modify queries to add location taxonomy parameters
		 *
		 * @param array $args Array of arguments passed to rtbQuery
		 * @since 1.6
		 */
		public function modify_query( $args, $context = '' ) {

			global $rtb_controller;

			if ( !empty( $args['location'] ) && !empty( $rtb_controller->locations->post_type ) ) {

				if ( !is_array( $args['location'] ) ) {
					$args['location'] = array( $args['location'] );
				}

				$args['tax_query'] = array(
					array(
						'taxonomy' => $rtb_controller->locations->location_taxonomy,
						'field'    => 'term_id',
						'terms'    => $args['location'],

					)
				);
			}

			return $args;
		}
	}
}
