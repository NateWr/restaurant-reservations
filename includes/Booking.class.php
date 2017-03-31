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
		$this->date = $post->post_date;
		$this->message = $post->post_content;
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
			'logs' => array(),
			'ip' => '',
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
		$this->logs = $meta['logs'];
		$this->ip = $meta['ip'];
	}

	/**
	 * Prepare booking data loaded from the database for display in a booking
	 * form as request fields. This is used, eg, for splitting datetime values
	 * into date and time fields.
	 * @since 1.3
	 */
	public function prepare_request_data() {

		// Split $date to $request_date and $request_time
		if ( empty( $this->request_date ) || empty( $this->request_time ) ) {
			$date = new DateTime( $this->date );
			$this->request_date = $date->format( 'Y/m/d' );
			$this->request_time = $date->format( 'h:i A' );
		}
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
	 * Format a timestamp into a human-readable date
	 *
	 * @since 1.7.1
	 */
	public function format_timestamp( $timestamp ) {
		$time = date( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), $timestamp );
		return $time;
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

		if ( empty( $this->ID ) ) {
			$action = 'insert';
		} else {
			$action = 'update';
		}

		$this->validate_submission();
		if ( $this->is_valid_submission() === false ) {
			return false;
		}

		if ( $this->insert_post_data() === false ) {
			return false;
		} else {
			$this->request_inserted = true;
		}

		do_action( 'rtb_' . $action . '_booking', $this );

		return true;
	}

	/**
	 * Validate submission data. Expects to find data in $_POST.
	 * @since 0.0.1
	 */
	public function validate_submission() {

		global $rtb_controller;

		$this->validation_errors = array();

		// Date
		$date = empty( $_POST['rtb-date'] ) ? false : stripslashes_deep( $_POST['rtb-date'] );
		if ( $date === false ) {
			$this->validation_errors[] = array(
				'field'		=> 'date',
				'error_msg'	=> 'Booking request missing date',
				'message'	=> __( 'Please enter the date you would like to book.', 'restaurant-reservations' ),
			);

		} else {
			try {
				$date = new DateTime( stripslashes_deep( $_POST['rtb-date'] ) );
			} catch ( Exception $e ) {
				$this->validation_errors[] = array(
					'field'		=> 'date',
					'error_msg'	=> $e->getMessage(),
					'message'	=> __( 'The date you entered is not valid. Please select from one of the dates in the calendar.', 'restaurant-reservations' ),
				);
			}
		}

		// Time
		$time = empty( $_POST['rtb-time'] ) ? false : stripslashes_deep( $_POST['rtb-time'] );
		if ( $time === false ) {
			$this->validation_errors[] = array(
				'field'		=> 'time',
				'error_msg'	=> 'Booking request missing time',
				'message'	=> __( 'Please enter the time you would like to book.', 'restaurant-reservations' ),
			);

		} else {
			try {
				$time = new DateTime( stripslashes_deep( $_POST['rtb-time'] ) );
			} catch ( Exception $e ) {
				$this->validation_errors[] = array(
					'field'		=> 'time',
					'error_msg'	=> $e->getMessage(),
					'message'	=> __( 'The time you entered is not valid. Please select from one of the times provided.', 'restaurant-reservations' ),
				);
			}
		}

		// Check against valid open dates/times
		if ( is_object( $time ) && is_object( $date ) ) {

			$request = new DateTime( $date->format( 'Y-m-d' ) . ' ' . $time->format( 'H:i:s' ) );

			// Exempt Bookings Managers from the early and late bookings restrictions
			if ( !current_user_can( 'manage_bookings' ) ) {

				$early_bookings = $rtb_controller->settings->get_setting( 'early-bookings' );
				if ( !empty( $early_bookings ) ) {
					$early_bookings_seconds = $early_bookings * 24 * 60 * 60; // Advanced bookings allowance in seconds
					if ( $request->format( 'U' ) > ( current_time( 'timestamp' ) + $early_bookings_seconds ) ) {
						$this->validation_errors[] = array(
							'field'		=> 'time',
							'error_msg'	=> 'Booking request too far in the future',
							'message'	=> sprintf( __( 'Sorry, bookings can not be made more than %s days in advance.', 'restaurant-reservations' ), $early_bookings ),
						);
					}
				}

				$late_bookings = $rtb_controller->settings->get_setting( 'late-bookings' );
				if ( empty( $late_bookings ) ) {
					if ( $request->format( 'U' ) < current_time( 'timestamp' ) ) {
						$this->validation_errors[] = array(
							'field'		=> 'time',
							'error_msg'	=> 'Booking request in the past',
							'message'	=> __( 'Sorry, bookings can not be made in the past.', 'restaurant-reservations' ),
						);
					}

				} elseif ( $late_bookings === 'same_day' ) {
					if ( $request->format( 'Y-m-d' ) == current_time( 'Y-m-d' ) ) {
						$this->validation_errors[] = array(
							'field'		=> 'time',
							'error_msg'	=> 'Booking request made on same day',
							'message'	=> __( 'Sorry, bookings can not be made for the same day.', 'restaurant-reservations' ),
						);
					}

				} else {
					$late_bookings_seconds = $late_bookings * 60; // Late bookings allowance in seconds
					if ( $request->format( 'U' ) < ( current_time( 'timestamp' ) + $late_bookings_seconds ) ) {
						if ( $late_bookings >= 1440 ) {
							$late_bookings_message = sprintf( __( 'Sorry, bookings must be made more than %s days in advance.', 'restaurant-reservations' ), $late_bookings / 1440 );
						} elseif ( $late_bookings >= 60 ) {
							$late_bookings_message = sprintf( __( 'Sorry, bookings must be made more than %s hours in advance.', 'restaurant-reservations' ), $late_bookings / 60 );
						} else {
							$late_bookings_message = sprintf( __( 'Sorry, bookings must be made more than %s minutes in advance.', 'restaurant-reservations' ), $late_bookings );
						}
						$this->validation_errors[] = array(
							'field'		=> 'time',
							'error_msg'	=> 'Booking request made too close to the reserved time',
							'message'	=> $late_bookings_message,
						);
					}
				}
			}

			// Check against scheduling exception rules
			$exceptions = $rtb_controller->settings->get_setting( 'schedule-closed' );
			if ( empty( $this->validation_errors ) && !empty( $exceptions ) && !current_user_can( 'manage_bookings' ) ) {
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
						'message'	=> __( 'Sorry, no bookings are being accepted then.', 'restaurant-reservations' ),
					);
				}
			}

			// Check against weekly scheduling rules
			$rules = $rtb_controller->settings->get_setting( 'schedule-open' );
			if ( empty( $exception_is_active ) && empty( $this->validation_errors ) && !empty( $rules ) && !current_user_can( 'manage_bookings' ) ) {
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
						'message'	=> __( 'Sorry, no bookings are being accepted on that date.', 'restaurant-reservations' ),
					);
				} elseif ( !$time_is_valid ) {
					$this->validation_errors[] = array(
						'field'		=> 'time',
						'error_msg'	=> 'Booking request made at an invalid time',
						'message'	=> __( 'Sorry, no bookings are being accepted at that time.', 'restaurant-reservations' ),
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
		$this->request_date = empty( $_POST['rtb-date'] ) ? '' : stripslashes_deep( $_POST['rtb-date'] );
		$this->request_time = empty( $_POST['rtb-time'] ) ? '' : stripslashes_deep( $_POST['rtb-time'] );

		// Name
		$this->name = empty( $_POST['rtb-name'] ) ? '' : wp_strip_all_tags( sanitize_text_field( stripslashes_deep( $_POST['rtb-name'] ) ), true ); // @todo should I limit length?
		if ( empty( $this->name ) ) {
			$this->validation_errors[] = array(
				'field'			=> 'name',
				'post_variable'	=> $this->name,
				'message'	=> __( 'Please enter a name for this booking.', 'restaurant-reservations' ),
			);
		}

		// Party
		$this->party = empty( $_POST['rtb-party'] ) ? '' : absint( $_POST['rtb-party'] );
		if ( empty( $this->party ) ) {
			$this->validation_errors[] = array(
				'field'			=> 'party',
				'post_variable'	=> $this->party,
				'message'	=> __( 'Please let us know how many people will be in your party.', 'restaurant-reservations' ),
			);

		// Check party size
		} else {
			$party_size = $rtb_controller->settings->get_setting( 'party-size' );
			if ( !empty( $party_size ) && $party_size < $this->party ) {
				$this->validation_errors[] = array(
					'field'			=> 'party',
					'post_variable'	=> $this->party,
					'message'	=> sprintf( __( 'We only accept bookings for parties of up to %d people.', 'restaurant-reservations' ), $party_size ),
				);
			}
			$party_size_min = $rtb_controller->settings->get_setting( 'party-size-min' );
			if ( !empty( $party_size_min ) && $party_size_min > $this->party ) {
				$this->validation_errors[] = array(
					'field'			=> 'party',
					'post_variable'	=> $this->party,
					'message'	=> sprintf( __( 'We only accept bookings for parties of more than %d people.', 'restaurant-reservations' ), $party_size_min ),
				);
			}
		}

		// Email
		$this->email = empty( $_POST['rtb-email'] ) ? '' : sanitize_text_field( stripslashes_deep( $_POST['rtb-email'] ) ); // @todo email validation? send notification back to form on bad email address.
		if ( empty( $this->email ) ) {
			$this->validation_errors[] = array(
				'field'			=> 'email',
				'post_variable'	=> $this->email,
				'message'	=> __( 'Please enter an email address so we can confirm your booking.', 'restaurant-reservations' ),
			);
		} elseif ( !is_email( $this->email ) && apply_filters( 'rtb_require_valid_email', true ) ) {
			$this->validation_errors[] = array(
				'field'			=> 'email',
				'post_variable'	=> $this->email,
				'message'	=> __( 'Please enter a valid email address so we can confirm your booking.', 'restaurant-reservations' ),
			);
		}

		// Phone
		$this->phone = empty( $_POST['rtb-phone'] ) ? '' : sanitize_text_field( stripslashes_deep( $_POST['rtb-phone'] ) );
		$phone_required = $rtb_controller->settings->get_setting( 'require-phone' );
		if ( $phone_required && empty( $this->phone ) ) {
			$this->validation_errors[] = array(
				'field'			=> 'phone',
				'post_variable'	=> $this->phone,
				'message'	=> __( 'Please provide a phone number so we can confirm your booking.', 'restaurant-reservations' ),
			);
		}

		// Message
		$this->message = empty( $_POST['rtb-message'] ) ? '' : nl2br( wp_kses_post( stripslashes_deep( $_POST['rtb-message'] ) ) );

		// Post Status (define a default post status is none passed)
		if ( !empty( $_POST['rtb-post-status'] ) && array_key_exists( $_POST['rtb-post-status'], $rtb_controller->cpts->booking_statuses ) ) {
			$this->post_status = sanitize_text_field( stripslashes_deep( $_POST['rtb-post-status'] ) );
		} else {
			$this->post_status = 'pending';
		}

		// Check if any required fields are empty
		$required_fields = $rtb_controller->settings->get_required_fields();
		foreach( $required_fields as $slug => $field ) {
			if ( !$this->field_has_error( $slug ) && $this->is_field_empty( $slug ) ) {
				$this->validation_errors[] = array(
					'field'			=> $slug,
					'post_variable'	=> '',
					'message'	=> __( 'Please complete this field to request a booking.', 'restaurant-reservations' ),
				);
			}
		}

		// Check if the email or IP is banned
		if ( !current_user_can( 'manage_bookings' ) ) {
			$ip = $_SERVER['REMOTE_ADDR'];
			if ( !$this->is_valid_ip( $ip ) || !$this->is_valid_email( $this->email ) ) {
				$this->validation_errors[] = array(
					'field'			=> 'date',
					'post_variable'	=> $ip,
					'message'	=> __( 'Your booking has been rejected. Please call us if you would like to make a booking.', 'restaurant-reservations' ),
				);
			} elseif ( empty( $this->ip ) ) {
				$this->ip = sanitize_text_field( $ip );
			}
		} elseif ( empty( $this->ip ) ) {
			$this->ip = sanitize_text_field( $_SERVER['REMOTE_ADDR'] );
		}

		do_action( 'rtb_validate_booking_submission', $this );

	}

	/**
	 * Check if submission is valid
	 *
	 * @since 0.0.1
	 */
	public function is_valid_submission() {

		if ( !count( $this->validation_errors ) ) {
			return true;
		}

		return false;
	}

	/**
	 * Check if a field already has an error attached to it
	 *
	 * @field string Field slug
	 * @since 1.3
	 */
	public function field_has_error( $field_slug ) {

		foreach( $this->validation_errors as $error ) {
			if ( $error['field'] == $field_slug ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Check if a field is missing
	 *
	 * Checks for empty strings and arrays, but accepts '0'
	 * @since 0.1
	 */
	public function is_field_empty( $slug ) {

		$input = isset( $_POST['rtb-' . $slug ] ) ? $_POST['rtb-' . $slug] : '';

		if ( ( is_string( $input ) && trim( $input ) == '' ) ||
			( is_array( $input ) && empty( $input ) ) ) {
			return true;
		}

		return false;
	}

	/**
	 * Check if an IP address has been banned
	 *
	 * @param string $ip
	 * @return bool
	 * @since 1.7
	 */
	public function is_valid_ip( $ip = null ) {

		if ( is_null( $ip ) ) {
			$ip = isset( $this->ip ) ? $this->ip : null;
			if ( is_null( $ip ) ) {
				return false;
			}
		}

		global $rtb_controller;

		$banned_ips = array_filter( explode( "\n", $rtb_controller->settings->get_setting( 'ban-ips' ) ) );

		foreach( $banned_ips as $banned_ip ) {
			if ( $ip == trim( $banned_ip ) ) {
				return false;
			}
		}

		return true;
	}

	/**
	 * Check if an email address has been banned
	 *
	 * @param string $email
	 * @return bool
	 * @since 1.7
	 */
	public function is_valid_email( $email = null ) {

		if ( is_null( $email ) ) {
			$email = isset( $this->email ) ? $this->email : null;
			if ( is_null( $email ) ) {
				return false;
			}
		}

		global $rtb_controller;

		$banned_emails = array_filter( explode( "\n", $rtb_controller->settings->get_setting( 'ban-emails' ) ) );

		foreach( $banned_emails as $banned_email ) {
			if ( $email == trim( $banned_email ) ) {
				return false;
			}
		}

		return true;
	}

	/**
	 * Add a log entry to the booking
	 *
	 * @since 1.3.1
	 */
	public function add_log( $type, $title, $message = '', $datetime = null ) {

		if ( empty( $datetime ) ) {
			$datetime = date( 'Y-m-d H:i:s');
		}

		if ( empty( $this->logs ) ) {
			$this->logs = array();
		}

		array_push( $this->logs, array( $type, $title, $message, $datetime ) );
	}

	/**
	 * Insert post data for a new booking or update a booking
	 * @since 0.0.1
	 */
	public function insert_post_data() {

		$args = array(
			'post_type'		=> RTB_BOOKING_POST_TYPE,
			'post_title'	=> $this->name,
			'post_content'	=> $this->message,
			'post_date'		=> $this->date,
			'post_status'	=> $this->post_status,
		);

		if ( !empty( $this->ID ) ) {
			$args['ID'] = $this->ID;
		}

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
			'ip'                => $this->ip,
		);

		if ( !empty( $this->logs ) ) {
			$meta['logs'] = $this->logs;
		}

		$meta = apply_filters( 'rtb_insert_booking_metadata', $meta, $this );

		return update_post_meta( $this->ID, 'rtb', $meta );

	}

}
} // endif;
