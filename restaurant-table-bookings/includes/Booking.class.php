<?php
if ( !defined( 'ABSPATH' ) ) exit;

if ( !class_exists( 'rtbBooking' ) ) {
/**
 * Class to handle a booking for Restaurant Table Bookings
 *
 * @since 0.0.1
 */
class rtbBooking {

	/**
	 * Retrieve all data for a booking
	 *
	 * $new is set to true when a post has just been created. The post meta
	 *	will not have made it into the database yet, so we need to pull it
	 *	from the $_POST variables.
	 *
	 * @var WP_Post object $booking_post
	 * @var boolean $new
	 * @since 0.0.1
	 */
	public function __construct() {

	}

	/**
	 * Load the booking information from a WP_Post object or an ID
	 *
	 * @uses load_wp_post()
	 * @since 0.0.1
	 */
	public function load_post( $post ) {

		if ( is_int( $post ) || is_string( $post ) ) {
			$post = get_post( $post );
		}

		if ( get_class( $post ) == 'WP_Post' && $post->post_type == RTB_BOOKING_POST_TYPE ) {
			$this->load_wp_post( $post );
			return true;
		} else {
			return false;
		}

	}

	/**
	 * Load data from WP post object and retrieve metadata
	 *
	 * @uses load_post_metadata()
	 * @since 0.0.1
	 */
	public function load_wp_post( $post ) {

		// Store post for access to other data if needed by extensions
		$this->post = $post;

		$this->ID = $post->ID;
		$this->name = $post->post_title;
		$this->date = $this->format_date( $post->post_title );
		$this->message = apply_filters( 'the_content', $post->post_content );
		$this->post_status = $post->post_status;

		$this->load_post_metadata();

		do_action( 'rtb_booking_load_post_data', $this, $post );
	}

	/**
	 * Store metadata for post
	 * @since 0.0.1
	 */
	public function load_post_metadata() {

		$meta_defaults = array(
			'party' => '',
			'email' => '',
			'phone' => '',
			'date_submission' => '',
		);

		$meta_defaults = apply_filters( 'rtb_booking_metadata_defaults', $meta_defaults );

		if ( is_array( $meta = get_post_meta( $this->ID, 'rtb', true ) ) ) {
			$meta = array_merge( $meta_defaults, get_post_meta( $this->ID, 'rtb', true ) );
		} else {
			$meta = $meta_defaults;
		}

		$this->party = $meta['party'];
		$this->email = $meta['email'];
		$this->phone = $meta['phone'];
		$this->date_submission = $meta['date_submission'];
	}

	/**
	 * Format date
	 * @since 0.0.1
	 */
	public function format_date( $date ) {
		$date = mysql2date( get_option( 'date_format' ), $date);
		return apply_filters( 'get_the_date', $date );
	}

	/**
	 * Insert a new booking submission into the database
	 *
	 * Validates the data, adds it to the database and executes notifications
	 * @since 0.0.1
	 */
	public function insert_booking() {

		$this->validate_submission();
		if ( $this->is_valid_submission() === false ) {
			return false;
		}

		if ( $this->insert_post_data() === false ) {
			return false;
		}

		do_action( 'rtb_insert_booking', $this );
	}

	/**
	 * Validate submission data. Expects to find data in $_POST.
	 * @since 0.0.1
	 */
	public function validate_submission() {

		$this->validation_errors = array();

		// Date
		$date = empty( $_POST['date'] ) ? false : $_POST['date'];
		if ( $date === false ) {
			$this->validation_errors[] = array(
				'field'		=> 'date',
				'error_msg'	=> $e->getMessage(),
				'message'	=> __( 'Please enter the date you would like to book.', RTB_TEXTDOMAIN ),
			);
		}

		try {
			$date = empty( $_POST['date'] ) ? '' : new DateTime( $_POST['date'] );
		} catch ( Exception $e ) {
			$this->validation_errors[] = array(
				'field'		=> 'date',
				'error_msg'	=> $e->getMessage(),
				'message'	=> __( 'The date you entered is not valid. Please select from one of the dates in the calendar.', RTB_TEXTDOMAIN ),
			);
		}

		// Time
		$time = empty( $_POST['time'] ) ? false : $_POST['time'];
		if ( $time === false ) {
			$this->validation_errors[] = array(
				'field'		=> 'time',
				'error_msg'	=> $e->getMessage(),
				'message'	=> __( 'Please enter the time you would like to book.', RTB_TEXTDOMAIN ),
			);
		}

		try {
			$time = empty( $_POST['time'] ) ? '' : new DateTime( $_POST['time'] );
		} catch ( Exception $e ) {
			$this->validation_errors[] = array(
				'field'		=> 'time',
				'error_msg'	=> $e->getMessage(),
				'message'	=> __( 'The time you entered is not valid. Please select from one of the times presented.', RTB_TEXTDOMAIN ),
			);
		}

		if ( is_object( $time ) && is_object( $date ) ) {

			// @todo check against valid open dates/times
			// error message: "Sorry, there are no bookings available at that time."
			$this->date = $date->format( 'Y-m-d' ) . ' ' . $time->format( 'H:i:s' );
		}

		// Name
		$this->name = empty( $_POST['name'] ) ? '' : wp_strip_all_tags( sanitize_text_field( $_POST['name'] ), true ); // @todo limit length? how long will WP leave a post title?
		if ( empty( $this->name ) ) {
			$this->validation_errors[] = array(
				'field'			=> 'name',
				'post_variable'	=> $_POST['name'],
				'message'	=> __( 'Please enter a name for this booking.', RTB_TEXTDOMAIN ),
			);
		}

		// Party
		$this->party = empty( $_POST['party'] ) ? '' : sanitize_text_field( $_POST['party'] );
		if ( empty( $this->party ) ) {
			$this->validation_errors[] = array(
				'field'			=> 'party',
				'post_variable'	=> $_POST['party'],
				'message'	=> __( 'Please let us know how many people will be in your party.', RTB_TEXTDOMAIN ),
			);
		}

		// Email/Phone
		$this->email = empty( $_POST['email'] ) ? '' : sanitize_text_field( $_POST['email'] ); // @todo email validation? send notification back to form on bad email
		$this->phone = empty( $_POST['phone'] ) ? '' : sanitize_text_field( $_POST['phone'] );
		if ( empty( $this->email ) && empty( $this->phone ) ) {
			$this->validation_errors[] = array(
				'field'			=> 'email',
				'post_variable'	=> $_POST['email'] . '/' . $_POST['phone'],
				'message'	=> __( 'Please enter an email address or phone number so we can confirm your booking.', RTB_TEXTDOMAIN ),
			);
		}

		// Message
		$this->message = empty( $_POST['message'] ) ? '' : sanitize_text_field( $_POST['message'] );

		do_action( 'rtb_validate_booking_submission', $this );

	}

	/**
	 * Check if submission is valid
	 * @since 0.0.1
	 */
	public function is_valid_submission() {
		if ( !count( $this->validation_errors ) ) {
			return true;
		}
		return false;
	}

	/**
	 * Insert post data for a new booking.
	 * @since 0.0.1
	 */
	public function insert_post_data() {

		$args = array(
			'post_type'		=> RTB_BOOKING_POST_TYPE,
			'post_title'	=> $this->name,
			'post_content'	=> $this->message,
			'post_date'		=> $this->date,
			'post_status'	=> 'pending',
		);

		$args = apply_filters( 'rtb_insert_booking_data', $args, $this );

		$id = wp_insert_post( $args );

		if ( is_wp_error( $id ) || $id === false ) {
			$this->insert_post_error = $id;
			return false;
		} else {
			$this->id = $id;
		}

		$meta = array(
			'party' 			=> $this->party,
			'email' 			=> $this->email,
			'phone' 			=> $this->phone,
			'date_submission' 	=> current_time( 'timestamp' ),
		);

		$meta = apply_filters( 'rtb_insert_booking_metadata', $meta, $this );

		return update_post_meta( $this->id, 'rtb', $meta );

	}

}
} // endif;
