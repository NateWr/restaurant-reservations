<?php
if ( !defined( 'ABSPATH' ) ) exit;

if ( !class_exists( 'rtbQuery' ) ) {
/**
 * Class to handle common queries used to pull bookings from
 * the database.
 *
 * Bookings can be retrieved with specific date ranges, common
 * date params (today/upcoming), etc. This class is intended for
 * the base plugin as well as extensions or custom projects which
 * need a stable mechanism for reliably retrieving bookings data.
 *
 * Queries return an array of rtbBooking objects.
 *
 * @since 1.4.1
 */
class rtbQuery {

	/**
	 * Bookings
	 *
	 * Array of bookings retrieved after get_bookings() is called
	 *
	 * @since 1.4.1
	 */
	public $bookings = array();

	/**
	 * Query args
	 *
	 * Passed to WP_Query
	 * http://codex.wordpress.org/Class_Reference/WP_Query
	 *
	 * @since 1.4.1
	 */
	public $args = array();

	/**
	 * Query context
	 *
	 * Defines the context in which the query is run.
	 * Useful for hooking into the right query without
	 * tampering with others.
	 *
	 * @since 1.4.1
	 */
	public $context;

	/**
	 * Instantiate the query with an array of arguments
	 *
	 * This supports all WP_Query args as well as several
	 * short-hand arguments for common needs. Short-hands
	 * include:
	 *
	 * date_range string today|upcoming|dates
	 * start_date string don't get bookings before this
	 * end_date string don't get bookings after this
	 *
	 * @see rtbQuery::prepare_args()
	 * @param args array Options to tailor the query
	 * @param context string Context for the query, used
	 *		in filters
	 * @since 1.4.1
	 */
	public function __construct( $args = array(), $context = '' ) {

		global $rtb_controller;

		$defaults = array(
			'post_type'			=> RTB_BOOKING_POST_TYPE,
			'posts_per_page'	=> 10,
			'date_range'		=> 'upcoming',
			'post_status'		=> array_keys( $rtb_controller->cpts->booking_statuses ),
			'order'				=> 'ASC',
			'paged'				=> 1,
		);

		$this->args = wp_parse_args( $args, $defaults );

		$this->context = $context;

	}

	/**
	 * Parse the args array and convert custom arguments
	 * for use by WP_Query
	 *
	 * @since 1.4.1
	 */
	public function prepare_args() {

		$args = $this->args;

		if ( is_string( $args['date_range'] ) ) {

			if ( !empty( $args['start_date'] ) || !empty( $args['end_date'] ) ) {
				$date_query = array( 'inclusive' => true );

				if ( !empty( $args['start_date'] ) ) {
					$date_query['after'] = sanitize_text_field( $args['start_date'] );
				}

				if ( !empty( $args['end_date'] ) ) {
					$date_query['before'] = sanitize_text_field( $args['end_date'] ) . ' 23:59'; // end of day
				}

				if ( count( $date_query ) ) {
					$args['date_query'] = $date_query;
				}
			} elseif ( $args['date_range'] === 'today' ) {
				$args['year'] = date( 'Y', current_time( 'timestamp' ) );
				$args['monthnum'] = date( 'm', current_time( 'timestamp' ) );
				$args['day'] = date( 'd', current_time( 'timestamp' ) );

			} elseif ( $args['date_range'] === 'upcoming' ) {
				$args['date_query'] = array(
					array(
						'after' => '-1 hour', // show bookings that have just passed
					)
				);
			}
		}

		if ( !empty( $args['post_status'] ) ) {
			if ( is_string( $args['post_status'] ) ) {

				// Parse a comma-separated string of statuses
				if ( strpos( $args['post_status'], ',' ) !== false ) {
					$statuses = explode( ',', $args['post_status'] );
					$args['post_status'] = array();
					foreach( $statuses as $status ) {
						$args['post_status'][] = sanitize_key( $status );
					}
				} else {
					$args['post_status'] = sanitize_key( $_REQUEST['status'] );
				}
			}
		}

		$this->args = $args;

		return $this->args;
	}

	/**
	 * Parse $_REQUEST args and store in $this->args
	 *
	 * @since 1.4.1
	 */
	public function parse_request_args() {

		$args = array();

		if ( !empty( $_REQUEST['paged'] ) ) {
			$args['paged'] = (int) $_REQUEST['paged'];
		}

		if ( !empty( $_REQUEST['posts_per_page'] ) ) {
			$args['posts_per_page'] = (int) $_REQUEST['posts_per_page'];
		}

		if ( !empty( $_REQUEST['status'] ) ) {
			if ( is_string( $_REQUEST['status'] ) ) {
				$args['post_status'] = sanitize_text_field( $_REQUEST['status'] );
			} elseif ( is_array( $_REQUEST['status'] ) ) {
				$args['post_status'] = array();
				foreach( $_REQUEST['status'] as $status ) {
					$args['post_status'][] = sanitize_key( $status );
				}
			}
		}

		if ( !empty( $_REQUEST['orderby'] ) ) {
			$args['orderby'] = sanitize_key( $_REQUEST['orderby'] );
		}

		if ( !empty( $_REQUEST['order'] ) && $_REQUEST['order'] === 'desc' ) {
			$args['order'] = $_REQUEST['orderby'];
		}

		if ( !empty( $_REQUEST['date_range'] ) ) {
			$args['date_range'] = sanitize_key( $_REQUEST['date_range'] );
		}

		if ( !empty( $_REQUEST['start_date'] ) ) {
			$args['start_date'] = sanitize_text_field( $_REQUEST['start_date'] );
		}

		if ( !empty( $_REQUEST['end_date'] ) ) {
			$args['end_date'] = sanitize_text_field( $_REQUEST['end_date'] );
		}

		if ( !empty( $_REQUEST['location'] ) ) {
			$args['location'] = absint( $_REQUEST['location'] );
		}

		$this->args = array_merge( $this->args, $args );
	}

	/**
	 * Retrieve query results
	 *
	 * @since 1.4.1
	 */
	public function get_bookings() {

		$bookings = array();

		$args = apply_filters( 'rtb_query_args', $this->args, $this->context );

		$query = new WP_Query( $args );

		if ( $query->have_posts() ) {
			require_once( RTB_PLUGIN_DIR . '/includes/Booking.class.php' );

			while( $query->have_posts() ) {
				$query->the_post();

				$booking = new rtbBooking();
				if ( $booking->load_post( $query->post ) ) {
					$bookings[] = $booking;
				}
			}
		}

		$this->bookings = $bookings;

		wp_reset_query();

		return $this->bookings;
	}

}
} // endif
