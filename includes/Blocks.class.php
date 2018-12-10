<?php
if ( !defined( 'ABSPATH' ) ) exit;

if ( !class_exists( 'rtbBlocks' ) ) {
/**
 * Class to create, edit and display blocks for the Gutenberg editor
 *
 * @since 0.0.1
 */
class rtbBlocks {

	/**
	 * Add hooks
	 */
	public function __construct() {
		add_action( 'init', array( $this, 'register' ) );
	}

	/**
	 * Register blocks
	 */
	public function register() {

		if ( !function_exists( 'register_block_type' ) ) {
			return;
		}

		wp_register_script(
			'restaurant-reservations-blocks',
			RTB_PLUGIN_URL . '/assets/js/blocks.build.js',
			array( 'wp-blocks', 'wp-i18n', 'wp-element', 'wp-editor' )
		);

		wp_register_style(
			'restaurant-reservations-blocks',
			RTB_PLUGIN_URL . '/assets/css/block-booking-form.css',
			array()
		);

		register_block_type( 'restaurant-reservations/booking-form', array(
			'editor_script' => 'restaurant-reservations-blocks',
			'editor_style' => 'restaurant-reservations-blocks',
			'render_callback' => array( $this, 'render' )
		) );

		add_action( 'admin_init', array( $this, 'register_admin' ) );
	}

	/**
	 * Register admin-only assets for block handling
	 */
	public function register_admin() {

		global $rtb_controller;

		$fields = $rtb_controller->settings->get_booking_form_fields();
		$form_outline = array();
		foreach ( $fields as $fieldset ) {
			if ( !isset( $fieldset['fields'] ) ) {
				continue;
			}
			$fieldset_outline = array();
			foreach ( $fieldset['fields'] as $field_name => $field ) {
				if ( $field_name === 'party' ) {
					$fieldset_outline[] = $field['callback'] . ' rtb-block-outline-field-party';
				} elseif ( $field_name === 'message' ) {
					$fieldset_outline[] = $field['callback'] . ' rtb-block-outline-field-message';
				} elseif ( $field_name === 'consent-statement' ) {
					$fieldset_outline[] = $field['callback'] . ' rtb-block-outline-field-consent-statement';
				} else {
					$fieldset_outline[] = $field['callback'];
				}
			}
			$form_outline[] = $fieldset_outline;
		}

		$locations_enabled = !!$rtb_controller->locations->post_type;

		if ($locations_enabled) {
			$locations = $rtb_controller->locations->get_location_options();
			$location_options = array( array( 'value' => 0, 'label' => __('Ask the user to select a location', 'restaurant-reservations' ) ) );
			foreach ( $locations as $id => $name ) {
				$location_options[] = array( 'value' => $id, 'label' => $name);
			}
		}

		wp_add_inline_script(
			'restaurant-reservations-blocks',
			sprintf(
				'var rtb_blocks = %s;',
				json_encode( array(
					'formOutline' => $form_outline,
					'locationsEnabled' => $locations_enabled,
					'locations' => $location_options,
				) )
			),
			'before'
		);
	}

	/**
	 * Render the booking form
	 *
	 * @param array $attributes The block attributes
	 * @return string
	 */
	public function render( $attributes ) {
		return rtb_print_booking_form( $attributes );
	}
}
} // endif
