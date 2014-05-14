<?php
if ( !defined( 'ABSPATH' ) ) exit;

if ( !class_exists( 'rtbBooking' ) ) {
/**
 * Class to handle configurable settings for Restaurant Reservations
 *
 * @since 0.0.1
 */
class rtbSettings {

	/**
	 * Default values for settings
	 * @since 0.0.1
	 */
	public $defaults = array();

	/**
	 * Stored values for settings
	 * @since 0.0.1
	 */
	public $settings = array();
	
	/**
	 *
	 */
	public $supported_i8n = array(
		'ar'	=> 'ar',
		'bg_BG'	=> 'bg_BG',
		'bs_BA'	=> 'bs_BA',
		'ca_ES'	=> 'ca_ES',
		'cs_CZ'	=> 'cs_CZ',
		'da_DK'	=> 'da_DK',
		'de_DE'	=> 'de_DE',
		'el_GR'	=> 'el_GR',
		'es_ES'	=> 'es_ES',
		'et_EE'	=> 'et_EE',
		'eu_ES'	=> 'eu_ES',
		'fi_FI'	=> 'fi_FI',
		'fr_FR'	=> 'fr_FR',
		'gl_ES'	=> 'gl_ES',
		'he_IL'	=> 'he_IL',
		'hr_HR'	=> 'hr_HR',
		'hu_HU'	=> 'hu_HU',
		'id_ID'	=> 'id_ID',
		'is_IS'	=> 'is_IS',
		'it_IT'	=> 'it_IT',
		'ja_JP'	=> 'ja_JP',
		'ko_KR'	=> 'ko_KR',
		'nl_NL'	=> 'nl_NL',
		'no_NO'	=> 'no_NO',
		'pl_PL'	=> 'pl_PL',
		'pt_BR'	=> 'pt_BR',
		'pt_PT'	=> 'pt_PT',
		'ro_RO'	=> 'ro_RO',
		'ru_RU'	=> 'ru_RU',
		'sk_SK'	=> 'sk_SK',
		'sl_SI'	=> 'sl_SI',
		'sv_SE'	=> 'sv_SE',
		'th_TH'	=> 'th_TH',
		'tr_TR'	=> 'tr_TR',
		'uk_UA'	=> 'uk_UA',
		'zh_CN'	=> 'zh_CN',
		'zh_TW'	=> 'zh_TW',
	);

	public function __construct() {

		add_action( 'init', array( $this, 'set_defaults' ) );

		add_action( 'init', array( $this, 'load_settings_panel' ) );

	}

	/**
	 * Load the plugin's default settings
	 * @since 0.0.1
	 */
	public function set_defaults() {

		$this->defaults = array(

			'success-message'				=> _x( 'Thanks, your booking request is waiting to be confirmed. Updates will be sent to the email address you provided.', RTB_TEXTDOMAIN ),
			'date-format'					=> _x( 'mmmm d, yyyy', 'Default date format for display. Must match formatting rules at http://amsul.ca/pickadate.js/date.htm#formatting-rules', RTB_TEXTDOMAIN ),
			'time-format'					=> _x( 'h:i A', 'Default time format for display. Must match formatting rules at http://amsul.ca/pickadate.js/time.htm#formats', RTB_TEXTDOMAIN ),

			// Email address where admin notifications should be sent
			'admin-email-address'			=> get_option( 'admin_email' ),

			// Name and email address which should appear in the Reply-To section of notification emails
			'reply-to-name'					=> get_bloginfo( 'name' ),
			'reply-to-address'				=> get_option( 'admin_email' ),

			// Email template sent to an admin when a new booking request is made
			'subject-booking-admin'			=> _x( 'New Booking Request', 'Default email subject for admin notifications of new bookings', RTB_TEXTDOMAIN ),
			'template-booking-admin'		=> _x( 'A new booking request has been made at {site_name}:

{user_name}
{party} people
{date}

{bookings_link}
{confirm_link}
{close_link}

&nbsp;

<em>This message was sent by {site_link} on {current_time}.</em>',
				'Default email sent to the admin when a new booking request is made. The tags in {brackets} will be replaced by the appropriate content and should be left in place. HTML is allowed, but be aware that many email clients do not handle HTML very well.',
				RTB_TEXTDOMAIN
			),

			// Email template sent to a user when a new booking request is made
			'subject-booking-user'			=> sprintf( _x( 'Your booking at %s is pending', 'Default email subject sent to user when they request a booking. %s will be replaced by the website name', RTB_TEXTDOMAIN ), get_bloginfo( 'name' ) ),
			'template-booking-user'			=> _x( 'Thanks {user_name},

Your booking request is <strong>waiting to be confirmed</strong>.

Give us a few moments to make sure that we\'ve got space for you. You will receive another email from us soon. If this request was made outside of our normal working hours, we may not be able to confirm it until we\'re open again.

<strong>Your request details:</strong>
{user_name}
{party} people
{date}

&nbsp;

<em>This message was sent by {site_link} on {current_time}.</em>',
				'Default email sent to users when they make a new booking request. The tags in {brackets} will be replaced by the appropriate content and should be left in place. HTML is allowed, but be aware that many email clients do not handle HTML very well.',
				RTB_TEXTDOMAIN
			),

			// Email template sent to a user when a booking request is confirmed
			'subject-confirmed-user'		=> sprintf( _x( 'Your booking at %s is confirmed', 'Default email subject sent to user when their booking is confirmed. %s will be replaced by the website name', RTB_TEXTDOMAIN ), get_bloginfo( 'name' ) ),
			'template-confirmed-user'		=> _x( 'Hi {user_name},

Your booking request has been <strong>confirmed</strong>. We look forward to seeing you soon.

<strong>Your booking:</strong>
{user_name}
{party} people
{date}

&nbsp;

<em>This message was sent by {site_link} on {current_time}.</em>',
				'Default email sent to users when they make a new booking request. The tags in {brackets} will be replaced by the appropriate content and should be left in place. HTML is allowed, but be aware that many email clients do not handle HTML very well.',
				RTB_TEXTDOMAIN
			),

			// Email template sent to a user when a booking request is rejected
			'subject-rejected-user'			=> sprintf( _x( 'Your booking at %s was not accepted', 'Default email subject sent to user when their booking is rejected. %s will be replaced by the website name', RTB_TEXTDOMAIN ), get_bloginfo( 'name' ) ),
			'template-rejected-user'		=> _x( 'Hi {user_name},

Sorry, we could not accomodate your booking request. We\'re full or not open at the time you requested:

{user_name}
{party} people
{date}

&nbsp;

<em>This message was sent by {site_link} on {current_time}.</em>',
				'Default email sent to users when they make a new booking request. The tags in {brackets} will be replaced by the appropriate content and should be left in place. HTML is allowed, but be aware that many email clients do not handle HTML very well.',
				RTB_TEXTDOMAIN
			),
		);
		
		$i8n = get_bloginfo( 'language' );
		if ( array_key_exists( $i8n, $this->supported_i8n ) ) {
			$this->defaults['i8n'] = $i8n;
		}

		$this->defaults = apply_filters( 'rtb_defaults', $this->defaults );
	}

	/**
	 * Get a setting's value or fallback to a default if one exists
	 * @since 0.0.1
	 */
	public function get_setting( $setting ) {

		if ( empty( $this->settings ) ) {
			$this->settings = get_option( 'rtb-settings' );
		}

		if ( !empty( $this->settings[ $setting ] ) ) {
			return $this->settings[ $setting ];
		}

		if ( !empty( $this->defaults[ $setting ] ) ) {
			return $this->defaults[ $setting ];
		}

		return null;
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
				'version'       => '2.0.a.5',
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
				'capability'    => 'manage_options',
				'default_tab'   => 'rtb-general',
			)
		);

		$sap->add_section(
			'rtb-settings',
			array(
				'id'            => 'rtb-general',
				'title'         => __( 'General', RTB_TEXTDOMAIN ),
				'is_tab'		=> true,
			)
		);

		$sap->add_setting(
			'rtb-settings',
			'rtb-general',
			'post',
			array(
				'id'            => 'booking-page',
				'title'         => __( 'Booking Page', RTB_TEXTDOMAIN ),
				'description'   => __( 'Select a page on your site to automatically display the booking form and confirmation message.', RTB_TEXTDOMAIN ),
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
			'rtb-general',
			'textarea',
			array(
				'id'			=> 'success-message',
				'title'			=> __( 'Success Message', RTB_TEXTDOMAIN ),
				'description'	=> __( 'Enter the message to display when a booking request is made.', RTB_TEXTDOMAIN ),
				'placeholder'	=> $this->defaults['success-message'],
			)
		);

		$sap->add_setting(
			'rtb-settings',
			'rtb-general',
			'text',
			array(
				'id'            => 'date-format',
				'title'         => __( 'Date Format', RTB_TEXTDOMAIN ),
				'description'   => __( 'Define how the date should appear after it has been selected. <a href="http://amsul.ca/pickadate.js/date.htm#formatting-rules">Formatting rules</a>', RTB_TEXTDOMAIN ),
				'placeholder'	=> $this->defaults['date-format'],
			)
		);

		$sap->add_setting(
			'rtb-settings',
			'rtb-general',
			'text',
			array(
				'id'            => 'time-format',
				'title'         => __( 'Time Format', RTB_TEXTDOMAIN ),
				'description'   => __( 'Define how the time should appear after it has been selected. <a href="http://amsul.ca/pickadate.js/time.htm#formatting-rules">Formatting rules</a>', RTB_TEXTDOMAIN ),
				'placeholder'	=> $this->defaults['time-format'],
			)
		);

		// Add i8n setting for pickadate if the frontend assets are to be loaded
		if ( RTB_LOAD_FRONTEND_ASSETS ) {
			$sap->add_setting(
				'rtb-settings',
				'rtb-general',
				'select',
				array(
					'id'            => 'i8n',
					'title'         => __( 'Language', RTB_TEXTDOMAIN ),
					'description'   => __( 'Select a language to use for the booking form datepicker if it is different than your WordPress language setting.', RTB_TEXTDOMAIN ),
					'options'		=> $this->supported_i8n,
				)
			);
		}

		$sap->add_section(
			'rtb-settings',
			array(
				'id'            => 'rtb-schedule',
				'title'         => __( 'Booking Schedule', RTB_TEXTDOMAIN ),
				'is_tab'		=> true,
			)
		);

		$sap->add_setting(
			'rtb-settings',
			'rtb-schedule',
			'scheduler',
			array(
				'id'			=> 'schedule-open',
				'title'			=> __( 'Schedule', RTB_TEXTDOMAIN ),
				'description'	=> __( 'Define the weekly schedule during which you accept bookings.', RTB_TEXTDOMAIN ),
				'weekdays'		=> array(
					'monday'		=> _x( 'Mo', 'Monday abbreviation', RTB_TEXTDOMAIN ),
					'tuesday'		=> _x( 'Tu', 'Tuesday abbreviation', RTB_TEXTDOMAIN ),
					'wednesday'		=> _x( 'We', 'Wednesday abbreviation', RTB_TEXTDOMAIN ),
					'thursday'		=> _x( 'Th', 'Thursday abbreviation', RTB_TEXTDOMAIN ),
					'friday'		=> _x( 'Fr', 'Friday abbreviation', RTB_TEXTDOMAIN ),
					'saturday'		=> _x( 'Sa', 'Saturday abbreviation', RTB_TEXTDOMAIN ),
					'sunday'		=> _x( 'Su', 'Sunday abbreviation', RTB_TEXTDOMAIN )
				),
				'time_format'	=> $this->get_setting( 'time-format' ),
				'date_format'	=> $this->get_setting( 'date-format' ),
				'disable_weeks'	=> true,
				'disable_date'	=> true,
			)
		);

		$sap->add_setting(
			'rtb-settings',
			'rtb-schedule',
			'scheduler',
			array(
				'id'				=> 'schedule-closed',
				'title'				=> __( 'Exceptions', RTB_TEXTDOMAIN ),
				'description'		=> __( "Define special opening hours for holidays, events or other needs. Leave the time empty if you're closed all day.", RTB_TEXTDOMAIN ),
				'time_format'		=> $this->get_setting( 'time-format' ),
				'date_format'		=> $this->get_setting( 'date-format' ),
				'disable_weekdays'	=> true,
				'disable_weeks'		=> true,
				'instance_schedule_summaries' => array(
					'all_day'	=> _x( 'Closed all day', 'Brief description of a scheduling exception when no times are set', SAP_TEXTDOMAIN ),
				),
			)
		);

		$sap->add_setting(
			'rtb-settings',
			'rtb-schedule',
			'select',
			array(
				'id'            => 'early-bookings',
				'title'         => __( 'Early Bookings', RTB_TEXTDOMAIN ),
				'description'   => __( 'Select how early customers can make their booking.', RTB_TEXTDOMAIN ),
				'blank_option'	=> false,
				'options'       => array(
					''		=> __( 'Any time', RTB_TEXTDOMAIN ),
					'1' 	=> __( 'Up to 1 day in advance', RTB_TEXTDOMAIN ),
					'7' 	=> __( 'Up to 1 week in advance', RTB_TEXTDOMAIN ),
					'14' 	=> __( 'Up to 2 weeks in advance', RTB_TEXTDOMAIN ),
					'30' 	=> __( 'Up to 30 days in advance', RTB_TEXTDOMAIN ),
					'90' 	=> __( 'Up to 90 days in advance', RTB_TEXTDOMAIN ),
				)
			)
		);

		$sap->add_setting(
			'rtb-settings',
			'rtb-schedule',
			'select',
			array(
				'id'            => 'late-bookings',
				'title'         => __( 'Late Bookings', RTB_TEXTDOMAIN ),
				'description'   => __( 'Select how late customers can make their booking.', RTB_TEXTDOMAIN ),
				'blank_option'	=> false,
				'options'       => array(
					'' 		=> __( 'Up to the last minute', RTB_TEXTDOMAIN ),
					'15' 	=> __( 'At least 15 minutes in advance', RTB_TEXTDOMAIN ),
					'30' 	=> __( 'At least 30 minutes in advance', RTB_TEXTDOMAIN ),
					'45' 	=> __( 'At least 45 minutes in advance', RTB_TEXTDOMAIN ),
					'60' 	=> __( 'At least 1 hour in advance', RTB_TEXTDOMAIN ),
					'240' 	=> __( 'At least 4 hours in advance', RTB_TEXTDOMAIN ),
					'1440' 	=> __( 'At least 1 day in advance', RTB_TEXTDOMAIN ),
				)
			)
		);

		$sap->add_section(
			'rtb-settings',
			array(
				'id'            => 'rtb-notifications',
				'title'         => __( 'Notifications', RTB_TEXTDOMAIN ),
				'is_tab'		=> true,
			)
		);

		$sap->add_setting(
			'rtb-settings',
			'rtb-notifications',
			'text',
			array(
				'id'			=> 'reply-to-name',
				'title'			=> __( 'Reply-To Name', RTB_TEXTDOMAIN ),
				'description'	=> __( 'The name which should appear in the Reply-To field of a notification email', RTB_TEXTDOMAIN ),
				'placeholder'	=> $this->defaults['reply-to-name'],
			)
		);

		$sap->add_setting(
			'rtb-settings',
			'rtb-notifications',
			'text',
			array(
				'id'			=> 'reply-to-address',
				'title'			=> __( 'Reply-To Email Address', RTB_TEXTDOMAIN ),
				'description'	=> __( 'The email address which should appear in the Reply-To field of a notification email.', RTB_TEXTDOMAIN ),
				'placeholder'	=> $this->defaults['reply-to-address'],
			)
		);

		$sap->add_setting(
			'rtb-settings',
			'rtb-notifications',
			'toggle',
			array(
				'id'			=> 'admin-email-option',
				'title'			=> __( 'Admin Notification', RTB_TEXTDOMAIN ),
				'label'			=> __( 'Send an email notification to an administrator when a new booking is requested.', RTB_TEXTDOMAIN )
			)
		);

		$sap->add_setting(
			'rtb-settings',
			'rtb-notifications',
			'text',
			array(
				'id'			=> 'admin-email-address',
				'title'			=> __( 'Admin Email Address', RTB_TEXTDOMAIN ),
				'description'	=> __( 'The email address where admin notifications should be sent.', RTB_TEXTDOMAIN ),
				'placeholder'	=> $this->defaults['admin-email-address'],
			)
		);

		$sap->add_section(
			'rtb-settings',
			array(
				'id'            => 'rtb-notifications-templates',
				'title'         => __( 'Email Templates', RTB_TEXTDOMAIN ),
				'tab'			=> 'rtb-notifications',
				'description'	=> 'Adjust the messages that are emailed to users and admins during the booking process.',
			)
		);

		// @todo this should be generated automatically from an array of tags/descriptions somewhere, so that addons
		//	can easily add/edit without conflicting with each other.
		$sap->add_setting(
			'rtb-settings',
			'rtb-notifications-templates',
			'html',
			array(
				'id'			=> 'template-tags-description',
				'title'			=> __( 'Template Tags', RTB_TEXTDOMAIN ),
				'html'			=> '
					<p class="description">' . __( 'Use the following tags to automatically add booking information to the emails.', RTB_TEXTDOMAIN ) . '</p>
					<div class="rtb-template-tags-box">
						<strong>{user_name}</strong> ' . __( 'Name of the user who made the booking', RTB_TEXTDOMAIN ) . '
					</div>
					<div class="rtb-template-tags-box">
						<strong>{party}</strong> ' . __( 'Number of people booked', RTB_TEXTDOMAIN ) . '
					</div>
					<div class="rtb-template-tags-box">
						<strong>{date}</strong> ' . __( 'Date and time of the booking', RTB_TEXTDOMAIN ) . '
					</div>
					<div class="rtb-template-tags-box">
						<strong>{bookings_link}</strong> ' . __( 'A link to the admin panel showing pending bookings', RTB_TEXTDOMAIN ) . '
					</div>
					<div class="rtb-template-tags-box">
						<strong>{confirm_link}</strong> ' . __( 'A link to confirm this booking. Only include this in admin notifications', RTB_TEXTDOMAIN ) . '
					</div>
					<div class="rtb-template-tags-box">
						<strong>{close_link}</strong> ' . __( 'A link to reject this booking. Only include this in admin notifications', RTB_TEXTDOMAIN ) . '
					</div>
					<div class="rtb-template-tags-box">
						<strong>{site_name}</strong> ' . __( 'The name of this website', RTB_TEXTDOMAIN ) . '
					</div>
					<div class="rtb-template-tags-box">
						<strong>{site_link}</strong> ' . __( 'A link to this website', RTB_TEXTDOMAIN ) . '
					</div>
					<div class="rtb-template-tags-box">
						<strong>{current_time}</strong> ' . __( 'Current date and time', RTB_TEXTDOMAIN ) . '
					</div>',
			)
		);

		$sap->add_setting(
			'rtb-settings',
			'rtb-notifications-templates',
			'text',
			array(
				'id'			=> 'subject-booking-admin',
				'title'			=> __( 'Admin Notification Subject', RTB_TEXTDOMAIN ),
				'description'	=> __( 'The email subject for admin notifications.', RTB_TEXTDOMAIN ),
				'placeholder'	=> $this->defaults['subject-booking-admin'],
			)
		);

		$sap->add_setting(
			'rtb-settings',
			'rtb-notifications-templates',
			'editor',
			array(
				'id'			=> 'template-booking-admin',
				'title'			=> __( 'Admin Notification Email', RTB_TEXTDOMAIN ),
				'description'	=> __( 'Enter the email an admin should receive when an initial booking request is made.', RTB_TEXTDOMAIN ),
				'default'		=> $this->defaults['template-booking-admin'],
			)
		);

		$sap->add_setting(
			'rtb-settings',
			'rtb-notifications-templates',
			'text',
			array(
				'id'			=> 'subject-booking-user',
				'title'			=> __( 'New Request Email Subject', RTB_TEXTDOMAIN ),
				'description'	=> __( 'The email subject a user should receive when they make an initial booking request.', RTB_TEXTDOMAIN ),
				'placeholder'	=> $this->defaults['subject-booking-user'],
			)
		);

		$sap->add_setting(
			'rtb-settings',
			'rtb-notifications-templates',
			'editor',
			array(
				'id'			=> 'template-booking-user',
				'title'			=> __( 'New Request Email', RTB_TEXTDOMAIN ),
				'description'	=> __( 'Enter the email a user should receive when they make an initial booking request.', RTB_TEXTDOMAIN ),
				'default'		=> $this->defaults['template-booking-user'],
			)
		);

		$sap->add_setting(
			'rtb-settings',
			'rtb-notifications-templates',
			'text',
			array(
				'id'			=> 'subject-confirmed-user',
				'title'			=> __( 'Confirmed Email Subject', RTB_TEXTDOMAIN ),
				'description'	=> __( 'The email subject a user should receive when their booking has been confirmed.', RTB_TEXTDOMAIN ),
				'placeholder'	=> $this->defaults['subject-confirmed-user'],
			)
		);

		$sap->add_setting(
			'rtb-settings',
			'rtb-notifications-templates',
			'editor',
			array(
				'id'			=> 'template-confirmed-user',
				'title'			=> __( 'Confirmed Email', RTB_TEXTDOMAIN ),
				'description'	=> __( 'Enter the email a user should receive when their booking has been confirmed.', RTB_TEXTDOMAIN ),
				'default'		=> $this->defaults['template-confirmed-user'],
			)
		);

		$sap->add_setting(
			'rtb-settings',
			'rtb-notifications-templates',
			'text',
			array(
				'id'			=> 'subject-rejected-user',
				'title'			=> __( 'Rejected Email Subject', RTB_TEXTDOMAIN ),
				'description'	=> __( 'The email subject a user should receive when their booking has been rejected.', RTB_TEXTDOMAIN ),
				'placeholder'	=> $this->defaults['subject-rejected-user'],
			)
		);

		$sap->add_setting(
			'rtb-settings',
			'rtb-notifications-templates',
			'editor',
			array(
				'id'			=> 'template-rejected-user',
				'title'			=> __( 'Rejected Email', RTB_TEXTDOMAIN ),
				'description'	=> __( 'Enter the email a user should receive when their booking has been rejected.', RTB_TEXTDOMAIN ),
				'default'		=> $this->defaults['template-rejected-user'],
			)
		);

		$sap = apply_filters( 'rtb_settings_page', $sap );

		$sap->add_admin_menus();

	}

}
} // endif;
