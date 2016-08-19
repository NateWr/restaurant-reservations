<?php
/**
 * Template functions for rendering booking forms, etc.
 */

/**
 * Create a shortcode to render the booking form
 * @since 0.0.1
 */
if ( !function_exists( 'rtb_booking_form_shortcode' ) ) {
function rtb_booking_form_shortcode( $args = array() ) {

	$args = shortcode_atts(
		array(
			'location' => 0,
		),
		$args,
		'booking-form'
	);

	return rtb_print_booking_form( $args );
}
add_shortcode( 'booking-form', 'rtb_booking_form_shortcode' );
} // endif;

/**
 * Print the booking form's HTML code, including error handling and confirmation
 * notices.
 * @since 0.0.1
 */
if ( !function_exists( 'rtb_print_booking_form' ) ) {
function rtb_print_booking_form( $args = array() ) {

	global $rtb_controller;

	// Only allow the form to be displayed once on a page
	if ( $rtb_controller->form_rendered === true ) {
		return;
	} else {
		$rtb_controller->form_rendered = true;
	}

	// Sanitize incoming arguments
	if ( isset( $args['location'] ) ) {
		$args['location'] = $rtb_controller->locations->get_location_term_id( $args['location'] );
	} else {
		$args['location'] = 0;
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

		if ( get_class( $rtb_controller->request ) === 'stdClass' ) {
			require_once( RTB_PLUGIN_DIR . '/includes/Booking.class.php' );
			$rtb_controller->request = new rtbBooking();
		}

		$rtb_controller->request->insert_booking();
	}

	// Define the form's action parameter
	$booking_page = $rtb_controller->settings->get_setting( 'booking-page' );
	if ( !empty( $booking_page ) ) {
		$booking_page = get_permalink( $booking_page );
	}

	// Retrieve the form fields
	$fields = $rtb_controller->settings->get_booking_form_fields( $rtb_controller->request, $args );

	ob_start();

	?>

<div class="rtb-booking-form">
	<?php if ( $rtb_controller->request->request_inserted === true ) : ?>
	<div class="rtb-message">
		<p><?php echo $rtb_controller->settings->get_setting( 'success-message' ); ?></p>
	</div>
	<?php else : ?>
	<form method="POST" action="<?php echo esc_attr( $booking_page ); ?>">
		<input type="hidden" name="action" value="booking_request">

		<?php if ( !empty( $args['location'] ) ) : ?>
			<input type="hidden" name="rtb-location" value="<?php echo absint( $args['location'] ); ?>">
		<?php endif; ?>

		<?php do_action( 'rtb_booking_form_before_fields' ); ?>

		<?php foreach( $fields as $fieldset => $contents ) :
			$fieldset_classes = isset( $contents['callback_args']['classes'] ) ? $contents['callback_args']['classes'] : array();
			$legend_classes = isset( $contents['callback_args']['legend_classes'] ) ? $contents['callback_args']['legend_classes'] : array();
		?>
		<fieldset <?php echo rtb_print_element_class( $fieldset, $fieldset_classes ); ?>>

			<?php if ( !empty( $contents['legend'] ) ) : ?>
			<legend <?php echo rtb_print_element_class( '', $legend_classes ); ?>>
				<?php echo $contents['legend']; ?>
			</legend>
			<?php endif; ?>

			<?php
				foreach( $contents['fields'] as $slug => $field ) {

					$args = empty( $field['callback_args'] ) ? null : $field['callback_args'];

					call_user_func( $field['callback'], $slug, $field['title'], $field['request_input'], $args );
				}
			?>
		</fieldset>
		<?php endforeach; ?>

		<?php do_action( 'rtb_booking_form_after_fields' ); ?>

		<?php
			$button = sprintf(
				'<button type="submit">%s</button>',
				apply_filters( 'rtb_booking_form_submit_label', __( 'Request Booking', 'restaurant-reservations' ) )
			);

			echo apply_filters( 'rtb_booking_form_submit_button', $button );
		?>


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

	global $wp_scripts;

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
			'early_bookings' => current_user_can( 'manage_bookings' ) ? '' : $rtb_controller->settings->get_setting( 'early-bookings' ),
			'late_bookings' => current_user_can( 'manage_bookings' ) ? '' : $rtb_controller->settings->get_setting( 'late-bookings' ),
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
function rtb_print_form_text_field( $slug, $title, $value, $args = array() ) {

	$slug = esc_attr( $slug );
	$value = esc_attr( $value );
	$type = empty( $args['input_type'] ) ? 'text' : esc_attr( $args['input_type'] );
	$classes = isset( $args['classes'] ) ? $args['classes'] : array();
	$classes[] = 'rtb-text';

	?>

	<div <?php echo rtb_print_element_class( $slug, $classes ); ?>>
		<?php echo rtb_print_form_error( $slug ); ?>
		<label for="rtb-<?php echo $slug; ?>">
			<?php echo $title; ?>
		</label>
		<input type="<?php echo $type; ?>" name="rtb-<?php echo $slug; ?>" id="rtb-<?php echo $slug; ?>" value="<?php echo $value; ?>">
	</div>

	<?php

}
} // endif;

/**
 * Print a textarea form field
 * @since 1.3
 */
if ( !function_exists( 'rtb_print_form_textarea_field' ) ) {
function rtb_print_form_textarea_field( $slug, $title, $value, $args = array() ) {

	$slug = esc_attr( $slug );
	// Strip out <br> tags when placing in a textarea
	$value = preg_replace('/\<br(\s*)?\/?\>/i', '', $value);
	$classes = isset( $args['classes'] ) ? $args['classes'] : array();
	$classes[] = 'rtb-textarea';

	?>

	<div <?php echo rtb_print_element_class( $slug, $classes ); ?>>
		<?php echo rtb_print_form_error( $slug ); ?>
		<label for="rtb-<?php echo $slug; ?>">
			<?php echo $title; ?>
		</label>
		<textarea name="rtb-<?php echo $slug; ?>" id="rtb-<?php echo $slug; ?>"><?php echo $value; ?></textarea>
	</div>

	<?php

}
} // endif;

/**
 * Print a select form field
 * @since 1.3
 */
if ( !function_exists( 'rtb_print_form_select_field' ) ) {
function rtb_print_form_select_field( $slug, $title, $value, $args ) {

	$slug = esc_attr( $slug );
	$value = esc_attr( $value );
	$options = is_array( $args['options'] ) ? $args['options'] : array();
	$classes = isset( $args['classes'] ) ? $args['classes'] : array();
	$classes[] = 'rtb-select';

	?>

	<div <?php echo rtb_print_element_class( $slug, $classes ); ?>>
		<?php echo rtb_print_form_error( $slug ); ?>
		<label for="rtb-<?php echo $slug; ?>">
			<?php echo $title; ?>
		</label>
		<select name="rtb-<?php echo $slug; ?>" id="rtb-<?php echo $slug; ?>">
			<?php foreach ( $options as $opt_value => $opt_label ) : ?>
			<option value="<?php echo esc_attr( $opt_value ); ?>" <?php selected( $opt_value, $value ); ?>><?php echo esc_attr( $opt_label ); ?></option>
			<?php endforeach; ?>
		</select>
	</div>

	<?php

}
} // endif;

/**
 * Print a checkbox form field
 *
 * @uses rtb_print_form_tick_field
 * @since 1.3.1
 */
if ( !function_exists( 'rtb_print_form_checkbox_field' ) ) {
function rtb_print_form_checkbox_field( $slug, $title, $value, $args ) {

	$slug = esc_attr( $slug );
	$value = !empty( $value ) ? array_map( 'esc_attr', $value ) : array();
	$options = is_array( $args['options'] ) ? $args['options'] : array();
	$classes = isset( $args['classes'] ) ? $args['classes'] : array();
	$classes[] = 'rtb-checkbox';

	?>

	<div <?php echo rtb_print_element_class( $slug, $classes ); ?>>
		<?php echo rtb_print_form_error( $slug ); ?>
		<label>
			<?php echo $title; ?>
		</label>
		<?php foreach ( $options as $opt_value => $opt_label ) : ?>
		<label>
			<input type="checkbox" name="rtb-<?php echo $slug; ?>[]" id="rtb-<?php echo $slug; ?>-<?php echo esc_attr( $opt_value ); ?>" value="<?php echo esc_attr( $opt_value ); ?>"<?php echo !empty( $value ) && in_array( $opt_value, $value ) ? ' checked' : ''; ?>>
			<?php echo $opt_label; ?>
		</label>
		<?php endforeach; ?>
	</div>

	<?php
}
} // endif;

/**
 * Print a radio button form field
 *
 * @uses rtb_print_form_tick_field
 * @since 1.3.1
 */
if ( !function_exists( 'rtb_print_form_radio_field' ) ) {
function rtb_print_form_radio_field( $slug, $title, $value, $args ) {

	$slug = esc_attr( $slug );
	$value = esc_attr( $value );
	$options = is_array( $args['options'] ) ? $args['options'] : array();
	$classes = isset( $args['classes'] ) ? $args['classes'] : array();
	$classes[] = 'rtb-radio';

	?>

	<div <?php echo rtb_print_element_class( $slug, $classes ); ?>>
		<?php echo rtb_print_form_error( $slug ); ?>
		<label>
			<?php echo $title; ?>
		</label>
		<?php foreach ( $options as $opt_value => $opt_label ) : ?>
		<label>
			<input type="radio" name="rtb-<?php echo $slug; ?>" id="rtb-<?php echo $slug; ?>" value="<?php echo esc_attr( $opt_value ); ?>" <?php checked( $opt_value, $value ); ?>>
			<?php echo $opt_label; ?>
		</label>
		<?php endforeach; ?>
	</div>

	<?php
}
} // endif;

/**
 * Print a confirm prompt form field
 *
 * @since 1.3.1
 */
if ( !function_exists( 'rtb_print_form_confirm_field' ) ) {
function rtb_print_form_confirm_field( $slug, $title, $value, $args ) {

	$slug = esc_attr( $slug );
	$value = esc_attr( $value );
	$classes = isset( $args['classes'] ) ? $args['classes'] : array();
	$classes[] = 'rtb-confirm';

	?>

	<div <?php echo rtb_print_element_class( $slug, $classes ); ?>>
		<?php echo rtb_print_form_error( $slug ); ?>
		<label for="rtb-<?php echo $slug; ?>">
			<input type="checkbox" name="rtb-<?php echo $slug; ?>" id="rtb-<?php echo $slug; ?>" value="1" <?php checked( $value, 1 ); ?>>
			<?php echo $title; ?>
		</label>
	</div>

	<?php

}
} // endif;

/**
 * Print the Add Message link to display the message field
 * @since 1.3
 */
if ( !function_exists( 'rtb_print_form_message_link' ) ) {
function rtb_print_form_message_link( $slug, $title, $value, $args = array() ) {

	$slug = esc_attr( $slug );
	$value = esc_attr( $value );
	$classes = isset( $args['classes'] ) ? $args['classes'] : array();

	?>

	<div <?php echo rtb_print_element_class( $slug, $classes ); ?>>
		<a href="#">
			<?php echo $title; ?>
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

/**
 * Print a class attribute based on the slug and optional classes, provided with arguments
 * @since 1.3
 */
if ( !function_exists( 'rtb_print_element_class' ) ) {
function rtb_print_element_class( $slug, $additional_classes = array() ) {
	$classes = empty( $additional_classes ) ? array() : $additional_classes;

	if ( ! empty( $slug ) ) {
		array_push( $classes, $slug );
	}

	$class_attr = esc_attr( join( ' ', $classes ) );

	return empty( $class_attr ) ? '' : sprintf( 'class="%s"', $class_attr );

}
} // endif;
