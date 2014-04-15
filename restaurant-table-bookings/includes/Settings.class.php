<?php
if ( !defined( 'ABSPATH' ) ) exit;

if ( !class_exists( 'rtbBooking' ) ) {
/**
 * Class to handle configurable settings for Restaurant Table Bookings
 *
 * @since 0.0.1
 */
class rtbSettings {

	public function __construct() {

		add_action( 'init', array( $this, 'load_settings_panel' ) );

	}

	/**
	 * Load the admin settings page
	 * @since 0.0.1
	 * @sa https://github.com/NateWr/simple-admin-pages
	 */
	public function load_settings_panel() {

		require_once( RTB_PLUGIN_DIR . '/lib/simple-admin-pages/simple-admin-pages.php' );
		$sap = sap_initialize_library(
			$args = array(
				'version'       => '2.0.a.1',
				'lib_url'       => RTB_PLUGIN_URL . '/lib/simple-admin-pages/',
			)
		);

		$sap->add_page(
			'submenu',
			array(
				'id'            => 'rtb-settings',
				'title'         => __( 'Settings', RTB_TEXTDOMAIN ),
				'menu_title'    => __( 'Settings', RTB_TEXTDOMAIN ),
				'parent_menu'	=> 'rtb-bookings',
				'description'   => '',
				'capability'    => 'manage_options', // @todo custom capability?
				'default_tab'   => 'general',
			)
		);

		$sap->add_section(
			'rtb-settings',
			array(
				'id'            => 'general',
				'title'         => __( 'General', RTB_TEXTDOMAIN ),
				'is_tab'		=> true,
			)
		);

		$sap->add_setting(
			'rtb-settings',
			'general',
			'post',
			array(
				'id'            => 'booking-page',
				'title'         => __( 'Booking Page', RTB_TEXTDOMAIN ),
				'description'   => __( 'Select the page on your site to display the booking form.', RTB_TEXTDOMAIN ), // @todo "Click here to create the page"
				'blank_option'	=> true,
				'args'			=> array(
					'post_type' 		=> 'page',
					'posts_per_page'	=> -1,
					'post_status'		=> 'publish',
				),
			)
		);

		$sap->add_setting(
			'rtb-settings',
			'general',
			'post',
			array(
				'id'            => 'confirmation-page',
				'title'         => __( 'Confirmation Page', RTB_TEXTDOMAIN ),
				'description'   => __( 'Select the page on your site to display the booking confirmation details.', RTB_TEXTDOMAIN ), // @todo "Click here to create the page"
				'blank_option'	=> true,
				'args'			=> array(
					'post_type' 		=> 'page',
					'posts_per_page'	=> -1,
					'post_status'		=> 'publish',
				),
			)
		);

		$sap->add_section(
			'rtb-settings',
			array(
				'id'            => 'schedule',
				'title'         => __( 'Booking Schedule', RTB_TEXTDOMAIN ),
				'is_tab'		=> true,
			)
		);

		$sap->add_setting(
			'rtb-settings',
			'schedule',
			'scheduler',
			array(
				'id'			=> 'schedule-open',
				'title'			=> __( 'Allow Bookings', RTB_TEXTDOMAIN ),
				'description'	=> __( 'Set up rules to define when visitors can request a booking on your site.', RTB_TEXTDOMAIN ),
				'weekdays'		=> array(
					'monday'		=> _x( 'Mo', 'Monday abbreviation', RTB_TEXTDOMAIN ),
					'tuesday'		=> _x( 'Tu', 'Tuesday abbreviation', RTB_TEXTDOMAIN ),
					'wednesday'		=> _x( 'We', 'Wednesday abbreviation', RTB_TEXTDOMAIN ),
					'thursday'		=> _x( 'Th', 'Thursday abbreviation', RTB_TEXTDOMAIN ),
					'friday'		=> _x( 'Fr', 'Friday abbreviation', RTB_TEXTDOMAIN ),
					'saturday'		=> _x( 'Sa', 'Saturday abbreviation', RTB_TEXTDOMAIN ),
					'sunday'		=> _x( 'Su', 'Sunday abbreviation', RTB_TEXTDOMAIN )
				),
				'weeks'			=> array(
					'first'		=> __( '1st', RTB_TEXTDOMAIN ),
					'second'	=> __( '2nd', RTB_TEXTDOMAIN ),
					'third'		=> __( '3rd', RTB_TEXTDOMAIN ),
					'fourth'	=> __( '4th', RTB_TEXTDOMAIN ),
					'last'		=> _x( 'last', 'Last week of the month', RTB_TEXTDOMAIN ),
				),
				'time_format'	=> _x( 'h:i A', 'Time format when selecting a time. See http://amsul.ca/pickadate.js/ for formatting options', RTB_TEXTDOMAIN ),
				'date_format'	=> _x( 'd mmmm, yyyy', 'Time format when selecting a time. See http://amsul.ca/pickadate.js/ for formatting options', RTB_TEXTDOMAIN ),
			)
		);

		$sap->add_setting(
			'rtb-settings',
			'schedule',
			'scheduler',
			array(
				'id'			=> 'schedule-closed',
				'title'			=> __( 'Forbid Bookings', RTB_TEXTDOMAIN ),
				'description'	=> __( 'Set up rules to define when visitors can <strong>not</strong> request a booking on your site.', RTB_TEXTDOMAIN ),
				'weekdays'		=> array(
					'monday'		=> _x( 'Mo', 'Monday abbreviation', RTB_TEXTDOMAIN ),
					'tuesday'		=> _x( 'Tu', 'Tuesday abbreviation', RTB_TEXTDOMAIN ),
					'wednesday'		=> _x( 'We', 'Wednesday abbreviation', RTB_TEXTDOMAIN ),
					'thursday'		=> _x( 'Th', 'Thursday abbreviation', RTB_TEXTDOMAIN ),
					'friday'		=> _x( 'Fr', 'Friday abbreviation', RTB_TEXTDOMAIN ),
					'saturday'		=> _x( 'Sa', 'Saturday abbreviation', RTB_TEXTDOMAIN ),
					'sunday'		=> _x( 'Su', 'Sunday abbreviation', RTB_TEXTDOMAIN )
				),
				'weeks'			=> array(
					'first'		=> __( '1st', RTB_TEXTDOMAIN ),
					'second'	=> __( '2nd', RTB_TEXTDOMAIN ),
					'third'		=> __( '3rd', RTB_TEXTDOMAIN ),
					'fourth'	=> __( '4th', RTB_TEXTDOMAIN ),
					'last'		=> _x( 'last', 'Last week of the month', RTB_TEXTDOMAIN ),
				),
				'time_format'	=> _x( 'h:i A', 'Time format when selecting a time. See http://amsul.ca/pickadate.js/ for formatting options', RTB_TEXTDOMAIN ),
				'date_format'	=> _x( 'd mmmm, yyyy', 'Time format when selecting a time. See http://amsul.ca/pickadate.js/ for formatting options', RTB_TEXTDOMAIN ),
			)
		);

		$sap->add_setting(
			'rtb-settings',
			'schedule',
			'select',
			array(
				'id'            => 'late-bookings',
				'title'         => __( 'Late Bookings', RTB_TEXTDOMAIN ),
				'description'   => __( 'Select how long in advance customers should have to make their booking.', RTB_TEXTDOMAIN ),
				'options'       => array(
					'15' => __( '15 minutes before booking', RTB_TEXTDOMAIN ),
					'30' => __( '30 minutes before booking', RTB_TEXTDOMAIN ),
					'45' => __( '45 minutes before booking', RTB_TEXTDOMAIN ),
					'60' => __( '1 hour before booking', RTB_TEXTDOMAIN ),
					'120' => __( '2 hours before booking', RTB_TEXTDOMAIN ),
					'240' => __( '4 hours before booking', RTB_TEXTDOMAIN ),
					'1440' => __( '1 day before booking', RTB_TEXTDOMAIN ),
				)
			)
		);

		$sap->add_section(
			'rtb-settings',
			array(
				'id'            => 'notifications',
				'title'         => __( 'Notifications', RTB_TEXTDOMAIN ),
				'is_tab'		=> true,
			)
		);

		$sap->add_setting(
			'rtb-settings',
			'notifications',
			'toggle',
			array(
				'id'			=> 'admin-email-option',
				'title'			=> __( 'Admin Notification', RTB_TEXTDOMAIN ),
				'label'			=> __( 'Send an email notification to an administrator when a new booking is requested.', RTB_TEXTDOMAIN )
			)
		);

		$sap->add_setting(
			'rtb-settings',
			'notifications',
			'text',
			array(
				'id'			=> 'admin-email-address',
				'title'			=> __( 'Admin Email Address', RTB_TEXTDOMAIN ),
				'description'	=> __( 'The recipient email address where admin notifications should be sent.', RTB_TEXTDOMAIN ),
				'placeholder'	=> get_option( 'admin_email' ),
			)
		);

		$sap->add_section(
			'rtb-settings',
			array(
				'id'            => 'notifications-templates',
				'title'         => __( 'Email Templates', RTB_TEXTDOMAIN ),
				'tab'			=> 'notifications',
			)
		);

		$sap->add_setting(
			'rtb-settings',
			'notifications-templates',
			'textarea',
			array(
				'id'			=> 'template-booking-user',
				'title'			=> __( 'Booking Request Received', RTB_TEXTDOMAIN ),
				'description'	=> __( 'Enter the email a user should receive when they make an initial booking request on your website.', RTB_TEXTDOMAIN ),
				'size'			=> 'large',
			)
		);

		$sap->add_setting(
			'rtb-settings',
			'notifications-templates',
			'textarea',
			array(
				'id'			=> 'template-confirmed-user',
				'title'			=> __( 'Booking Request Confirmed', RTB_TEXTDOMAIN ),
				'description'	=> __( 'Enter the email a user should receive when their booking has been confirmed.', RTB_TEXTDOMAIN ),
				'size'			=> 'large',
			)
		);

		$sap->add_setting(
			'rtb-settings',
			'notifications-templates',
			'textarea',
			array(
				'id'			=> 'template-rejected-user',
				'title'			=> __( 'Booking Request Rejected', RTB_TEXTDOMAIN ),
				'description'	=> __( 'Enter the email a user should receive when their booking has been rejected.', RTB_TEXTDOMAIN ),
				'size'			=> 'large',
			)
		);


		$sap = apply_filters( 'rtb_settings_page', $sap );

		$sap->add_admin_menus();

	}

}
} // endif;
