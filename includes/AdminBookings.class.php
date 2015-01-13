<?php
if ( !defined( 'ABSPATH' ) ) exit;

if ( !class_exists( 'rtbAdminBookings' ) ) {
/**
 * Class to handle the admin bookings page for Restaurant Reservations
 *
 * @since 1.3
 */
class rtbAdminBookings {

	public function __construct() {

		// Add the admin menu
		add_action( 'admin_menu', array( $this, 'add_menu_page' ) );

		// Print the booking form modal
		add_action( 'admin_footer-toplevel_page_rtb-bookings', array( $this, 'print_booking_form_modal' ) );

		// Receive Ajax requests
		add_action( 'wp_ajax_nopriv_rtb-admin-booking-modal' , array( $this , 'nopriv_ajax' ) );
		add_action( 'wp_ajax_rtb-admin-booking-modal', array( $this, 'booking_modal_ajax' ) );
		add_action( 'wp_ajax_nopriv_rtb-admin-trash-booking' , array( $this , 'nopriv_ajax' ) );
		add_action( 'wp_ajax_rtb-admin-trash-booking', array( $this, 'trash_booking_ajax' ) );

		// Validate post status and notification fields
		add_action( 'rtb_validate_booking_submission', array( $this, 'validate_admin_fields' ) );

		// Set post status when adding to the database
		add_filter( 'rtb_insert_booking_data', array( $this, 'insert_booking_data' ), 10, 2 );

	}

	/**
	 * Add the top-level admin menu page
	 * @since 0.0.1
	 */
	public function add_menu_page() {

		add_menu_page(
			_x( 'Bookings', 'Title of admin page that lists bookings', 'restaurant-reservations' ),
			_x( 'Bookings', 'Title of bookings admin menu item', 'restaurant-reservations' ),
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
			<h2>
				<?php _e( 'Restaurant Bookings', 'restaurant-reservations' ); ?>
				<a href="#" class="add-new-h2 add-booking"><?php _e( 'Add New', 'restaurant-reservations' ); ?></a>
			</h2>

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
	 * Print the booking form modal container on the admin bookings page
	 * @since 0.0.1
	 */
	public function print_booking_form_modal() {
		?>

		<div id="rtb-booking-modal" class="rtb-admin-modal">
			<div class="rtb-booking-form rtb-container">
				<form method="POST">
					<input type="hidden" name="action" value="admin_booking_request">
					<input type="hidden" name="ID" value="">

					<?php
					/**
					 * The generated fields are wrapped in a div so we can
					 * replace its contents with an HTML blob passed back from
					 * an Ajax request. This way the field data and error
					 * messages are always populated from the same server-side
					 * code.
					 */
					?>
					<div id="rtb-booking-form-fields">
						<?php echo $this->print_booking_form_fields(); ?>
					</div>

					<button type="submit" class="button-primary">
						<?php _e( 'Add Booking', 'restaurant-reservations' ); ?>
					</button>
					<a href="#" class="button" id="rtb-cancel-booking-modal">
						<?php _e( 'Cancel', 'restaurant-reservations' ); ?>
					</a>
					<div class="action-status">
						<span class="spinner loading"></span>
						<span class="dashicons dashicons-no-alt error"></span>
						<span class="dashicons dashicons-yes success"></span>
					</div>
				</form>
			</div>
		</div>
		<!-- Restaurant Reservations error message modal -->
		<div id="rtb-error-modal" class="rtb-admin-modal">
			<div class="rtb-error rtb-container">
				<div class="rtb-error-msg"></div>
				<a href="#" class="button"><?php _e( 'Close', 'restaurant-reservations' ); ?></a>
			</div>
		</div>

		<?php
	}

	/**
	 * Retrieve booking form fields used in the admin booking modal. These
	 * fields are also passed back with ajax requests since they render error
	 * messages and populate fields with validated data.
	 * @since 0.0.1
	 */
	public function print_booking_form_fields() {

		global $rtb_controller;

		// Add post status and notification fields to admin booking form
		add_filter( 'rtb_booking_form_fields', array( $this, 'add_admin_fields' ), 20, 2 );

		// Retrieve the form fields
		$fields = $rtb_controller->settings->get_booking_form_fields( $rtb_controller->request );

		ob_start();
		?>

			<?php foreach( $fields as $fieldset => $contents ) : ?>
			<fieldset class="<?php echo $fieldset; ?>">
				<?php
					foreach( $contents['fields'] as $slug => $field ) {

						$args = empty( $field['callback_args'] ) ? null : $field['callback_args'];

						call_user_func( $field['callback'], $slug, $field['title'], $field['request_input'], $args );
					}
				?>
			</fieldset>
			<?php endforeach;

		// Remove the admin fields filter
		remove_filter( 'rtb_booking_form_fields', array( $this, 'add_admin_fields' ) );

		return ob_get_clean();
	}

	/**
	 * Add the post status and notifications fields to the booking form fields
	 * @since 1.3
	 */
	public function add_admin_fields( $fields, $request ) {

		if ( !is_admin() || !current_user_can( 'manage_bookings' ) ) {
			return $fields;
		}

		global $rtb_controller;

		// Get all valid booking statuses
		$booking_statuses = array();
		foreach( $rtb_controller->cpts->booking_statuses as $status => $args ) {
			$booking_statuses[ $status ] = $args['label'];
		}

		$fields['admin'] = array(
			'fields'	=> array(
				'post-status'	=> array(
					'title'			=> __( 'Booking Status', 'restaurant-reservations' ),
					'request_input'	=> empty( $request->post_status ) ? '' : $request->post_status,
					'callback'		=> 'rtb_print_form_select_field',
					'callback_args'	=> array(
						'options'		=> $booking_statuses,
					)
				),
				'notifications'	=> array(
					'title'			=> __( 'Send notifications', 'restaurant-reservations' ),
					'request_input'	=> empty( $request->send_notifications ) ? false : $request->send_notifications,
					'callback'		=> array( $this, 'print_form_send_notifications_field' ),
					'callback_args'	=> array(
						'description'	=> array(
							'prompt'		=> __( 'Learn more', 'restaurant-reservations' ),
							'text'			=> __( "When adding a booking or changing a booking's status with this form, no email notifications will be sent. Check this option if you want to send email notifications.", 'restaurant-reservations' ),
						),
					),
				),
			),
		);

		return $fields;
	}


	/**
	 * Print a field to toggle notifications when adding/editing a booking from
	 * the admin
	 * @since 1.3
	 */
	function print_form_send_notifications_field( $slug, $title, $value, $args ) {

		$slug = esc_attr( $slug );
		$title = esc_attr( $title );
		$value = (bool) $value;
		$description = empty( $args['description'] ) || empty( $args['description']['prompt'] ) || empty( $args['description']['text'] ) ? null : $args['description'];
		?>

		<div class="<?php echo $slug; ?>">
			<?php echo rtb_print_form_error( $slug ); ?>
			<label>
				<input type="checkbox" name="rtb-<?php echo esc_attr( $slug ); ?>" value="1"<?php checked( $value ); ?>>
				<?php echo $title; ?>
				<?php if ( !empty( $description ) ) : ?>
				<a href="#" class="rtb-description-prompt">
					<?php echo $description['prompt']; ?>
				</a>
				<?php endif; ?>
			</label>
			<?php if ( !empty( $description ) ) : ?>
			<div class="rtb-description">
				<?php echo $description['text']; ?>
			</div>
			<?php endif; ?>
		</div>

		<?php
	}

	/**
	 * Handle ajax requests from the admin bookings area from logged out users
	 * @since 1.3
	 */
	public function nopriv_ajax() {

		wp_send_json_error(
			array(
				'error' => 'loggedout',
				'msg' => sprintf( __( 'You have been logged out. Please %slogin again%s.', 'restaurant-reservations' ), '<a href="' . wp_login_url( admin_url( 'admin.php?page=rtb-bookings&status=pending' ) ) . '">', '</a>' ),
			)
		);
	}

	/**
	 * Handle ajax requests from the admin bookings area
	 * @since 1.3
	 */
	public function booking_modal_ajax() {

		global $rtb_controller;

		// Authenticate request
		if ( !check_ajax_referer( 'rtb-admin', 'nonce' ) || !current_user_can( 'manage_bookings' ) ) {
			$this->nopriv_ajax();
		}

		// Retrieve a booking with a GET request
		if ( !empty( $_GET['booking'] ) && !empty( $_GET['booking']['ID'] ) ) {

			$id = (int) $_GET['booking']['ID'];

			require_once( RTB_PLUGIN_DIR . '/includes/Booking.class.php' );
			$rtb_controller->request = new rtbBooking();
			$result = $rtb_controller->request->load_post( $id );

			if ( $result ) {

				// Don't allow editing of trashed bookings. This wil force
				// appropriate use of the trash status and (hopefully) prevent
				// mistakes in booking management.
				if ( $rtb_controller->request->post_status == 'trash' ) {
					wp_send_json_error(
						array(
							'error'		=> 'booking_trashed',
							'msg'		=> sprintf( __( 'This booking has been sent to the %sTrash%s where it can not be edited. Set the booking to Pending or Confirmed to edit it.', 'restaurant-reservations' ), '<a href="' . admin_url( 'admin.php?page=rtb-bookings&status=trash' ) . '">', '</a>' ),
						)
					);
				}

				$rtb_controller->request->prepare_request_data();
				wp_send_json_success(
					array(
						'booking'	=> $rtb_controller->request,
						'fields'	=> $this->print_booking_form_fields(),
					)
				);

			} else {
				wp_send_json_error(
					array(
						'error'		=> 'booking_not_found',
						'msg'		=> __( 'The booking could not be retrieved. Please reload the page and try again.', 'restaurant-reservations' ),
					)
				);
			}

		// Insert or update a booking with a POST request
		} elseif ( !empty( $_POST['booking'] ) ) {

			// Set up $_POST object for validation
			foreach( $_POST['booking'] as $field ) {
				$_POST[ $field['name'] ] = $field['value'];
			}

			require_once( RTB_PLUGIN_DIR . '/includes/Booking.class.php' );
			$rtb_controller->request = new rtbBooking();

			// Add an ID if we're updating the post
			if ( !empty( $_POST['ID'] ) ) {
				$rtb_controller->request->ID = (int) $_POST['ID'];
			}

			// Disable notifications
			$this->maybe_disable_notifications();

			$result = $rtb_controller->request->insert_booking();

			if ( $result ) {
				wp_send_json_success(
					array(
						'booking'	=> $rtb_controller->request,
					)
				);
			} else {
				wp_send_json_error(
					array(
						'error'		=> 'invalid_booking_data',
						'booking'	=> $rtb_controller->request,
						'fields'	=> $this->print_booking_form_fields(),
					)
				);
			}
		}

		// Fallback to a valid error
		wp_send_json_error();
	}

	/**
	 * Set booking status to trash
	 * @since 1.3
	 */
	public function trash_booking_ajax() {

		global $rtb_controller;

		// Authenticate request
		if ( !check_ajax_referer( 'rtb-admin', 'nonce' ) || !current_user_can( 'manage_bookings' ) || empty( $_POST['booking'] ) ) {
			$this->nopriv_ajax();
		}

		$id = (int) $_POST['booking'];

		$result = wp_trash_post( $id );

		if ( $result === false ) {
			wp_send_json_error(
				array(
					'error'		=> 'trash_failed',
					'msg'		=> __( 'Unable to trash this post. Please try again. If you continue to have trouble, please refresh the page.', 'restaurant-reservations' ),
				)
			);

		} else {
			wp_send_json_success(
				array(
					'booking'	=> $id,
				)
			);
		}



	}

	/**
	 * Validate post status and notification fields
	 * @since 1.3
	 */
	public function validate_admin_fields( $booking ) {

		// Only validate in the admin
		if ( !$_POST['action'] || $_POST['action'] !== 'admin_booking_request' ) {
			return;
		}

		global $rtb_controller;

		// Disable Notifications
		$booking->send_notifications = empty( $_POST['rtb-notifications'] ) ? false : true;
	}

	/**
	 * Adjust post status when adding/editing a booking from the admin area
	 * @since 1.3
	 */
	public function insert_booking_data( $args, $booking ) {

		// Validate user request
		if ( empty( $_POST['action'] ) || $_POST['action'] !== 'admin_booking_request' || !current_user_can( 'manage_bookings' ) ) {
			return $args;
		}

		if ( !empty( $booking->post_status ) ) {
			$args['post_status'] = $booking->post_status;
		}

		return $args;
	}

	/**
	 * Maybe disable notifications when adding/editing bookings from the
	 * admin booking modal
	 * @since 1.3
	 */
	public function maybe_disable_notifications() {

		// Don't disable notifications if they have opted to send them
		if ( !empty( $_POST['rtb-notifications'] ) ) {
			return;
		}

		// Disable all notifications. This filter is here in case a
		// third-party sets up a notification that they don't want to be
		// disabled even if the user has opted not to send notifications
		// To exempt a notification, hook into the filter and copy it
		// from $rtb_notifications to the empty array.
		global $rtb_controller;
		$rtb_controller->notifications->notifications = apply_filters( 'rtb_admin_disabled_notifications_exemption', array(), $rtb_controller->notifications->notifications );
	}
}
} // endif;
