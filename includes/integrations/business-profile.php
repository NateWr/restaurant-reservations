<?php if ( !defined( 'ABSPATH' ) ) exit;
/**
 * Business Profile: Enable multi-location support in Restaurant Reservations
 * when locations are activated in Business Profile
 *
 * @param string $post_type The post type to use for locations
 * @since 1.6
 */
function rtb_bp_maybe_enable_bp_locations( $post_type ) {

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
add_filter( 'rtb_set_locations_post_type', 'rtb_bp_maybe_enable_bp_locations' );

/**
 * Business Profile: Add a default display setting for the booking link
 *
 * @param array $defaults Array of display settings
 * @since 1.6
 */
function rtb_bp_booking_link_default( $defaults ) {

	$defaults['show_booking_link'] = true;

	return $defaults;
}
add_filter( 'bpfwp_default_display_settings','rtb_bp_booking_link_default' );

/**
 * Business Profile: Add callback to print the booking link in contact cards
 *
 * @param array $data Key/value list of callbacks for printing card details
 * @since 1.6
 */
function rtb_bp_add_booking_link_callback( $data ) {

	global $rtb_controller;
	$booking_page = $rtb_controller->settings->get_setting( 'booking-page' );

	if ( !empty( $booking_page ) ) {

		// Place the link at the end of other short links if they're
		// displayed
		if ( isset( $data['contact'] ) ) {
			$pos = array_search( 'contact', array_keys( $data ) );
		} elseif ( isset( $data['phone'] ) ) {
			$pos = array_search( 'phone', array_keys( $data ) );
		} elseif ( isset( $data['address'] ) ) {
			$pos = array_search( 'address', array_keys( $data ) );
		}

		if ( !empty( $pos ) ) {
			$a = array_slice( $data, 0, $pos );
			$b = array_slice( $data, $pos );
			$data = array_merge( $a, array( 'booking_page' => 'rtb_bp_print_booking_link' ), $b );
		} else {
			// If no short links are being displayed, just add it to the bottom.
			$data['booking_page'] = 'rtb_bp_print_booking_link';
		}
	}

	return $data;
}
add_filter( 'bpwfwp_component_callbacks', 'rtb_bp_add_booking_link_callback' );

/**
 * Print the booking link
 *
 * @param bool|int $location Optional location post ID being displayed
 * @since 1.6
 */
function rtb_bp_print_booking_link( $location = false ) {

	global $rtb_controller;

	$booking_page = $rtb_controller->settings->get_setting( 'booking-page'  );

	if ( $location && get_post_meta( $location, 'rtb_append_booking_form', true ) ) {
		$booking_page = $location;
	}

	$schema_type = 'Organization';
	if ( function_exists( 'bpfwp_setting' ) ) {
		$schema_type = bpfwp_setting( 'schema-type', $location );
	}

	if ( bpfwp_get_display( 'show_booking_link' ) ) :
		?>
		<div class="bp-booking">
			<a href="<?php echo esc_url( get_permalink( $booking_page ) ); ?>"<?php if ( rtb_bp_is_schema_type_compatible( $schema_type ) ) : ?> itemprop="acceptsReservations"<?php endif; ?>><?php _e( 'Book a table', 'business-profile' ); ?></a>
		</div>

	<?php elseif ( rtb_bp_is_schema_type_compatible( $schema_type ) ) : ?>
		<meta itemprop="acceptsReservations" content="<?php echo esc_url( get_permalink( $booking_page ) ); ?>">
		<?php
	endif;
}

/**
 * Business Profile: Add an option to the contact card widget to show/hide the
 * booking link
 *
 * @param array $toggles Key/value list of show/hide checkbox toggles
 * @since 1.6
 */
function rtb_bp_add_booking_link_widget_option( $toggles ) {

	// Place the option below the contact option
	$pos = array_search( 'show_contact', array_keys( $toggles ) );

	if ( ! empty( $pos ) ) {
		$a = array_slice( $toggles, 0, $pos );
		$b = array_slice( $toggles, $pos );
		$toggles = array_merge( $a, array( 'show_booking_link' => __( 'Show book a table link', 'business-profile' ) ) , $b );
	} else {
		// If no short links are being displayed, just add it to the bottom.
		$toggles['show_booking_link'] = __( 'Show book a table link', 'business-profile' );
	}

	return $toggles;
}
add_filter( 'bpfwp_widget_display_toggles', 'rtb_bp_add_booking_link_widget_option' );

/**
 * Business Profile: Check if a given schema type supports the
 *`acceptsReservations` param
 *
 * Only `FoodEstablishment` and child schemas of that type are allowed to use
 * the `acceptsReservations` parameter.
 *
 * @param string $type Schema type. See: https://schema.org/docs/full.html
 * @since 1.6
 */
function rtb_bp_is_schema_type_compatible( $type ) {

	$food_schema_types = rtb_bp_food_schema_types();

	$allowed_schema_types = array_keys( $food_schema_types );
	$allowed_schema_types[] = 'FoodEstablishment';

	return in_array( $type, $allowed_schema_types );
}

/**
 * Business Profile: Add all FoodEstablishment sub-types to the list of
 * available schema
 *
 * @param array $schema_types Key/value with id/label of schema types
 * @since 1.6
 */
function rtb_bp_schema_types( $schema_types ) {

	$pos = array_search( 'FoodEstablishment', array_keys( $schema_types ) ) + 1;

	// Do nothing if no Food Establishment has been found
	if ( empty( $pos ) ) {
		return $schema_types;
	}

	$a = array_slice( $schema_types, 0, $pos );
	$b = array_slice( $schema_types, $pos );
	$schema_types = array_merge( $a, rtb_bp_food_schema_types(), $b );

	return $schema_types;
}
add_filter( 'bp_schema_types', 'rtb_bp_schema_types' );

/**
 * Business Profile: Get an array of all FoodEstablishment sub-types with
 * labels
 *
 * @since 1.6
 */
function rtb_bp_food_schema_types() {

	return array(
		'Baker' => __( '--- Baker', 'restaurant-reservations' ),
		'BarOrPub' => __( '--- Bar or Pub', 'restaurant-reservations' ),
		'Brewery' => __( '--- Brewery', 'restaurant-reservations' ),
		'CafeOrCoffeeShop' => __( '--- Cafe or Coffee Shop', 'restaurant-reservations' ),
		'FastFoodRestaurant' => __( '--- FastFoodRestaurant', 'restaurant-reservations' ),
		'IceCreamShop' => __( '--- Ice Cream Shop', 'restaurant-reservations' ),
		'Restaurant' => __( '--- Restaurant', 'restaurant-reservations' ),
		'Winery' => __( '--- Winery', 'restaurant-reservations' ),
	);
}
