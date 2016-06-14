<?php
/**
 * Plugin Name: Restaurant Reservations
 * Plugin URI: http://themeofthecrop.com
 * Description: Accept restaurant reservations and bookings online.
 * Version: 1.6
 * Author: Theme of the Crop
 * Author URI: http://themeofthecrop.com
 * License:     GNU General Public License v2.0 or later
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 *
 * Text Domain: restaurant-reservations
 * Domain Path: /languages/
 *
 * This program is free software; you can redistribute it and/or modify it under the terms of the GNU
 * General Public License as published by the Free Software Foundation; either version 2 of the License,
 * or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without
 * even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 *
 * You should have received a copy of the GNU General Public License along with this program; if not, write
 * to the Free Software Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA 02110-1301 USA
 */
if ( ! defined( 'ABSPATH' ) )
	exit;

if ( !class_exists( 'rtbInit' ) ) {
class rtbInit {

	/**
	 * Set a flag which tracks whether the form has already been rendered on
	 * the page. Only one form per page for now.
	 * @todo support multiple forms per page
	 */
	public $form_rendered = false;

	/**
	 * An object which stores a booking request, or an empty object if
	 * no request has been processed.
	 */
	public $request;

	/**
	 * Initialize the plugin and register hooks
	 */
	public function __construct() {

		// Common strings
		define( 'RTB_VERSION', '1.6' );
		define( 'RTB_PLUGIN_DIR', untrailingslashit( plugin_dir_path( __FILE__ ) ) );
		define( 'RTB_PLUGIN_URL', untrailingslashit( plugins_url( basename( plugin_dir_path( __FILE__ ) ), basename( __FILE__ ) ) ) );
		define( 'RTB_PLUGIN_FNAME', plugin_basename( __FILE__ ) );
		define( 'RTB_BOOKING_POST_TYPE', 'rtb-booking' );
		define( 'RTB_BOOKING_POST_TYPE_SLUG', 'booking' );
		define( 'RTB_LOAD_FRONTEND_ASSETS', apply_filters( 'rtb-load-frontend-assets', true ) );

		// Initialize the plugin
		add_action( 'init', array( $this, 'load_textdomain' ) );

		// Set up empty request object
		$this->request = new stdClass();
		$this->request->request_processed = false;
		$this->request->request_inserted = false;

		// Load query class
		require_once( RTB_PLUGIN_DIR . '/includes/Query.class.php' );

		// Add custom roles and capabilities
		add_action( 'init', array( $this, 'add_roles' ) );

		// Load custom post types
		require_once( RTB_PLUGIN_DIR . '/includes/CustomPostTypes.class.php' );
		$this->cpts = new rtbCustomPostTypes();

		// Load multiple location support
		require_once( RTB_PLUGIN_DIR . '/includes/MultipleLocations.class.php' );
		$this->locations = new rtbMultipleLocations();

		// Flush the rewrite rules for the custom post types
		register_activation_hook( __FILE__, array( $this, 'rewrite_flush' ) );

		// Load the template functions which print the booking form, etc
		require_once( RTB_PLUGIN_DIR . '/includes/template-functions.php' );

		// Load the admin bookings page
		require_once( RTB_PLUGIN_DIR . '/includes/AdminBookings.class.php' );
		$this->bookings = new rtbAdminBookings();

		// Load assets
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_assets' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'register_assets' ) );

		// Handle notifications
		require_once( RTB_PLUGIN_DIR . '/includes/Notifications.class.php' );
		$this->notifications = new rtbNotifications();

		// Load settings
		require_once( RTB_PLUGIN_DIR . '/includes/Settings.class.php' );
		$this->settings = new rtbSettings();

		// Append booking form to a post's $content variable
		add_filter( 'the_content', array( $this, 'append_to_content' ) );

		// Register the widget
		add_action( 'widgets_init', array( $this, 'register_widgets' ) );

		// Add links to plugin listing
		add_filter('plugin_action_links', array( $this, 'plugin_action_links' ), 10, 2);

		// Load the license handling
		if ( file_exists( RTB_PLUGIN_DIR . '/includes/Licenses.class.php' ) ) {
			require_once( RTB_PLUGIN_DIR . '/includes/Licenses.class.php' );
			$this->licenses = new rtbLicenses();
		}

		// Add the addons page
		require_once( RTB_PLUGIN_DIR . '/includes/Addons.class.php' );
		new rtbAddons(
			array(
				'api_url'	=> 'http://api.themeofthecrop.com/addons/',
				'plugin'	=> basename( plugin_dir_path( __FILE__ ) ),
			)
		);

		// Load integrations with other plugins
		require_once( RTB_PLUGIN_DIR . '/includes/integrations/business-profile.php' );
		require_once( RTB_PLUGIN_DIR . '/includes/integrations/woocommerce.php' );

		// Load backwards compatibility functions
		require_once( RTB_PLUGIN_DIR . '/includes/Compatibility.class.php' );
		new rtbCompatibility();

	}

	/**
	 * Flush the rewrite rules when this plugin is activated to update with
	 * custom post types
	 * @since 0.0.1
	 */
	public function rewrite_flush() {
		$this->cpts->load_cpts();
		flush_rewrite_rules();
	}

	/**
	 * Load the plugin textdomain for localistion
	 * @since 0.0.1
	 */
	public function load_textdomain() {
		load_plugin_textdomain( 'restaurant-reservations', false, plugin_basename( dirname( __FILE__ ) ) . "/languages/" );
	}

	/**
	 * Add a role to manage the bookings and add the capability to Editors,
	 * Administrators and Super Admins
	 * @since 0.0.1
	 */
	public function add_roles() {

		// The booking manager should be able to access the bookings list and
		// update booking statuses, but shouldn't be able to touch anything else
		// in the account.
		$booking_manager = add_role(
			'rtb_booking_manager',
			__( 'Booking Manager', 'restaurant-reservations' ),
			array(
				'read'				=> true,
				'manage_bookings'	=> true,
			)
		);

		$manage_bookings_roles = apply_filters(
			'rtb_manage_bookings_roles',
			array(
				'administrator',
				'editor',
			)
		);

		global $wp_roles;
		foreach ( $manage_bookings_roles as $role ) {
			$wp_roles->add_cap( $role, 'manage_bookings' );
		}
	}

	/**
	 * Append booking form to a post's $content variable
	 * @since 0.0.1
	 */
	function append_to_content( $content ) {

		if ( !is_main_query() || !in_the_loop() || post_password_required() ) {
			return $content;
		}

		$booking_page = $this->settings->get_setting( 'booking-page' );
		if ( empty( $booking_page ) ) {
			return $content;
		}

		global $post;
		if ( $post->ID != $this->settings->get_setting( 'booking-page' ) ) {
			return $content;
		}

		return $content . rtb_print_booking_form();
	}

	/**
	 * Enqueue the admin-only CSS and Javascript
	 * @since 0.0.1
	 */
	public function enqueue_admin_assets() {

		// Use the page reference in $admin_page_hooks because
		// it changes in SOME hooks when it is translated.
		// https://core.trac.wordpress.org/ticket/18857
		global $admin_page_hooks;

		$screen = get_current_screen();
		if ( empty( $screen ) || empty( $admin_page_hooks['rtb-bookings'] ) ) {
			return;
		}

		if ( $screen->base == 'toplevel_page_rtb-bookings' || $screen->base == $admin_page_hooks['rtb-bookings'] . '_page_rtb-settings' || $screen->base == $admin_page_hooks['rtb-bookings'] . '_page_rtb-addons' ) {
			wp_enqueue_style( 'rtb-admin', RTB_PLUGIN_URL . '/assets/css/admin.css' );
			wp_enqueue_script( 'rtb-admin', RTB_PLUGIN_URL . '/assets/js/admin.js', array( 'jquery' ), '', true );
			wp_localize_script(
				'rtb-admin',
				'rtb_admin',
				array(
					'nonce'		=> wp_create_nonce( 'rtb-admin' ),
					'strings'	=> array(
						'add_booking'		=> __( 'Add Booking', 'restaurant-reservations' ),
						'edit_booking'		=> __( 'Edit Booking', 'restaurant-reservations' ),
						'error_unspecified'	=> __( 'An unspecified error occurred. Please try again. If the problem persists, try logging out and logging back in.', 'restaurant-reservations' ),
					),
				)
			);
		}

		// Enqueue frontend assets to add/edit bookins on the bookings page
		if ( $screen->base == 'toplevel_page_rtb-bookings' ) {
			$this->register_assets();
			rtb_enqueue_assets();
		}
	}

	/**
	 * Register the front-end CSS and Javascript for the booking form
	 * @since 0.0.1
	 */
	function register_assets() {

		if ( !RTB_LOAD_FRONTEND_ASSETS ) {
			return;
		}

		wp_register_style( 'pickadate-default', RTB_PLUGIN_URL . '/lib/simple-admin-pages/lib/pickadate/themes/default.css' );
		wp_register_style( 'pickadate-date', RTB_PLUGIN_URL . '/lib/simple-admin-pages/lib/pickadate/themes/default.date.css' );
		wp_register_style( 'pickadate-time', RTB_PLUGIN_URL . '/lib/simple-admin-pages/lib/pickadate/themes/default.time.css' );
		wp_register_script( 'pickadate', RTB_PLUGIN_URL . '/lib/simple-admin-pages/lib/pickadate/picker.js', array( 'jquery' ), '', true );
		wp_register_script( 'pickadate-date', RTB_PLUGIN_URL . '/lib/simple-admin-pages/lib/pickadate/picker.date.js', array( 'jquery' ), '', true );
		wp_register_script( 'pickadate-time', RTB_PLUGIN_URL . '/lib/simple-admin-pages/lib/pickadate/picker.time.js', array( 'jquery' ), '', true );
		wp_register_script( 'pickadate-legacy', RTB_PLUGIN_URL . '/lib/simple-admin-pages/lib/pickadate/legacy.js', array( 'jquery' ), '', true );

		$i8n = $this->settings->get_setting( 'i8n' );
		if ( !empty( $i8n ) ) {
			wp_register_script( 'pickadate-i8n', RTB_PLUGIN_URL . '/lib/simple-admin-pages/lib/pickadate/translations/' . esc_attr( $i8n ) . '.js', array( 'jquery' ), '', true );

			// Arabic and Hebrew are right-to-left languages
			if ( $i8n == 'ar' || $i8n == 'he_IL' ) {
				wp_register_style( 'pickadate-rtl', RTB_PLUGIN_URL . '/lib/simple-admin-pages/lib/pickadate/themes/rtl.css' );
			}
		}

		wp_register_style( 'rtb-booking-form', RTB_PLUGIN_URL . '/assets/css/booking-form.css' );
		wp_register_script( 'rtb-booking-form', RTB_PLUGIN_URL . '/assets/js/booking-form.js', array( 'jquery' ) );
	}

	/**
	 * Register the widgets
	 * @since 0.0.1
	 */
	public function register_widgets() {
		require_once( RTB_PLUGIN_DIR . '/includes/WP_Widget.BookingFormWidget.class.php' );
		register_widget( 'rtbBookingFormWidget' );
	}

	/**
	 * Add links to the plugin listing on the installed plugins page
	 * @since 0.0.1
	 */
	public function plugin_action_links( $links, $plugin ) {

		if ( $plugin == RTB_PLUGIN_FNAME ) {

			$links['help'] = '<a href="http://doc.themeofthecrop.com/plugins/restaurant-reservations/?utm_source=Plugin&utm_medium=Plugin%Help&utm_campaign=Restaurant%20Reservations" title="' . __( 'View the help documentation for Restaurant Reservations', 'restaurant-reservations' ) . '">' . __( 'Help', 'restaurant-reservations' ) . '</a>';
		}

		return $links;

	}

}
} // endif;

global $rtb_controller;
$rtb_controller = new rtbInit();
