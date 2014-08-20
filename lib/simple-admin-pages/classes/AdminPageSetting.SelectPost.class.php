<?php

/**
 * Register, display and save a selection with a drop-down list of any post type
 *
 * This setting accepts the following arguments in its constructor function.
 *
 * $args = array(
 *		'id'			=> 'setting_id', 	// Unique id
 *		'title'			=> 'My Setting', 	// Title or label for the setting
 *		'description'	=> 'Description', 	// Help text description
 *		'blank_option'	=> true, 			// Whether or not to show a blank option
 *		'args'			=> array();			// Arguments to pass to WordPress's get_post() function
 * );
 *
 * @since 1.0
 * @package Simple Admin Pages
 */

class sapAdminPageSettingSelectPost_2_0_a_7 extends sapAdminPageSetting_2_0_a_7 {

	public $sanitize_callback = 'intval';

	// Whether or not to display a blank option
	public $blank_option = true;

	/**
	 * An array of arguments accepted by get_posts().
	 * See: http://codex.wordpress.org/Template_Tags/get_posts
	 */
	public $args = array();

	/**
	 * Display this setting
	 * @since 1.0
	 */
	public function display_setting() {

		$posts = get_posts( $this->args );

		?>

			<select name="<?php echo $this->get_input_name(); ?>" id="<?php echo $this->get_input_name(); ?>">

				<?php if ( $this->blank_option === true ) : ?>
					<option></option>
				<?php endif; ?>

				<?php foreach ( $posts as $post  ) : ?>
					<option value="<?php echo esc_attr( $post->ID ); ?>"<?php if( $this->value == $post->ID ) : ?> selected="selected"<?php endif; ?>><?php echo esc_html( $post->post_title ); ?></option>
				<?php endforeach; ?>

			</select>

		<?php

		$this->display_description();

	}

}
