<?php
if ( !defined( 'ABSPATH' ) ) exit;

if ( !class_exists( 'rtbAddons' ) ) {
/**
 * Class to handle the addons page for Restaurant Reservations
 *
 * @since 1.3
 */
class rtbAddons {

	public function __construct( ) {

		// Add the admin menu
		add_action( 'admin_menu', array( $this, 'add_menu_page' ), 100 );

		// Add a newsletter subscription prompt above the addons
		add_action( 'rtb_addons_pre', array( $this, 'add_subscribe_pompt' ) );
	}

	/**
	 * Add the addons page to the admin menu
	 */
	public function add_menu_page() {

		add_submenu_page(
			'rtb-bookings',
			_x( 'Addons', 'Title of addons page', 'restaurant-reservations' ),
			_x( 'Addons', 'Title of addons page in the admin menu', 'restaurant-reservations' ),
			'manage_options',
			'rtb-addons',
			array( $this, 'show_admin_addons_page' )
		);

	}

	/**
	 * Display the addons page
	 */
	public function show_admin_addons_page() {

		// Set campaign parameters for addon URLs
		$url_params = '?utm_source=Plugin&utm_medium=Addon%20List&utm_campaign=Restaurant%20Reservations';
		?>

		<div class="wrap">
			<h1><?php _e( 'Addons for Restaurant Reservations', 'restaurant-reservations' ); ?></h1>
			<?php do_action( 'rtb_addons_pre' ); ?>
			<div class="rtb-addons">
				<div class="addon addon-custom-fields">
					<a href="https://themeofthecrop.com/plugins/restaurant-reservations/custom-fields/<?php echo $url_params; ?>">
						<img src="<?php echo RTB_PLUGIN_URL . '/assets/img/custom-fields.png'; ?>">
					</a>
					<h3><?php echo esc_html( 'Custom Fields', 'restaurant-reservations' ); ?></h3>
					<div class="details">
						<div class="description">
							<?php echo esc_html( 'Plan your dinner service better by asking for special seating requests, dietary needs and more when customers book online.', 'restaurant-reservations' ); ?>
						</div>
						<div class="action">
							<a href="https://themeofthecrop.com/plugins/restaurant-reservations/custom-fields/<?php echo $url_params; ?>" class="button button-primary" target="_blank">
								<?php echo esc_html( 'Learn More', 'restaurant-reservations' ); ?>
							</a>
						</div>
					</div>
				</div>
				<div class="addon addon-export-bookings">
					<a href="https://themeofthecrop.com/plugins/restaurant-reservations/export-bookings/<?php echo $url_params; ?>">
						<img src="<?php echo RTB_PLUGIN_URL . '/assets/img/export-bookings.png'; ?>">
					</a>
					<h3><?php echo esc_html( 'Export Bookings', 'restaurant-reservations' ); ?></h3>
					<div class="details">
						<div class="description">
							<?php echo esc_html( 'Easily print your bookings in a PDF or export them to an Excel/CSV file so you can analyze patterns, cull customer data and import bookings into other services.' ); ?>
						</div>
						<div class="action">
							<a href="https://themeofthecrop.com/plugins/restaurant-reservations/export-bookings/<?php echo $url_params; ?>" class="button button-primary" target="_blank">
								<?php echo esc_html( 'Learn More', 'restaurant-reservations' ); ?>
							</a>
						</div>
					</div>
				</div>
				<div class="addon addon-mailchimp">
					<a href="https://themeofthecrop.com/plugins/restaurant-reservations/mailchimp/<?php echo $url_params; ?>">
						<img src="<?php echo RTB_PLUGIN_URL . '/assets/img/mailchimp.png'; ?>">
					</a>
					<h3><?php echo esc_html( 'MailChimp', 'restaurant-reservations' ); ?></h3>
					<div class="details">
						<div class="description">
							<?php echo esc_html( 'Subscribe requests to your MailChimp mailing list and watch your subscription rates grow effortlessly.' ); ?>
						</div>
						<div class="action">
							<a href="https://themeofthecrop.com/plugins/restaurant-reservations/mailchimp/<?php echo $url_params; ?>" class="button button-primary" target="_blank">
								<?php echo esc_html( 'Learn More', 'restaurant-reservations' ); ?>
							</a>
						</div>
					</div>
				</div>
				<div class="addon addon-themes">
					<a href="https://themeofthecrop.com/themes/<?php echo $url_params; ?>">
						<img src="<?php echo RTB_PLUGIN_URL . '/assets/img/themes.png'; ?>">
					</a>
					<h3><?php echo esc_html( 'Restaurant WordPress Themes', 'restaurant-reservations' ); ?></h3>
					<div class="details">
						<div class="description">
							<?php echo esc_html( 'Find the best WordPress restaurant themes that integrate beautifully with your reservations plugin.' ); ?>
						</div>
						<div class="action">
							<a href="https://themeofthecrop.com/themes/<?php echo $url_params; ?>" class="button" target="_blank">
								<?php echo esc_html( 'View Themes', 'restaurant-reservations' ); ?>
							</a>
						</div>
					</div>
				</div>
			</div>
			<?php do_action( 'rtb_addons_post' ); ?>
		</div>

		<?php
	}

	/**
	 * Add a prompt for users to subscribe to the Theme of the Crop mailing list
	 * below the addons list.
	 *
	 * @since 0.1
	 */
	public function add_subscribe_pompt() {

		?>

		<p>
			<?php
				echo sprintf(
					esc_html_x( 'Find out when new addons are available by subscribing to the %smonthly newsletter%s, liking %sTheme of the Crop%s on Facebook, or following %sTheme of the Crop%s on Twitter.', 'restaurant-reservations' ),
					'<a target="_blank" href="https://themeofthecrop.com/about/mailing-list/?utm_source=Plugin&utm_medium=Addon%20List&utm_campaign=Restaurant%20Reservations">',
					'</a>',
					'<a target="_blank" href="https://www.facebook.com/themeofthecrop/">',
					'</a>',
					'<a target="_blank" href="http://twitter.com/themeofthecrop">',
					'</a>'
				);
			?>
		</p>

		<?php
	}

}
} // endif;
