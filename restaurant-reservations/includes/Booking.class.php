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
	 * Whether or not this request has been processed. Used to prevent
	 * duplicate forms on one page from processing a booking form more than
	 * once.
	 * @since 0.0.1
	 */
	public $request_processed = false;

	/**
	 * Whether or not this request was successfully saved to the database.
	 * @since 0.0.1
	 */
	public $request_inserted = false;

	public function __construct() {}

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
		$this->date = $this->format_date( $post->post_date );
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
		$date = mysql2date( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), $date);
		return apply_filters( 'get_the_date', $date );
	}

	/**
	 * Insert a new booking submission into the database
	 *
	 * Validates the data, adds it to the database and executes notifications
	 * @since 0.0.1
	 */
	public function insert_booking() {

		// Check if this request has already been processed. If multiple forms
		// exist on the same page, this prevents a single submission from
		// being added twice.
		if ( $this->request_processed === true ) {
			return true;
		}

		$this->request_processed = true;

		$this->validate_submission();
		if ( $this->is_valid_submission() === false ) {
			return false;
		}

		if ( $this->insert_post_data() === false ) {
			return false;
		} else {
			$this->request_inserted = true;
		}

		do_action( 'rtb_insert_booking', $this );

		return true;
	}

	/**
	 * Validate submission data. Expects to find data in $_POST.
	 * @since 0.0.1
	 */
	public function validate_submission() {

		$this->validation_errors = array();

		// Date
		$date = empty( $_POST['rtb-date'] ) ? false : $_POST['rtb-date'];
		if ( $date === false ) {
			$this->validation_errors[] = array(
				'field'		=> 'date',
				'error_msg'	=> 'Booking request missing date',
				'message'	=> __( 'Please enter the date you would like to book.', RTB_TEXTDOMAIN ),
			);

		} else {
			try {
				$date = new DateTime( $_POST['rtb-date'] );
			} catch ( Exception $e ) {
				$this->validation_errors[] = array(
					'field'		=> 'date',
					'error_msg'	=> $e->getMessage(),
					'message'	=> __( 'The date you entered is not valid. Please select from one of the dates in the calendar.', RTB_TEXTDOMAIN ),
				);
			}
		}

		// Time
		$time = empty( $_POST['rtb-time'] ) ? false : $_POST['rtb-time'];
		if ( $time === false ) {
			$this->validation_errors[] = array(
				'field'		=> 'time',
				'error_msg'	=> 'Booking request missing time',
				'message'	=> __( 'Please enter the time you would like to book.', RTB_TEXTDOMAIN ),
			);

		} else {
			try {
				$time = new DateTime( $_POST['rtb-time'] );
			} catch ( Exception $e ) {
				$this->validation_errors[] = array(
					'field'		=> 'time',
					'error_msg'	=> $e->getMessage(),
					'message'	=> __( 'The time you entered is not valid. Please select from one of the times provided.', RTB_TEXTDOMAIN ),
				);
			}
		}

		// Check against valid open dates/times
		if ( is_object( $time ) && is_object( $date ) ) {

			global $rtb_controller;

			$request = new DateTime( $date->format( 'Y-m-d' ) . ' ' . $time->format( 'H:i:s' ) );

			$early_bookings = $rtb_controller->settings->get_setting( 'early-bookings' );
			if ( !empty( $early_bookings ) ) {
				$early_bookings_seconds = $early_bookings * 24 * 60 * 60; // Advanced bookings allowance in seconds
				if ( $request->format( 'U' ) > ( current_time( 'timestamp' ) + $early_bookings_seconds ) ) {
					$this->validation_errors[] = array(
						'field'		=> 'time',
						'error_msg'	=> 'Booking request too far in the future',
						'message'	=> sprintf( __( 'Sorry, bookings can not be made more than %s days in advance.', RTB_TEXTDOMAIN ), $early_bookings ),
					);
				}
			}

			$late_bookings = $rtb_controller->settings->get_setting( 'late-bookings' );
			if ( empty( $late_bookings ) ) {
				if ( $request->format( 'U' ) < current_time( 'timestamp' ) ) {
					$this->validation_errors[] = array(
						'field'		=> 'time',
						'error_msg'	=> 'Booking request in the past',
						'message'	=> __( 'Sorry, bookings can not be made in the past.', RTB_TEXTDOMAIN ),
					);
				}

			} else {
				$late_bookings_seconds = $late_bookings * 60; // Late bookings allowance in seconds
				if ( $request->format( 'U' ) < ( current_time( 'timestamp' ) + $late_bookings_seconds ) ) {
					if ( $late_bookings >= 1440 ) {
						$late_bookings_message = sprintf( __( 'Sorry, bookings must be made more than %s days in advance.', RTB_TEXTDOMAIN ), $late_bookings / 1440 );
					} elseif ( $late_bookings >= 60 ) {
						$late_bookings_message = sprintf( __( 'Sorry, bookings must be made more than %s hours in advance.', RTB_TEXTDOMAIN ), $late_bookings / 60 );
					} else {
						$late_bookings_message = sprintf( __( 'Sorry, bookings must be made more than %s mings in advance.', RTB_TEXTDOMAIN ), $late_bookings );
					}
					$this->validation_errors[] = array(
						'field'		=> 'time',
						'error_msg'	=> 'Booking request made too close to the reserved time',
						'message'	=> $late_bookings_message,
					);
				}
			}

			// Check against scheduling exception rules
			$exceptions = $rtb_controller->settings->get_setting( 'schedule-closed' );
			if ( empty( $this->validation_errors ) && !empty( $exceptions ) ) {
				$exception_is_active = false;
				$datetime_is_valid = false;
				foreach( $exceptions as $exception ) {
					$excp_date = new DateTime( $exception['date'] );
					if ( $excp_date->format( 'Y-m-d' ) == $request->format( 'Y-m-d' ) ) {
						$exception_is_active = true;

						// Closed all day
						if ( empty( $exception['time'] ) ) {
							continue;
						}

						$excp_start_time = empty( $exception['time']['start'] ) ? $request : new DateTime( $exception['date'] . ' ' . $exception['time']['start'] );
						$excp_end_time = empty( $exception['time']['end'] ) ? $request : new DateTime( $exception['date'] . ' ' . $exception['time']['end'] );

						if ( $request->format( 'U' ) >= $excp_start_time->format( 'U' ) && $request->format( 'U' ) <= $excp_end_time->format( 'U' ) ) {
							$datetime_is_valid = true;
							break;
						}
					}
				}

				if ( $exception_is_active && !$datetime_is_valid ) {
					$this->validation_errors[] = array(
						'field'		=> 'date',
						'error_msg'	=> 'Booking request made on invalid date or time in an exception rule',
						'message'	=> __( 'Sorry, no bookings are being accepted then.', RTB_TEXTDOMAIN ),
					);
				}
			}

			// Check against weekly scheduling rules
			$rules = $rtb_controller->settings->get_setting( 'schedule-open' );
			if ( empty( $exception_is_active ) && empty( $this->validation_errors ) && !empty( $rules ) ) {
				$request_weekday = strtolower( $request->format( 'l' ) );
				$time_is_valid = null;
				$day_is_valid = null;
				foreach( $rules as $rule ) {

					if ( !empty( $rule['weekdays'][ $request_weekday ] ) ) {
						$day_is_valid = true;

						if ( empty( $rule['time'] ) ) {
							$time_is_valid = true; // Days with no time values are open all day
							break;
						}

						$too_early = true;
						$too_late = true;

						// Too early
						if ( !empty( $rule['time']['start'] ) ) {
							$rule_start_time = new DateTime( $request->format( 'Y-m-d' ) . ' ' . $rule['time']['start'] );
							if ( $rule_start_time->format( 'U' ) <= $request->format( 'U' ) ) {
								$too_early = false;
							}
						}

						// Too late
						if ( !empty( $rule['time']['end'] ) ) {
							$rule_end_time = new DateTime( $request->format( 'Y-m-d' ) . ' ' . $rule['time']['end'] );
							if ( $rule_end_time->format( 'U' ) >= $request->format( 'U' ) ) {
								$too_late = false;
							}
						}

						// Valid time found
						if ( $too_early === false && $too_late === false) {
							$time_is_valid = true;
							break;
						}
					}
				}

				if ( !$day_is_valid ) {
					$this->validation_errors[] = array(
						'field'		=> 'date',
						'error_msg'	=> 'Booking request made on an invalid date',
						'message'	=> __( 'Sorry, no bookings are being accepted on that date.', RTB_TEXTDOMAIN ),
					);
				} elseif ( !$time_is_valid ) {
					$this->validation_errors[] = array(
						'field'		=> 'time',
						'error_msg'	=> 'Booking request made at an invalid time',
						'message'	=> __( 'Sorry, no bookings are being accepted at that time.', RTB_TEXTDOMAIN ),
					);
				}
			}

			// Accept the date if it has passed validation
			if ( empty( $this->validation_errors ) ) {
				$this->date = $request->format( 'Y-m-d H:i:s' );
			}
		}

		// Save requested date/time values in case they need to be
		// printed in the form again
		$this->request_date = empty( $_POST['rtb-date'] ) ? '' : $_POST['rtb-date'];
		$this->request_time = empty( $_POST['rtb-time'] ) ? '' : $_POST['rtb-time'];

		// Name
		$this->name = empty( $_POST['rtb-name'] ) ? '' : wp_strip_all_tags( sanitize_text_field( $_POST['rtb-name'] ), true ); // @todo should I limit length?
		if ( empty( $this->name ) ) {
			$this->validation_errors[] = array(
				'field'			=> 'name',
				'post_variable'	=> $this->name,
				'message'	=> __( 'Please enter a name for this booking.', RTB_TEXTDOMAIN ),
			);
		}

		// Party
		$this->party = empty( $_POST['rtb-party'] ) ? '' : sanitize_text_field( $_POST['rtb-party'] );
		if ( empty( $this->party ) ) {
			$this->validation_errors[] = array(
				'field'			=> 'party',
				'post_variable'	=> $this->party,
				'message'	=> __( 'Please let us know how many people will be in your party.', RTB_TEXTDOMAIN ),
			);
		}

		// Email/Phone
		$this->email = empty( $_POST['rtb-email'] ) ? '' : sanitize_text_field( $_POST['rtb-email'] ); // @todo email validation? send notification back to form on bad email address.
		if ( empty( $this->email ) ) {
			$this->validation_errors[] = array(
				'field'			=> 'email',
				'post_variable'	=> $this->email,
				'message'	=> __( 'Please enter an email address so we can confirm your booking.', RTB_TEXTDOMAIN ),
			);
		}

		// Phone/Message
		$this->phone = empty( $_POST['rtb-phone'] ) ? '' : sanitize_text_field( $_POST['rtb-phone'] );
		$this->message = empty( $_POST['rtb-message'] ) ? '' : sanitize_text_field( $_POST['rtb-message'] );

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
			$this->ID = $id;
		}

		$meta = array(
			'party' 			=> $this->party,
			'email' 			=> $this->email,
			'phone' 			=> $this->phone,
			'date_submission' 	=> current_time( 'timestamp' ),
		);

		$meta = apply_filters( 'rtb_insert_booking_metadata', $meta, $this );

		return update_post_meta( $this->ID, 'rtb', $meta );

	}

}
} // endif;
