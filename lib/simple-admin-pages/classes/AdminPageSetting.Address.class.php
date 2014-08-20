<?php

/**
 * Register, display and save a textarea field setting in the admin menu
 *
 * @since 2.0.a.5
 * @package Simple Admin Pages
 */

class sapAdminPageSettingAddress_2_0_a_7 extends sapAdminPageSetting_2_0_a_7 {

	/*
	 * Size of this textarea
	 *
	 * This is put directly into a css class [size]-text,
	 * and setting this to 'large' will link into WordPress's existing textarea
	 * style for full-width textareas.
	 */
	public $size = 'small';

	/**
	 * Scripts that must be loaded for this component
	 * @since 2.0.a.5
	 */
	public $scripts = array(
		'sap-address' => array(
			'path'			=> 'js/address.js',
			'dependencies'	=> array( 'jquery' ),
			'version'		=> '2.0.a.5',
			'footer'		=> true,
		),
	);

	public $sanitize_callback = 'sanitize_text_field';

	/**
	 * Escape the value to display it safely HTML textarea fields
	 * @since 2.0.a.5
	 */
	public function esc_value( $val ) {

		$escaped = array();
		$escaped['text'] = empty( $val['text'] ) ? '' : esc_textarea( $val['text'] );
		$escaped['lat'] = empty( $val['lat'] ) ? '' : esc_textarea( $val['lat'] );
		$escaped['lon'] = empty( $val['lon'] ) ? '' : esc_textarea( $val['lon'] );

		return $escaped;
	}

	/**
	 * Set the size of this textarea field
	 * @since 1.0
	 */
	public function set_size( $size ) {
		$this->size = esc_attr( $size );
	}

	/**
	 * Wrapper for the sanitization callback function.
	 *
	 * This just reduces code duplication for child classes that need a custom
	 * callback function.
	 * @since 2.0.a.5
	 */
	public function sanitize_callback_wrapper( $value ) {

		$sanitized = array();
		$sanitized['text'] = empty( $value['text'] ) ? '' : wp_kses_post( $value['text'] );
		$sanitized['lat'] = empty( $value['lat'] ) ? '' : sanitize_text_field( $value['lat'] );
		$sanitized['lon'] = empty( $value['lon'] ) ? '' : sanitize_text_field( $value['lon'] );

		return $sanitized;
	}

	/**
	 * Display this setting
	 * @since 2.0.a.5
	 */
	public function display_setting() {
	
		$strings = array(
			'sep-lat-lon'		=> _x( ', ', 'separates latitude and longitude', SAP_TEXTDOMAIN ),
			'no-setting'		=> __( 'No map coordinates set.', SAP_TEXTDOMAIN ),
			'retrieving'		=> __( 'Requesting new coordinates', SAP_TEXTDOMAIN ),
			'select'			=> __( 'Select a match below', SAP_TEXTDOMAIN ),
			'view'				=> __( 'View', SAP_TEXTDOMAIN ),
			'retrieve'			=> __( 'Retrieve map coordinates', SAP_TEXTDOMAIN ),
			'remove'			=> __( 'Remove map coordinates', SAP_TEXTDOMAIN ),
			'try_again'			=> __( 'Try again?', SAP_TEXTDOMAIN ),
			'result_error'		=> __( 'Error', SAP_TEXTDOMAIN ),
			'result_invalid'	=> __( 'Invalid request. Be sure to fill out the address field before retrieving coordinates.', SAP_TEXTDOMAIN ),
			'result_denied'		=> __( 'Request denied.', SAP_TEXTDOMAIN ),
			'result_limit'		=> __( 'Request denied because you are over your request quota.', SAP_TEXTDOMAIN ),
			'result_empty'		=> __( 'Nothing was found at that address', SAP_TEXTDOMAIN ),
		);

		wp_localize_script(
			'sap-address',
			'sap_address',
			array(
				'strings' => $strings,
			)
		);

		$this->display_description();
	
		?>

		<div class="sap-address" id="<?php echo $this->id; ?>">
			<textarea name="<?php echo $this->get_input_name(); ?>[text]" id="<?php echo $this->get_input_name(); ?>" class="<?php echo $this->size; ?>-text"<?php echo !empty( $this->placeholder ) ? ' placeholder="' . esc_attr( $this->placeholder ) . '"' : ''; ?>><?php echo $this->value['text']; ?></textarea>
			<p class="sap-map-coords-wrapper">
				<span class="dashicons dashicons-location-alt"></span>
				<span class="sap-map-coords">
				<?php if ( empty( $this->value['lat'] ) || empty( $this->value['lon'] ) ) : ?>
					<?php echo $strings['no-setting']; ?>
				<?php else : ?>
					<?php echo $this->value['lat'] . $strings['sep-lat-lon'] . $this->value['lon']; ?>
					<a href="//maps.google.com/maps?q=<?php echo esc_attr( $this->value['lat'] ) . ',' . esc_attr( $this->value['lon'] ); ?>" class="sap-view-coords" target="_blank"><?php echo $strings['view']; ?></a>
				<?php endif; ?>
				</span>
			</p>
			<p class="sap-coords-action-wrapper">
				<a href="#" class="sap-get-coords">
					<?php echo $strings['retrieve']; ?>
				</a>
				<?php _ex( ' | ', 'separator between admin action links in address component', SAP_TEXTDOMAIN ); ?>
				<a href="#" class="sap-remove-coords">
					<?php echo $strings['remove']; ?>
				</a>
			</p>
			<input type="hidden" class="lat" name="<?php echo $this->get_input_name(); ?>[lat]" value="<?php echo $this->value['lat']; ?>">
			<input type="hidden" class="lon" name="<?php echo $this->get_input_name(); ?>[lon]" value="<?php echo $this->value['lon']; ?>">
		</div>

		<?php
	}

}
