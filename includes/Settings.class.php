<?php
if ( !defined( 'ABSPATH' ) ) exit;

if ( !class_exists( 'rtbSettings' ) ) {
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
	 * Languages supported by the pickadate library
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
		'fa_IR'	=> 'fa_IR',
		'fi_FI'	=> 'fi_FI',
		'fr_FR'	=> 'fr_FR',
		'gl_ES'	=> 'gl_ES',
		'he_IL'	=> 'he_IL',
		'hi_IN'	=> 'hi_IN',
		'hr_HR'	=> 'hr_HR',
		'hu_HU'	=> 'hu_HU',
		'id_ID'	=> 'id_ID',
		'is_IS'	=> 'is_IS',
		'it_IT'	=> 'it_IT',
		'ja_JP'	=> 'ja_JP',
		'ko_KR'	=> 'ko_KR',
		'lt_LT'	=> 'lt_LT',
		'lv_LV'	=> 'lv_LV',
		'nb_NO'	=> 'nb_NO',
		'ne_NP'	=> 'ne_NP',
		'nl_NL'	=> 'nl_NL',
		'no_NO'	=> 'no_NO', // Old norwegian translation kept for backwards compatibility
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

		// Order schedule exceptions and remove past exceptions
		add_filter( 'sanitize_option_rtb-settings', array( $this, 'clean_schedule_exceptions' ), 100 );

	}

	/**
	 * Load the plugin's default settings
	 * @since 0.0.1
	 */
	public function set_defaults() {

		$this->defaults = array(

			'success-message'				=> _x( 'Thanks, your booking request is waiting to be confirmed. Updates will be sent to the email address you provided.', 'restaurant-reservations' ),
			'date-format'					=> _x( 'mmmm d, yyyy', 'Default date format for display. Must match formatting rules at http://amsul.ca/pickadate.js/date/#formats', 'restaurant-reservations' ),
			'time-format'					=> _x( 'h:i A', 'Default time format for display. Must match formatting rules at http://amsul.ca/pickadate.js/time/#formats', 'restaurant-reservations' ),
			'time-interval'					=> _x( '30', 'Default interval in minutes when selecting a time.', 'restaurant-reservations' ),

			// Email address where admin notifications should be sent
			'admin-email-address'			=> get_option( 'admin_email' ),

			// Name and email address which should appear in the Reply-To section of notification emails
			'reply-to-name'					=> get_bloginfo( 'name' ),
			'reply-to-address'				=> get_option( 'admin_email' ),

			// Email template sent to an admin when a new booking request is made
			'subject-booking-admin'			=> _x( 'New Booking Request', 'Default email subject for admin notifications of new bookings', 'restaurant-reservations' ),
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
				'restaurant-reservations'
			),

			// Email template sent to a user when a new booking request is made
			'subject-booking-user'			=> sprintf( _x( 'Your booking at %s is pending', 'Default email subject sent to user when they request a booking. %s will be replaced by the website name', 'restaurant-reservations' ), get_bloginfo( 'name' ) ),
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
				'restaurant-reservations'
			),

			// Email template sent to a user when a booking request is confirmed
			'subject-confirmed-user'		=> sprintf( _x( 'Your booking at %s is confirmed', 'Default email subject sent to user when their booking is confirmed. %s will be replaced by the website name', 'restaurant-reservations' ), get_bloginfo( 'name' ) ),
			'template-confirmed-user'		=> _x( 'Hi {user_name},

Your booking request has been <strong>confirmed</strong>. We look forward to seeing you soon.

<strong>Your booking:</strong>
{user_name}
{party} people
{date}

&nbsp;

<em>This message was sent by {site_link} on {current_time}.</em>',
				'Default email sent to users when they make a new booking request. The tags in {brackets} will be replaced by the appropriate content and should be left in place. HTML is allowed, but be aware that many email clients do not handle HTML very well.',
				'restaurant-reservations'
			),

			// Email template sent to a user when a booking request is rejected
			'subject-rejected-user'			=> sprintf( _x( 'Your booking at %s was not accepted', 'Default email subject sent to user when their booking is rejected. %s will be replaced by the website name', 'restaurant-reservations' ), get_bloginfo( 'name' ) ),
			'template-rejected-user'		=> _x( 'Hi {user_name},

Sorry, we could not accomodate your booking request. We\'re full or not open at the time you requested:

{user_name}
{party} people
{date}

&nbsp;

<em>This message was sent by {site_link} on {current_time}.</em>',
				'Default email sent to users when they make a new booking request. The tags in {brackets} will be replaced by the appropriate content and should be left in place. HTML is allowed, but be aware that many email clients do not handle HTML very well.',
				'restaurant-reservations'
			),

			// Email sent to a user with a custom update notice from the admin
			'subject-admin-notice'			=> sprintf( _x( 'Update regarding your booking at %s', 'Default email subject sent to users when the admin sends a custom notice email from the bookings panel.', 'restaurant-reservations' ), get_bloginfo( 'name' ) ),
		);

		$i8n = str_replace( '-', '_', get_bloginfo( 'language' ) );
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
			return apply_filters( 'rtb-setting-' . $setting, $this->settings[ $setting ] );
		}

		if ( !empty( $this->defaults[ $setting ] ) ) {
			return apply_filters( 'rtb-setting-' . $setting, $this->defaults[ $setting ] );
		}

		return apply_filters( 'rtb-setting-' . $setting, null );
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
				'version'       => '2.0',
				'lib_url'       => RTB_PLUGIN_URL . '/lib/simple-admin-pages/',
			)
		);

		$sap->add_page(
			'submenu',
			array(
				'id'            => 'rtb-settings',
				'title'         => __( 'Settings', 'restaurant-reservations' ),
				'menu_title'    => __( 'Settings', 'restaurant-reservations' ),
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
				'title'         => __( 'General', 'restaurant-reservations' ),
				'is_tab'		=> true,
			)
		);

		$sap->add_setting(
			'rtb-settings',
			'rtb-general',
			'post',
			array(
				'id'            => 'booking-page',
				'title'         => __( 'Booking Page', 'restaurant-reservations' ),
				'description'   => __( 'Select a page on your site to automatically display the booking form and confirmation message.', 'restaurant-reservations' ),
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
			'select',
			array(
				'id'            => 'party-size-min',
				'title'         => __( 'Min Party Size', 'restaurant-reservations' ),
				'description'   => __( 'Set a minimum allowed party size for bookings.', 'restaurant-reservations' ),
				'blank_option'	=> false,
				'options'       => $this->get_party_size_setting_options( false ),
			)
		);

		$sap->add_setting(
			'rtb-settings',
			'rtb-general',
			'select',
			array(
				'id'            => 'party-size',
				'title'         => __( 'Max Party Size', 'restaurant-reservations' ),
				'description'   => __( 'Set a maximum allowed party size for bookings.', 'restaurant-reservations' ),
				'blank_option'	=> false,
				'options'       => $this->get_party_size_setting_options(),
			)
		);

		$sap->add_setting(
			'rtb-settings',
			'rtb-general',
			'select',
			array(
				'id'            => 'require-phone',
				'title'         => __( 'Require Phone', 'restaurant-reservations' ),
				'description'   => __( "Don't accept booking requests without a phone number.", 'restaurant-reservations' ),
				'blank_option'	=> false,
				'options'       => array(
					'' => __( 'No', 'restaurant-reservations' ),
					'1' => __( 'Yes', 'restaurant-reservations' ),
				),
			)
		);

		$sap->add_setting(
			'rtb-settings',
			'rtb-general',
			'textarea',
			array(
				'id'			=> 'success-message',
				'title'			=> __( 'Success Message', 'restaurant-reservations' ),
				'description'	=> __( 'Enter the message to display when a booking request is made.', 'restaurant-reservations' ),
				'placeholder'	=> $this->defaults['success-message'],
			)
		);

		$sap->add_setting(
			'rtb-settings',
			'rtb-general',
			'text',
			array(
				'id'            => 'date-format',
				'title'         => __( 'Date Format', 'restaurant-reservations' ),
				'description'   => sprintf( __( 'Define how the date is formatted on the booking form. %sFormatting rules%s. This only changes the format on the booking form. To change the date format in notification messages, modify your general %sWordPress Settings%s.', 'restaurant-reservations' ), '<a href="http://amsul.ca/pickadate.js/date/#formats">', '</a>', '<a href="' . admin_url( 'options-general.php' ) . '">', '</a>' ),
				'placeholder'	=> $this->defaults['date-format'],
			)
		);

		$sap->add_setting(
			'rtb-settings',
			'rtb-general',
			'text',
			array(
				'id'            => 'time-format',
				'title'         => __( 'Time Format', 'restaurant-reservations' ),
				'description'   => sprintf( __( 'Define how the time is formatted on the booking form. %sFormatting rules%s. This only changes the format on the booking form. To change the time format in notification messages, modify your general %sWordPress Settings%s.', 'restaurant-reservations' ), '<a href="http://amsul.ca/pickadate.js/time/#formats">', '</a>', '<a href="' . admin_url( 'options-general.php' ) . '">', '</a>' ),
				'placeholder'	=> $this->defaults['time-format'],
			)
		);

		// Add i8n setting for pickadate if the frontend assets are to be loaded
		if ( apply_filters( 'rtb-load-frontend-assets', true ) ) {
			$sap->add_setting(
				'rtb-settings',
				'rtb-general',
				'select',
				array(
					'id'            => 'i8n',
					'title'         => __( 'Language', 'restaurant-reservations' ),
					'description'   => __( 'Select a language to use for the booking form datepicker if it is different than your WordPress language setting.', 'restaurant-reservations' ),
					'options'		=> $this->supported_i8n,
				)
			);
		}

		$sap->add_setting(
			'rtb-settings',
			'rtb-general',
			'textarea',
			array(
				'id'			=> 'ban-emails',
				'title'			=> __( 'Banned Email Addresses', 'restaurant-reservations' ),
				'description'	=> __( 'You can block bookings from specific email addresses. Enter each email address on a separate line.', 'restaurant-reservations' ),
			)
		);

		$sap->add_setting(
			'rtb-settings',
			'rtb-general',
			'textarea',
			array(
				'id'			=> 'ban-ips',
				'title'			=> __( 'Banned IP Addresses', 'restaurant-reservations' ),
				'description'	=> __( 'You can block bookings from specific IP addresses. Enter each IP address on a separate line. Be aware that many internet providers rotate their IP address assignments, so an IP address may accidentally refer to a different user. Also, if you block an IP address used by a public connection, such as cafe WIFI, a public library, or a university network, you may inadvertantly block several people.', 'restaurant-reservations' ),
			)
		);

		$sap->add_section(
			'rtb-settings',
			array(
				'id'            => 'rtb-schedule',
				'title'         => __( 'Booking Schedule', 'restaurant-reservations' ),
				'is_tab'		=> true,
			)
		);

		// Translateable strings for scheduler components
		$scheduler_strings = array(
			'add_rule'			=> __( 'Add new scheduling rule', 'restaurant-reservations' ),
			'weekly'			=> _x( 'Weekly', 'Format of a scheduling rule', 'restaurant-reservations' ),
			'monthly'			=> _x( 'Monthly', 'Format of a scheduling rule', 'restaurant-reservations' ),
			'date'				=> _x( 'Date', 'Format of a scheduling rule', 'restaurant-reservations' ),
			'weekdays'			=> _x( 'Days of the week', 'Label for selecting days of the week in a scheduling rule', 'restaurant-reservations' ),
			'month_weeks'		=> _x( 'Weeks of the month', 'Label for selecting weeks of the month in a scheduling rule', 'restaurant-reservations' ),
			'date_label'		=> _x( 'Date', 'Label to select a date for a scheduling rule', 'restaurant-reservations' ),
			'time_label'		=> _x( 'Time', 'Label to select a time slot for a scheduling rule', 'restaurant-reservations' ),
			'allday'			=> _x( 'All day', 'Label to set a scheduling rule to last all day', 'restaurant-reservations' ),
			'start'				=> _x( 'Start', 'Label for the starting time of a scheduling rule', 'restaurant-reservations' ),
			'end'				=> _x( 'End', 'Label for the ending time of a scheduling rule', 'restaurant-reservations' ),
			'set_time_prompt'	=> _x( 'All day long. Want to %sset a time slot%s?', 'Prompt displayed when a scheduling rule is set without any time restrictions', 'restaurant-reservations' ),
			'toggle'			=> _x( 'Open and close this rule', 'Toggle a scheduling rule open and closed', 'restaurant-reservations' ),
			'delete'			=> _x( 'Delete rule', 'Delete a scheduling rule', 'restaurant-reservations' ),
			'delete_schedule'	=> __( 'Delete scheduling rule', 'restaurant-reservations' ),
			'never'				=> _x( 'Never', 'Brief default description of a scheduling rule when no weekdays or weeks are included in the rule', 'restaurant-reservations' ),
			'weekly_always'	=> _x( 'Every day', 'Brief default description of a scheduling rule when all the weekdays/weeks are included in the rule', 'restaurant-reservations' ),
			'monthly_weekdays'	=> _x( '%s on the %s week of the month', 'Brief default description of a scheduling rule when some weekdays are included on only some weeks of the month. %s should be left alone and will be replaced by a comma-separated list of days and weeks in the following format: M, T, W on the first, second week of the month', 'restaurant-reservations' ),
			'monthly_weeks'		=> _x( '%s week of the month', 'Brief default description of a scheduling rule when some weeks of the month are included but all or no weekdays are selected. %s should be left alone and will be replaced by a comma-separated list of weeks in the following format: First, second week of the month', 'restaurant-reservations' ),
			'all_day'			=> _x( 'All day', 'Brief default description of a scheduling rule when no times are set', 'restaurant-reservations' ),
			'before'			=> _x( 'Ends at', 'Brief default description of a scheduling rule when an end time is set but no start time. If the end time is 6pm, it will read: Ends at 6pm', 'restaurant-reservations' ),
			'after'				=> _x( 'Starts at', 'Brief default description of a scheduling rule when a start time is set but no end time. If the start time is 6pm, it will read: Starts at 6pm', 'restaurant-reservations' ),
			'separator'			=> _x( '&mdash;', 'Separator between times of a scheduling rule', 'restaurant-reservations' ),
		);

		$sap->add_setting(
			'rtb-settings',
			'rtb-schedule',
			'scheduler',
			array(
				'id'			=> 'schedule-open',
				'title'			=> __( 'Schedule', 'restaurant-reservations' ),
				'description'	=> __( 'Define the weekly schedule during which you accept bookings.', 'restaurant-reservations' ),
				'weekdays'		=> array(
					'monday'		=> _x( 'Mo', 'Monday abbreviation', 'restaurant-reservations' ),
					'tuesday'		=> _x( 'Tu', 'Tuesday abbreviation', 'restaurant-reservations' ),
					'wednesday'		=> _x( 'We', 'Wednesday abbreviation', 'restaurant-reservations' ),
					'thursday'		=> _x( 'Th', 'Thursday abbreviation', 'restaurant-reservations' ),
					'friday'		=> _x( 'Fr', 'Friday abbreviation', 'restaurant-reservations' ),
					'saturday'		=> _x( 'Sa', 'Saturday abbreviation', 'restaurant-reservations' ),
					'sunday'		=> _x( 'Su', 'Sunday abbreviation', 'restaurant-reservations' )
				),
				'time_format'	=> $this->get_setting( 'time-format' ),
				'date_format'	=> $this->get_setting( 'date-format' ),
				'disable_weeks'	=> true,
				'disable_date'	=> true,
				'strings' => $scheduler_strings,
			)
		);

		$scheduler_strings['all_day'] = _x( 'Closed all day', 'Brief default description of a scheduling exception when no times are set', 'restaurant-reservations' );
		$sap->add_setting(
			'rtb-settings',
			'rtb-schedule',
			'scheduler',
			array(
				'id'				=> 'schedule-closed',
				'title'				=> __( 'Exceptions', 'restaurant-reservations' ),
				'description'		=> __( "Define special opening hours for holidays, events or other needs. Leave the time empty if you're closed all day.", 'restaurant-reservations' ),
				'time_format'		=> $this->get_setting( 'time-format' ),
				'date_format'		=> $this->get_setting( 'date-format' ),
				'disable_weekdays'	=> true,
				'disable_weeks'		=> true,
				'strings' => $scheduler_strings,
			)
		);

		$sap->add_setting(
			'rtb-settings',
			'rtb-schedule',
			'select',
			array(
				'id'            => 'early-bookings',
				'title'         => __( 'Early Bookings', 'restaurant-reservations' ),
				'description'   => __( 'Select how early customers can make their booking. (Administrators and Booking Managers are not restricted by this setting.)', 'restaurant-reservations' ),
				'blank_option'	=> false,
				'options'       => array(
					''		=> __( 'Any time', 'restaurant-reservations' ),
					'1' 	=> __( 'From 1 day in advance', 'restaurant-reservations' ),
					'7' 	=> __( 'From 1 week in advance', 'restaurant-reservations' ),
					'14' 	=> __( 'From 2 weeks in advance', 'restaurant-reservations' ),
					'30' 	=> __( 'From 30 days in advance', 'restaurant-reservations' ),
					'90' 	=> __( 'From 90 days in advance', 'restaurant-reservations' ),
				)
			)
		);

		$sap->add_setting(
			'rtb-settings',
			'rtb-schedule',
			'select',
			array(
				'id'            => 'late-bookings',
				'title'         => __( 'Late Bookings', 'restaurant-reservations' ),
				'description'   => __( 'Select how late customers can make their booking. (Administrators and Booking Managers are not restricted by this setting.)', 'restaurant-reservations' ),
				'blank_option'	=> false,
				'options'       => array(
					'' 	       => __( 'Up to the last minute', 'restaurant-reservations' ),
					'15'       => __( 'At least 15 minutes in advance', 'restaurant-reservations' ),
					'30'       => __( 'At least 30 minutes in advance', 'restaurant-reservations' ),
					'45'       => __( 'At least 45 minutes in advance', 'restaurant-reservations' ),
					'60'       => __( 'At least 1 hour in advance', 'restaurant-reservations' ),
					'240'      => __( 'At least 4 hours in advance', 'restaurant-reservations' ),
					'1440'     => __( 'At least 24 hours in advance', 'restaurant-reservations' ),
					'same_day' => __( 'Block same-day bookings', 'restaurant-reservations' ),
				)
			)
		);

		$sap->add_setting(
			'rtb-settings',
			'rtb-schedule',
			'select',
			array(
				'id'			=> 'date-onload',
				'title'			=> __( 'Date Pre-selection', 'restaurant-reservations' ),
				'description'	=> __( 'When the booking form is loaded, should it automatically attempt to select a valid date?', 'restaurant-reservations' ),
				'blank_option'	=> false,
				'options'       => array(
					'' 			=> __( 'Select today if valid', 'restaurant-reservations' ),
					'soonest'	=> __( 'Select today or next valid date', 'restaurant-reservations' ),
					'empty' 	=> __( 'Leave empty', 'restaurant-reservations' ),
				)
			)
		);

		$sap->add_setting(
			'rtb-settings',
			'rtb-schedule',
			'select',
			array(
				'id'			=> 'time-interval',
				'title'			=> __( 'Time Interval', 'restaurant-reservations' ),
				'description'	=> __( 'Select the number of minutes between each available time.', 'restaurant-reservations' ),
				'blank_option'	=> false,
				'options'       => array(
					'' 			=> __( 'Every 30 minutes', 'restaurant-reservations' ),
					'15' 		=> __( 'Every 15 minutes', 'restaurant-reservations' ),
					'10' 		=> __( 'Every 10 minutes', 'restaurant-reservations' ),
					'5' 		=> __( 'Every 5 minutes', 'restaurant-reservations' ),
				)
			)
		);

		$sap->add_setting(
			'rtb-settings',
			'rtb-schedule',
			'select',
			array(
				'id'            => 'week-start',
				'title'         => __( 'Week Starts On', 'restaurant-reservations' ),
				'description'	=> __( 'Select the first day of the week', 'restaurant-reservations' ),
				'blank_option'	=> false,
				'options'       => array(
					'0' => __( 'Sunday', 'restaurant-reservations' ),
					'1' => __( 'Monday', 'restaurant-reservations' ),
				)
			)
		);

		$sap->add_section(
			'rtb-settings',
			array(
				'id'            => 'rtb-notifications',
				'title'         => __( 'Notifications', 'restaurant-reservations' ),
				'is_tab'		=> true,
			)
		);

		$sap->add_setting(
			'rtb-settings',
			'rtb-notifications',
			'text',
			array(
				'id'			=> 'reply-to-name',
				'title'			=> __( 'Reply-To Name', 'restaurant-reservations' ),
				'description'	=> __( 'The name which should appear in the Reply-To field of a user notification email', 'restaurant-reservations' ),
				'placeholder'	=> $this->defaults['reply-to-name'],
			)
		);

		$sap->add_setting(
			'rtb-settings',
			'rtb-notifications',
			'text',
			array(
				'id'			=> 'reply-to-address',
				'title'			=> __( 'Reply-To Email Address', 'restaurant-reservations' ),
				'description'	=> __( 'The email address which should appear in the Reply-To field of a user notification email.', 'restaurant-reservations' ),
				'placeholder'	=> $this->defaults['reply-to-address'],
			)
		);

		$sap->add_setting(
			'rtb-settings',
			'rtb-notifications',
			'toggle',
			array(
				'id'			=> 'admin-email-option',
				'title'			=> __( 'Admin Notification', 'restaurant-reservations' ),
				'label'			=> __( 'Send an email notification to an administrator when a new booking is requested.', 'restaurant-reservations' )
			)
		);

		$sap->add_setting(
			'rtb-settings',
			'rtb-notifications',
			'text',
			array(
				'id'			=> 'admin-email-address',
				'title'			=> __( 'Admin Email Address', 'restaurant-reservations' ),
				'description'	=> __( 'The email address where admin notifications should be sent.', 'restaurant-reservations' ),
				'placeholder'	=> $this->defaults['admin-email-address'],
			)
		);

		$sap->add_section(
			'rtb-settings',
			array(
				'id'            => 'rtb-notifications-templates',
				'title'         => __( 'Email Templates', 'restaurant-reservations' ),
				'tab'			=> 'rtb-notifications',
				'description'	=> __( 'Adjust the messages that are emailed to users and admins during the booking process.', 'restaurant-reservations' ),
			)
		);

		$sap->add_setting(
			'rtb-settings',
			'rtb-notifications-templates',
			'html',
			array(
				'id'			=> 'template-tags-description',
				'title'			=> __( 'Template Tags', 'restaurant-reservations' ),
				'html'			=> '
					<p class="description">' . __( 'Use the following tags to automatically add booking information to the emails. Tags labeled with an asterisk (*) can be used in the email subject as well.', 'restaurant-reservations' ) . '</p>' .
					$this->render_template_tag_descriptions(),
			)
		);

		$sap->add_setting(
			'rtb-settings',
			'rtb-notifications-templates',
			'text',
			array(
				'id'			=> 'subject-booking-admin',
				'title'			=> __( 'Admin Notification Subject', 'restaurant-reservations' ),
				'description'	=> __( 'The email subject for admin notifications.', 'restaurant-reservations' ),
				'placeholder'	=> $this->defaults['subject-booking-admin'],
			)
		);

		$sap->add_setting(
			'rtb-settings',
			'rtb-notifications-templates',
			'editor',
			array(
				'id'			=> 'template-booking-admin',
				'title'			=> __( 'Admin Notification Email', 'restaurant-reservations' ),
				'description'	=> __( 'Enter the email an admin should receive when an initial booking request is made.', 'restaurant-reservations' ),
				'default'		=> $this->defaults['template-booking-admin'],
			)
		);

		$sap->add_setting(
			'rtb-settings',
			'rtb-notifications-templates',
			'text',
			array(
				'id'			=> 'subject-booking-user',
				'title'			=> __( 'New Request Email Subject', 'restaurant-reservations' ),
				'description'	=> __( 'The email subject a user should receive when they make an initial booking request.', 'restaurant-reservations' ),
				'placeholder'	=> $this->defaults['subject-booking-user'],
			)
		);

		$sap->add_setting(
			'rtb-settings',
			'rtb-notifications-templates',
			'editor',
			array(
				'id'			=> 'template-booking-user',
				'title'			=> __( 'New Request Email', 'restaurant-reservations' ),
				'description'	=> __( 'Enter the email a user should receive when they make an initial booking request.', 'restaurant-reservations' ),
				'default'		=> $this->defaults['template-booking-user'],
			)
		);

		$sap->add_setting(
			'rtb-settings',
			'rtb-notifications-templates',
			'text',
			array(
				'id'			=> 'subject-confirmed-user',
				'title'			=> __( 'Confirmed Email Subject', 'restaurant-reservations' ),
				'description'	=> __( 'The email subject a user should receive when their booking has been confirmed.', 'restaurant-reservations' ),
				'placeholder'	=> $this->defaults['subject-confirmed-user'],
			)
		);

		$sap->add_setting(
			'rtb-settings',
			'rtb-notifications-templates',
			'editor',
			array(
				'id'			=> 'template-confirmed-user',
				'title'			=> __( 'Confirmed Email', 'restaurant-reservations' ),
				'description'	=> __( 'Enter the email a user should receive when their booking has been confirmed.', 'restaurant-reservations' ),
				'default'		=> $this->defaults['template-confirmed-user'],
			)
		);

		$sap->add_setting(
			'rtb-settings',
			'rtb-notifications-templates',
			'text',
			array(
				'id'			=> 'subject-rejected-user',
				'title'			=> __( 'Rejected Email Subject', 'restaurant-reservations' ),
				'description'	=> __( 'The email subject a user should receive when their booking has been rejected.', 'restaurant-reservations' ),
				'placeholder'	=> $this->defaults['subject-rejected-user'],
			)
		);

		$sap->add_setting(
			'rtb-settings',
			'rtb-notifications-templates',
			'editor',
			array(
				'id'			=> 'template-rejected-user',
				'title'			=> __( 'Rejected Email', 'restaurant-reservations' ),
				'description'	=> __( 'Enter the email a user should receive when their booking has been rejected.', 'restaurant-reservations' ),
				'default'		=> $this->defaults['template-rejected-user'],
			)
		);

		$sap->add_setting(
			'rtb-settings',
			'rtb-notifications-templates',
			'text',
			array(
				'id'			=> 'subject-admin-notice',
				'title'			=> __( 'Admin Update Subject', 'restaurant-reservations' ),
				'description'	=> sprintf( __( 'The email subject a user should receive when an admin sends them a custom email message from the %sbookings panel%s.', 'restaurant-reservations' ), '<a href="' . admin_url( '?page=rtb-bookings' ) . '">', '</a>' ),
				'placeholder'	=> $this->defaults['subject-admin-notice'],
			)
		);

		$sap = apply_filters( 'rtb_settings_page', $sap );

		$sap->add_admin_menus();

	}

	/**
	 * Get options for the party size setting
	 * @since 1.3
	 */
	public function get_party_size_setting_options( $max = true ) {

		$options = array();

		if ( $max ) {
			$options[''] = __( 'Any size', 'restaurant-reservations' );
		}

		$max = apply_filters( 'rtb_party_size_upper_limit', 100 );

		for ( $i = 1; $i <= $max; $i++ ) {
			$options[$i] = $i;
		}

		return apply_filters( 'rtb_party_size_setting_options', $options );
	}

	/**
	 * Get options for the party select field in the booking form
	 * @since 1.3
	 */
	public function get_form_party_options() {

		$party_size = (int) $this->get_setting( 'party-size' );
		$party_size_min = (int) $this->get_setting( 'party-size-min' );

		$min = empty( $party_size_min ) ? 1 : (int) $this->get_setting( 'party-size-min' );
		$max = empty( $party_size ) ? apply_filters( 'rtb_party_size_upper_limit', 100 ) : (int) $this->get_setting( 'party-size' );

		for ( $i = $min; $i <= $max; $i++ ) {
			$options[$i] = $i;
		}

		return apply_filters( 'rtb_form_party_options', $options );
	}

	/**
	 * Retrieve form fields
	 *
	 * @param $request rtbBooking Details of a booking request made
	 * @param $args array Associative array of arguments to pass to the field:
	 *  `location` int Location post id
	 * @since 1.3
	 */
	public function get_booking_form_fields( $request = null, $args = array() ) {

		// $request will represent a rtbBooking object with the request
		// details when the form is being printed and $_POST data exists
		// to populate the request. All other times $request will just
		// be an empty object
		if ( $request === null ) {
			global $rtb_controller;
			$request = $rtb_controller->request;
		}

		/**
		 * This array defines the field details and a callback function to
		 * render each field. To customize the form output, modify the
		 * callback functions to point to your custom function. Don't forget
		 * to output an error message in your custom callback function. You
		 * can use rtb_print_form_error( $slug ) to do this.
		 *
		 * In addition to the parameters described below, each fieldset
		 * and field can accept a `classes` array in the callback args since
		 * v1.3. These classes will be appended to the <fieldset> and
		 * <div> elements for each field. A fieldset can also take a
		 * `legend_classes` array in the callback_args which will be
		 * added to the legend element.
		 *
		 * Example:
		 *
		 * 	$fields = array(
		 * 		'fieldset'	=> array(
		 * 			'legend'	=> __( 'My Legend', 'restaurant-reservations' ),
		 * 			'callback_args'	=> array(
		 * 				'classes'		=> array( 'fieldset-class', 'other-fieldset-class' ),
		 * 				'legend_classes	=> array( 'legend-class' ),
		 *			),
		 * 			'fields'	=> array(
		 * 				'my-field'	=> array(
		 * 					...
		 * 					'callback_args'	=> array(
		 * 						'classes'	=> array( 'field-class' ),
		 *					)
		 * 				)
		 * 			)
		 * 		)
		 * 	);
		 *
		 * See /includes/template-functions.php
		 */
		$fields = array(

			// Reservation details fieldset
			'reservation'	=> array(
				'legend'	=> __( 'Book a table', 'restaurant-reservations' ),
				'fields'	=> array(
					'date'		=> array(
						'title'			=> __( 'Date', 'restaurant-reservations' ),
						'request_input'	=> empty( $request->request_date ) ? '' : $request->request_date,
						'callback'		=> 'rtb_print_form_text_field',
						'required'		=> true,
					),
					'time'		=> array(
						'title'			=> __( 'Time', 'restaurant-reservations' ),
						'request_input'	=> empty( $request->request_time ) ? '' : $request->request_time,
						'callback'		=> 'rtb_print_form_text_field',
						'required'		=> true,
					),
					'party'		=> array(
						'title'			=> __( 'Party', 'restaurant-reservations' ),
						'request_input'	=> empty( $request->party ) ? '' : $request->party,
						'callback'		=> 'rtb_print_form_select_field',
						'callback_args'	=> array(
							'options'	=> $this->get_form_party_options(),
						),
						'required'		=> true,
					),
				),
			),

			// Contact details fieldset
			'contact'	=> array(
				'legend'	=> __( 'Contact Details', 'restaurant-reservations' ),
				'fields'	=> array(
					'name'		=> array(
						'title'			=> __( 'Name', 'restaurant-reservations' ),
						'request_input'	=> empty( $request->name ) ? '' : $request->name,
						'callback'		=> 'rtb_print_form_text_field',
						'required'		=> true,
					),
					'email'		=> array(
						'title'			=> __( 'Email', 'restaurant-reservations' ),
						'request_input'	=> empty( $request->email ) ? '' : $request->email,
						'callback'		=> 'rtb_print_form_text_field',
						'callback_args'	=> array(
							'input_type'	=> 'email',
						),
						'required'		=> true,
					),
					'phone'		=> array(
						'title'			=> __( 'Phone', 'restaurant-reservations' ),
						'request_input'	=> empty( $request->phone ) ? '' : $request->phone,
						'callback'		=> 'rtb_print_form_text_field',
						'callback_args'	=> array(
							'input_type'	=> 'tel',
						),
					),
					'add-message'	=> array(
						'title'		=> __( 'Add a Message', 'restaurant-reservations' ),
						'request_input'	=> '',
						'callback'	=> 'rtb_print_form_message_link',
					),
					'message'		=> array(
						'title'			=> __( 'Message', 'restaurant-reservations' ),
						'request_input'	=> empty( $request->message ) ? '' : $request->message,
						'callback'		=> 'rtb_print_form_textarea_field',
					),
				),
			),
		);

		return apply_filters( 'rtb_booking_form_fields', $fields, $request, $args );
	}

	/**
	 * Get required fields
	 *
	 * Filters the fields array to return just those marked required
	 * @since 1.3
	 */
	public function get_required_fields() {

		$required_fields = array();

		$fieldsets = $this->get_booking_form_fields();
		foreach ( $fieldsets as $fieldset ) {
			$required_fields = array_merge( $required_fields, array_filter( $fieldset['fields'], array( $this, 'is_field_required' ) ) );
		}

		return $required_fields;
	}

	/**
	 * Check if a field is required
	 *
	 * @since 1.3
	 */
	public function is_field_required( $field ) {
		return !empty( $field['required'] );
	}

	/**
	 * Render HTML code of descriptions for the template tags
	 * @since 1.2.3
	 */
	public function render_template_tag_descriptions() {

		$descriptions = apply_filters( 'rtb_notification_template_tag_descriptions', array(
				'{user_email}'		=> __( 'Email of the user who made the booking', 'restaurant-reservations' ),
				'{user_name}'		=> __( '* Name of the user who made the booking', 'restaurant-reservations' ),
				'{party}'			=> __( '* Number of people booked', 'restaurant-reservations' ),
				'{date}'			=> __( '* Date and time of the booking', 'restaurant-reservations' ),
				'{phone}'			=> __( 'Phone number if supplied with the request', 'restaurant-reservations' ),
				'{message}'			=> __( 'Message added to the request', 'restaurant-reservations' ),
				'{bookings_link}'	=> __( 'A link to the admin panel showing pending bookings', 'restaurant-reservations' ),
				'{confirm_link}'	=> __( 'A link to confirm this booking. Only include this in admin notifications', 'restaurant-reservations' ),
				'{close_link}'		=> __( 'A link to reject this booking. Only include this in admin notifications', 'restaurant-reservations' ),
				'{site_name}'		=> __( 'The name of this website', 'restaurant-reservations' ),
				'{site_link}'		=> __( 'A link to this website', 'restaurant-reservations' ),
				'{current_time}'	=> __( 'Current date and time', 'restaurant-reservations' ),
			)
		);

		$output = '';

		foreach ( $descriptions as $tag => $description ) {
			$output .= '
				<div class="rtb-template-tags-box">
					<strong>' . $tag . '</strong> ' . $description . '
				</div>';
		}

		return $output;
	}

	/**
	 * Sort the schedule exceptions and remove past exceptions before saving
	 *
	 * @since 1.4.6
	 */
	public function clean_schedule_exceptions( $val ) {

		if ( empty( $val['schedule-closed'] ) ) {
			return $val;
		}

		// Sort by date
		$schedule_closed = $val['schedule-closed'];
		usort( $schedule_closed, array( $this, 'sort_by_date' ) );

		// Remove exceptions more than a week old
		$week_ago = time() - 604800;
		for( $i = 0; $i < count( $schedule_closed ); $i++ ) {
			if ( strtotime( $schedule_closed[$i]['date'] ) > $week_ago ) {
				break;
			}
		}
		if ( $i ) {
			$schedule_closed = array_slice( $schedule_closed, $i );
		}

		$val['schedule-closed'] = $schedule_closed;

		return $val;
	}

	/**
	 * Sort an associative array by the value's date parameter
	 *
	 * @usedby self::clean_schedule_exceptions()
	 * @since 0.1
	 */
	public function sort_by_date( $a, $b ) {

		$ad = empty( $a['date'] ) ? 0 : strtotime( $a['date'] );
		$bd = empty( $b['date'] ) ? 0 : strtotime( $b['date'] );

		return $ad - $bd;
	}

}
} // endif;
