<?php
/**
 * Template functions for rendering booking forms, etc.
 */

/**
 * Create a shortcode to render the booking form
 * @since 0.0.1
 */
if ( !function_exists( 'rtb_booking_form_shortcode' ) ) {
function rtb_booking_form_shortcode() {
	return rtb_print_booking_form();
}
add_shortcode( 'booking-form', 'rtb_booking_form_shortcode' );
} // endif;

/**
 * Print the booking form's HTML code, including error handling and confirmation
 * notices.
 * @since 0.0.1
 */
if ( !function_exists( 'rtb_print_booking_form' ) ) {
function rtb_print_booking_form() {

	global $rtb_controller;

	// Only allow the form to be displayed once on a page
	if ( $rtb_controller->form_rendered === true ) {
		return;
	} else {
		$rtb_controller->form_rendered = true;
	}

	// Enqueue assets for the form
	rtb_enqueue_assets();

	// Allow themes and plugins to override the booking form's HTML output.
	$output = apply_filters( 'rtb_booking_form_html_pre', '' );
	if ( !empty( $output ) ) {
		return $output;
	}

	// Process a booking request
	if ( !empty( $_POST['action'] ) && $_POST['action'] == 'booking_request' ) {

		if ( empty( $rtb_controller->request ) ) {
			require_once( RTB_PLUGIN_DIR . '/includes/Booking.class.php' );
			$rtb_controller->request = new rtbBooking();
		}

		$rtb_controller->request->insert_booking();
	}

	// Set up a dummy request object if no request has been made. This just
	// simplifies the display of values in the form below
	if ( empty( $rtb_controller->request ) ) {
		$request = new stdClass();
		$request->request_processed = false;
		$request->request_inserted = false;
	} else {
		$request = $rtb_controller->request;
	}

	// Define the form's action parameter
	$booking_page = $rtb_controller->settings->get_setting( 'booking-page' );
	if ( !empty( $booking_page ) ) {
		$booking_page = get_permalink( $booking_page );
	}

	// Define the form fields
	//
	// This array defines the field details and a callback function to
	// render each field. To customize the form output, modify the
	// callback functions to point to your custom function. Don't forget
	// to output an error message in your custom callback function. You
	// can use rtb_print_form_error( $slug ) to do this.
	$fields = array(

		// Reservation details fieldset
		'reservation'	=> array(
			'legend'	=> __( 'Book a table', RTB_TEXTDOMAIN ),
			'fields'	=> array(
				'date'		=> array(
					'title'			=> __( 'Date', RTB_TEXTDOMAIN ),
					'request_input'	=> empty( $request->request_date ) ? '' : $request->request_date,
					'callback'		=> 'rtb_print_form_text_field',
				),
				'time'		=> array(
					'title'			=> __( 'Time', RTB_TEXTDOMAIN ),
					'request_input'	=> empty( $request->request_time ) ? '' : $request->request_time,
					'callback'		=> 'rtb_print_form_text_field',
				),
				'party'		=> array(
					'title'			=> __( 'Party', RTB_TEXTDOMAIN ),
					'request_input'	=> empty( $request->party ) ? '' : $request->party,
					'callback'		=> 'rtb_print_form_text_field',
				),
			),
		),

		// Contact details fieldset
		'contact'	=> array(
			'legend'	=> __( 'Contact Details', RTB_TEXTDOMAIN ),
			'fields'	=> array(
				'name'		=> array(
					'title'			=> __( 'Name', RTB_TEXTDOMAIN ),
					'request_input'	=> empty( $request->name ) ? '' : $request->name,
					'callback'		=> 'rtb_print_form_text_field',
				),
				'email'		=> array(
					'title'			=> __( 'Email', RTB_TEXTDOMAIN ),
					'request_input'	=> empty( $request->email ) ? '' : $request->email,
					'callback'		=> 'rtb_print_form_text_field',
				),
				'phone'		=> array(
					'title'			=> __( 'Phone', RTB_TEXTDOMAIN ),
					'request_input'	=> empty( $request->phone ) ? '' : $request->phone,
					'callback'		=> 'rtb_print_form_text_field',
				),
				'add-message'	=> array(
					'title'		=> __( 'Add a Message', RTB_TEXTDOMAIN ),
					'request_input'	=> '',
					'callback'	=> 'rtb_print_form_message_link',
				),
				'message'		=> array(
					'title'			=> __( 'Message', RTB_TEXTDOMAIN ),
					'request_input'	=> empty( $request->message ) ? '' : $request->message,
					'callback'		=> 'rtb_print_form_textarea_field',
				),
			),
		),
	);

	$fields = apply_filters( 'rtb_booking_form_fields', $fields, $request );

	ob_start();

	?>

<div class="rtb-booking-form">
	<?php if ( $request->request_inserted === true ) : ?>
	<div class="rtb-message">
		<p><?php echo $rtb_controller->settings->get_setting( 'success-message' ); ?></p>
	</div>
	<?php else : ?>
	<form method="POST" action="<?php echo esc_attr( $booking_page ); ?>">
		<input type="hidden" name="action" value="booking_request">

		<?php do_action( 'rtb_booking_form_before_fields' ); ?>

		<?php foreach( $fields as $fieldset => $contents ) : ?>
		<fieldset class="<?php echo $fieldset; ?>">

			<?php if ( !empty( $contents['legend'] ) ) : ?>
			<legend>
				<?php echo $contents['legend']; ?>
			</legend>
			<?php endif; ?>

			<?php
				foreach( $contents['fields'] as $slug => $field ) {
					call_user_func( $field['callback'], $slug, $field['title'], $field['request_input'] );
				}
			?>
		</fieldset>
		<?php endforeach; ?>

		<?php do_action( 'rtb_booking_form_after_fields' ); ?>
		
		<button type="submit"><?php _e( 'Request Booking', RTB_TEXTDOMAIN ); ?></button>
		
	</form>
	<?php endif; ?>
</div>

	<?php

	$output = ob_get_clean();

	$output = apply_filters( 'rtb_booking_form_html_post', $output );

	return $output;
}
} // endif;

/**
 * Enqueue the front-end CSS and Javascript for the booking form
 * @since 0.0.1
 */
if ( !function_exists( 'rtb_enqueue_assets' ) ) {
function rtb_enqueue_assets() {

	wp_enqueue_style( 'rtb-booking-form' );

	wp_enqueue_style( 'pickadate-default' );
	wp_enqueue_style( 'pickadate-date' );
	wp_enqueue_style( 'pickadate-time' );
	wp_enqueue_script( 'pickadate' );
	wp_enqueue_script( 'pickadate-date' );
	wp_enqueue_script( 'pickadate-time' );
	wp_enqueue_script( 'pickadate-legacy' );
	wp_enqueue_script( 'pickadate-i8n' ); // only registered if needed
	wp_enqueue_style( 'pickadate-rtl' ); // only registered if needed

	wp_enqueue_script( 'rtb-booking-form' );

	// Pass date and time format settings to the pickadate controls
	global $rtb_controller;
	wp_localize_script(
		'rtb-booking-form',
		'rtb_pickadate',
		array(
			'date_format' => $rtb_controller->settings->get_setting( 'date-format' ),
			'time_format'  => $rtb_controller->settings->get_setting( 'time-format' ),
			'disable_dates'	=> rtb_get_datepicker_rules(),
			'schedule_open' => $rtb_controller->settings->get_setting( 'schedule-open' ),
			'schedule_closed' => $rtb_controller->settings->get_setting( 'schedule-closed' ),
			'early_bookings' => $rtb_controller->settings->get_setting( 'early-bookings' ),
			'late_bookings' => $rtb_controller->settings->get_setting( 'late-bookings' ),
			'date_onload' => $rtb_controller->settings->get_setting( 'date-onload' ),
			'time_interval' => $rtb_controller->settings->get_setting( 'time-interval' ),
		)
	);

}
} // endif;

/**
 * Get rules for datepicker date ranges
 * See: http://amsul.ca/pickadate.js/date.htm#disable-dates
 * @since 0.0.1
 */
if ( !function_exists( 'rtb_get_datepicker_rules' ) ) {
function rtb_get_datepicker_rules() {

	global $rtb_controller;

	$disable_rules = array();

	$disabled_weekdays = array(
		'sunday'	=> 1,
		'monday'	=> 2,
		'tuesday'	=> 3,
		'wednesday'	=> 4,
		'thursday'	=> 5,
		'friday'	=> 6,
		'saturday'	=> 7,
	);

	// Determine which weekdays should be disabled
	$enabled_dates = array();
	$schedule_open = $rtb_controller->settings->get_setting( 'schedule-open' );
	if ( is_array( $schedule_open ) ) {
		foreach ( $schedule_open as $rule ) {
			if ( !empty( $rule['weekdays'] ) ) {
				foreach ( $rule['weekdays'] as $weekday => $value ) {
					unset( $disabled_weekdays[ $weekday ] );
				}
			}
		}

		if ( count( $disabled_weekdays ) < 7 ) {
			foreach ( $disabled_weekdays as $weekday ) {
				$disable_rules[] = $weekday;
			}
		}
	}

	// Handle exception dates
	$schedule_closed = $rtb_controller->settings->get_setting( 'schedule-closed' );
	if ( is_array( $schedule_closed ) ) {
		foreach ( $schedule_closed as $rule ) {

			// Disable exception dates that are closed all day
			if ( !empty( $rule['date'] ) && empty( $rule['time'] ) ) {
				$date = new DateTime( $rule['date'] );
				$disable_rules[] = array( $date->format( 'Y' ), ( $date->format( 'n' ) - 1 ), $date->format( 'j' ) );

			// Enable exception dates that have opening times
			} elseif ( !empty( $rule['date'] ) ) {
				$date = new DateTime( $rule['date'] );
				$disable_rules[] = array( $date->format( 'Y' ), ( $date->format( 'n' ) - 1 ), $date->format( 'j' ), 'inverted' );
			}

		}
	}

	return apply_filters( 'rtb_datepicker_disable_rules', $disable_rules, $schedule_open, $schedule_closed );

}
} // endif;

/**
 * Print a text input form field
 * @since 1.3
 */
if ( !function_exists( 'rtb_print_form_text_field' ) ) {
function rtb_print_form_text_field( $slug, $title, $value ) {

	$slug = esc_attr( $slug );
	$value = esc_attr( $value );

	?>
	
	<div class="<?php echo $slug; ?>">
		<?php echo rtb_print_form_error( $slug ); ?>
		<label for="rtb-<?php echo $slug; ?>">
			<?php _e( $title, RTB_TEXTDOMAIN ); ?>
		</label>
		<input type="text" name="rtb-<?php echo $slug; ?>" id="rtb-<?php echo $slug; ?>" value="<?php echo $value; ?>">
	</div>

	<?php

}
} // endif;

/**
 * Print a textarea form field
 * @since 1.3
 */
if ( !function_exists( 'rtb_print_form_textarea_field' ) ) {
function rtb_print_form_textarea_field( $slug, $title, $value ) {

	$slug = esc_attr( $slug );

	?>
	
	<div class="<?php echo $slug; ?>">
		<?php echo rtb_print_form_error( $slug ); ?>
		<label for="rtb-<?php echo $slug; ?>">
			<?php _e( $title, RTB_TEXTDOMAIN ); ?>
		</label>
		<textarea name="rtb-<?php echo $slug; ?>" id="rtb-<?php echo $slug; ?>"><?php echo $value; ?></textarea>
	</div>
			
	<?php

}
} // endif;

/**
 * Print the Add Message link to display the message field
 * @since 1.3
 */
if ( !function_exists( 'rtb_print_form_message_link' ) ) {
function rtb_print_form_message_link( $slug, $title, $value ) {

	$slug = esc_attr( $slug );
	$value = esc_attr( $value );

	?>
	
	<div class="<?php echo $slug; ?>">
		<a href="#">
			<?php _e( $title, RTB_TEXTDOMAIN ); ?>
		</a>
	</div>
			
	<?php

}
} // endif;

/**
 * Print a form validation error
 * @since 0.0.1
 */
if ( !function_exists( 'rtb_print_form_error' ) ) {
function rtb_print_form_error( $field ) {

	global $rtb_controller;

	if ( !empty( $rtb_controller->request ) && !empty( $rtb_controller->request->validation_errors ) ) {
		foreach ( $rtb_controller->request->validation_errors as $error ) {
			if ( $error['field'] == $field ) {
				echo '<div class="rtb-error">' . $error['message'] . '</div>';
			}
		}
	}
}
} // endif;
