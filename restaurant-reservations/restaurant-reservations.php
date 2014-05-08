<?php
/**
 * Plugin Name: Restaurant Reservations
 * Plugin URI: http://themeofthecrop.com
 * Description: Accept restaurant reservations and bookings online.
 * Version: 1.0.1
 * Author: Theme of the Crop
 * Author URI: http://themeofthecrop.com
 * License:     GNU General Public License v2.0 or later
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 * Requires at least: 3.8
 * Tested up to: 3.9
 *
 * Text Domain: rtbdomain
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
	 * Initialize the plugin and register hooks
	 */
	public function __construct() {

		// Common strings
		define( 'RTB_TEXTDOMAIN', 'rtbdomain' );
		define( 'RTB_PLUGIN_DIR', untrailingslashit( plugin_dir_path( __FILE__ ) ) );
		define( 'RTB_PLUGIN_URL', untrailingslashit( plugins_url( basename( plugin_dir_path( __FILE__ ) ), basename( __FILE__ ) ) ) );
		define( 'RTB_PLUGIN_FNAME', plugin_basename( __FILE__ ) );
		define( 'RTB_BOOKING_POST_TYPE', 'rtb-booking' );
		define( 'RTB_BOOKING_POST_TYPE_SLUG', 'booking' );
		define( 'RTB_LOAD_FRONTEND_ASSETS', apply_filters( 'rtb-load-frontend-assets', true ) );
		

		// Initialize the plugin
		add_action( 'init', array( $this, 'load_textdomain' ) );

		// Add custom roles and capabilities
		add_action( 'init', array( $this, 'add_roles' ) );

		// Load custom post types
		require_once( RTB_PLUGIN_DIR . '/includes/CustomPostTypes.class.php' );
		$this->cpts = new rtbCustomPostTypes();

		// Add the admin menu
		add_action( 'admin_menu', array( $this, 'add_menu_page' ) );

		// Flush the rewrite rules for the custom post types
		register_activation_hook( __FILE__, array( $this, 'rewrite_flush' ) );

		// Load the template functions which print the booking form, etc
		require_once( RTB_PLUGIN_DIR . '/includes/template-functions.php' );

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

		// Development tool
		// @todo maybe split off this sort of thing to another file
		add_action( 'init', array( $this, 'dev_add_bookings_data' ) );

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
		load_plugin_textdomain( RTB_TEXTDOMAIN, false, plugin_basename( dirname( __FILE__ ) ) . "/languages" );
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
			__( 'Booking Manager', RTB_TEXTDOMAIN ),
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
	 * Add the top-level admin menu page
	 * @since 0.0.1
	 */
	public function add_menu_page() {

		add_menu_page(
			_x( 'Bookings', 'Title of admin page that lists bookings', RTB_TEXTDOMAIN ),
			_x( 'Bookings', 'Title of bookings admin menu item', RTB_TEXTDOMAIN ),
			'manage_bookings',
			'rtb-bookings',
			array( $this, 'show_admin_bookings_page' ),
			'dashicons-calendar',
			'26.2987'
		);

	}

	/**
	 * Display the admin bookings page
	 * @since 0.0.1
	 */
	public function show_admin_bookings_page() {

		require_once( RTB_PLUGIN_DIR . '/includes/WP_List_Table.BookingsTable.class.php' );
		$bookings_table = new rtbBookingsTable();
		$bookings_table->prepare_items();
		?>

		<div class="wrap">
			<h2><?php _e( 'Restaurant Bookings', RTB_TEXTDOMAIN ); ?></h2>
			<?php do_action( 'rtb_bookings_table_top' ); ?>
			<form id="rtb-bookings-table" method="POST" action="">
				<input type="hidden" name="post_type" value="<?php echo RTB_BOOKING_POST_TYPE; ?>" />
				<input type="hidden" name="page" value="rtb-bookings">

				<?php $bookings_table->views(); ?>
				<?php $bookings_table->advanced_filters(); ?>
				<?php $bookings_table->display(); ?>
			</form>
			<?php do_action( 'rtb_bookings_table_btm' ); ?>
		</div>

		<?php
	}

	/**
	 * Append booking form to a post's $content variable
	 * @since 0.0.1
	 */
	function append_to_content( $content ) {

		if ( !is_main_query() || !in_the_loop() ) {
			return $content;
		}

		$booking_page = $this->settings->get_setting( 'booking-page' );
		if ( empty( $booking_page ) ) {
			return $content;
		}

		global $post;
		if ( $post->ID !== $this->settings->get_setting( 'booking-page' ) ) {
			return $content;
		}

		return $content . rtb_print_booking_form();
	}

	/**
	 * Development tool to populate the database with lots of bookings
	 * @since 0.0.1
	 */
	public function dev_add_bookings_data() {

		if ( !WP_DEBUG || !isset( $_GET['rtb_devmode'] ) || $_GET['rtb_devmode'] !== 'add_bookings' ) {
			return;
		}

		$lorem = 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Etiam feugiat consequat diam, in tincidunt purus convallis vel. Morbi sed dapibus diam. Vestibulum laoreet mi at neque varius consequat. Nam non mi erat. Donec nec semper velit. Maecenas id tortor orci. Aenean viverra suscipit urna, egestas adipiscing felis varius vitae. Curabitur et accumsan turpis. Suspendisse sed risus ac mi lobortis aliquam vel vel dolor. Nulla facilisi. In feugiat tempus massa, sed pulvinar neque bibendum ut. Nullam nibh eros, consectetur et orci non, condimentum tempor nunc. Maecenas sit amet libero sed diam pulvinar iaculis eget vitae odio. Quisque ac luctus metus, sit amet fringilla magna. Aliquam commodo odio eu eros imperdiet, ut auctor odio faucibus.';
		$words = explode( ' ', str_replace( array( ',', '.'), '', $lorem ) );
		for ( $i = 0; $i < 100; $i++ ) {

			shuffle( $words );

			$phone = '(';
			for( $p = 0; $p < 3; $p++ ) {
				$phone .= rand(0,9);
			}
			$phone .= ') ';
			for( $p = 0; $p < 3; $p++ ) {
				$phone .= rand(0,9);
			}
			$phone .= '-';
			for( $p = 0; $p < 4; $p++ ) {
				$phone .= rand(0,9);
			}

			$status = rand(0, 100) > 30 ? 'confirmed' : 'pending';
			$status = rand(0, 100) > 90 ? 'closed' : $status;

			// Get the date formatted for MySQL
			$date = new DateTime( date('Y-m-d H:i:s', current_time() + rand( 0, 7776000 ) ) ); // 90 days in advance

			$id = wp_insert_post(
				array(
					'post_type'		=> RTB_BOOKING_POST_TYPE,
					'post_title'	=> $words[0] . ' ' . $words[1],
					'post_content'	=> rand(0,10) < 3 ? $lorem : '',
					'post_date'		=> $date->format( 'Y-m-d H:i:s' ),
					'post_status'	=> $status,
				)
			);

			$meta = array(
				'party' => rand(1, 20),
				'email' => $words[2] . '@email.com',
				'phone' => $phone,
				'date_submission' 	=> current_time(), // keep track of when it was submitted for logs
			);

			$meta = apply_filters( 'rtb_sanitize_post_metadata_devmode', $meta, $id );

			update_post_meta( $id, 'rtb', $meta );

		}
	}

	/**
	 * Enqueue the admin-only CSS and Javascript
	 * @since 0.0.1
	 */
	public function enqueue_admin_assets() {

		$screen = get_current_screen();
		if ( $screen->base == 'toplevel_page_rtb-bookings' || $screen->base == 'bookings_page_rtb-settings' ) {
			wp_enqueue_style( 'rtb-admin', RTB_PLUGIN_URL . '/assets/css/admin.css' );
			wp_enqueue_script( 'rtb-admin', RTB_PLUGIN_URL . '/assets/js/admin.js', array( 'jquery' ), '', true );
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

			$links['help'] = '<a href="' . RTB_PLUGIN_URL . '/docs" title="' . __( 'View the help documentation for Restaurant Reservations', RTB_TEXTDOMAIN ) . '">' . __( 'Help', RTB_TEXTDOMAIN ) . '</a>';
		}

		return $links;

	}

}
} // endif;

$rtb_controller = new rtbInit();