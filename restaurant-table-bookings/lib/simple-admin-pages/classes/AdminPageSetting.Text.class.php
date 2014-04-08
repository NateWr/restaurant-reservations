<?php

/**
 * Register, display and save a text field setting in the admin menu
 *
 * @since 1.0
 * @package Simple Admin Pages
 */

class sapAdminPageSettingText_2_0_a_1 extends sapAdminPageSetting_2_0_a_1 {

	public $sanitize_callback = 'sanitize_text_field';

	/**
	 * Display this setting
	 * @since 1.0
	 */
	public function display_setting() {
		?>

		<input name="<?php echo $this->get_input_name(); ?>" type="text" id="<?php echo $this->get_input_name(); ?>" value="<?php echo $this->value; ?>" class="regular-text" />

		<?php
		
		$this->display_description();
		
	}

}
