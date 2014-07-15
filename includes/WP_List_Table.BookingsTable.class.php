<?php
if ( ! defined( 'ABSPATH' ) ) exit;

if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

if ( !class_exists( 'rtbBookingsTable' ) ) {
/**
 * Bookings Table Class
 *
 * Extends WP_List_Table to display the list of bookings in a format similar to
 * the default WordPress post tables.
 *
 * @h/t Easy Digital Downloads by Pippin: https://easydigitaldownloads.com/
 * @since 0.0.1
 */
class rtbBookingsTable extends WP_List_Table {

	/**
	 * Number of results to show per page
	 *
	 * @var string
	 * @since 0.0.1
	 */
	public $per_page = 30;

	/**
	 * URL of this page
	 *
	 * @var string
	 * @since 0.0.1
	 */
	public $base_url;

	/**
	 * Array of booking counts by total and status
	 *
	 * @var array
	 * @since 0.0.1
	 */
	public $booking_counts;

	/**
	 * Array of bookings
	 *
	 * @var array
	 * @since 0.0.1
	 */
	public $bookings;

	/**
	 * Current date filters
	 *
	 * @var string
	 * @since 0.0.1
	 */
	public $filter_start_date = null;
	public $filter_end_date = null;

	/**
	 * Current query string
	 *
	 * @var string
	 * @since 0.0.1
	 */
	public $query_string;

	public function __construct() {

		global $status, $page;

		// Set parent defaults
		parent::__construct( array(
			'singular'  => __( 'Booking', RTB_TEXTDOMAIN ),
			'plural'    => __( 'Bookings', RTB_TEXTDOMAIN ),
			'ajax'      => false
		) );

		// Set the date filter
		$this->set_date_filter();

		// Strip unwanted query vars from the query string or ensure the correct
		// vars are used
		$this->query_string_maintenance();

		// Run any bulk action requests
		$this->process_bulk_action();
		
		// Run any quicklink requests
		$this->process_quicklink_action();

		// Retrieve a count of the number of bookings by status
		$this->get_booking_counts();

		// Retrieve bookings data for the table
		$this->bookings_data();

		$this->base_url = admin_url( 'admin.php?page=' . RTB_BOOKING_POST_TYPE );
	}

	/**
	 * Set the correct date filter
	 *
	 * $_POST values should always overwrite $_GET values
	 *
	 * @since 0.0.1
	 */
	public function set_date_filter( $start_date = null, $end_date = null) {

		if ( !empty( $_GET['action'] ) && $_GET['action'] == 'clear_date_filters' ) {
			$this->filter_start_date = null;
			$this->filter_end_date = null;
		}

		$this->filter_start_date = $start_date;
		$this->filter_end_date = $end_date;

		if ( $start_date === null ) {
			$this->filter_start_date = !empty( $_GET['start-date'] ) ? sanitize_text_field( $_GET['start-date'] ) : null;
			$this->filter_start_date = !empty( $_POST['start-date'] ) ? sanitize_text_field( $_POST['start-date'] ) : $this->filter_start_date;
		}

		if ( $end_date === null ) {
			$this->filter_end_date = !empty( $_GET['end-date'] ) ? sanitize_text_field( $_GET['end-date'] ) : null;
			$this->filter_end_date = !empty( $_POST['end-date'] ) ? sanitize_text_field( $_POST['end-date'] ) : $this->filter_end_date;
		}
	}

	/**
	 * Strip unwanted query vars from the query string or ensure the correct
	 * vars are passed around and those we don't want to preserve are discarded.
	 *
	 * @since 0.0.1
	 */
	public function query_string_maintenance() {

		$this->query_string = remove_query_arg( array( 'action', 'start-date', 'end-date' ) );

		if ( $this->filter_start_date !== null ) {
			$this->query_string = add_query_arg( array( 'start-date' => $this->filter_start_date ), $this->query_string );
		}

		if ( $this->filter_end_date !== null ) {
			$this->query_string = add_query_arg( array( 'end-date' => $this->filter_end_date ), $this->query_string );
		}

	}

	/**
	 * Show the time views, date filters and the search box
	 * @since 0.0.1
	 */
	public function advanced_filters() {

		// Show the schedule views (today, upcoming, all)
		if ( !empty( $_GET['schedule'] ) ) {
			$schedule = sanitize_text_field( $_GET['schedule'] );
		} else {
			$schedule = '';
		}

		// Use a custom schedule if a date range has been entered
		if ( $this->filter_start_date !== null || $this->filter_end_date !== null ) {
			$schedule = 'custom';
		}

		// Strip out existing date filters from the schedule view urls
		$schedule_query_string = remove_query_arg( array( 'schedule', 'start-date', 'end-date' ), $this->query_string );

		$views = array(
			'all'		=> sprintf( '<a href="%s"%s>%s</a>', $schedule_query_string, $schedule == '' ? ' class="current"' : '', __( 'All', RTB_TEXTDOMAIN ) ),
			'today'	=> sprintf( '<a href="%s"%s>%s</a>', add_query_arg( array( 'schedule' => 'today', 'paged' => FALSE ), $schedule_query_string ), $schedule === 'today' ? ' class="current"' : '', __( 'Today', RTB_TEXTDOMAIN ) ),
			'upcoming'	=> sprintf( '<a href="%s"%s>%s</a>', add_query_arg( array( 'schedule' => 'upcoming', 'paged' => FALSE ), remove_query_arg( array( 'order' ), $schedule_query_string ) ), $schedule === 'upcoming' ? ' class="current"' : '', __( 'Upcoming', RTB_TEXTDOMAIN ) ),
		);

		if ( $schedule == 'custom' ) {
			$start_date = !empty( $this->filter_start_date ) ? $this->filter_start_date : '*';
			$end_date = !empty( $this->filter_end_date ) ? $this->filter_end_date : '*';
			$views['custom'] = '<span class="current">' . $start_date . _x( '&mdash;', 'Separator between two dates in a date range', RTB_TEXTDOMAIN ) . $end_date . '</span>';
		}

		$views = apply_filters( 'rtn_bookings_table_views_schedule', $views );
		?>

		<div id="rtb-filters" class="clearfix">
			<ul class="subsubsub rtb-views-schedule">
				<li><?php echo join( ' | </li><li>', $views ); ?></li>
			</ul>

			<div class="date-filters">
				<label for="start-date" class="screen-reader-text"><?php _e( 'Start Date:', RTB_TEXTDOMAIN ); ?></label>
				<input type="text" id="start-date" name="start-date" class="datepicker" value="<?php echo esc_attr( $this->filter_start_date ); ?>" placeholder="<?php _e( 'Start Date', RTB_TEXTDOMAIN ); ?>" />
				<label for="end-date" class="screen-reader-text"><?php _e( 'End Date:', RTB_TEXTDOMAIN ); ?></label>
				<input type="text" id="end-date" name="end-date" class="datepicker" value="<?php echo esc_attr( $this->filter_end_date ); ?>" placeholder="<?php _e( 'End Date', RTB_TEXTDOMAIN ); ?>" />
				<input type="submit" class="button-secondary" value="<?php _e( 'Apply', RTB_TEXTDOMAIN ); ?>"/>
				<?php if( !empty( $start_date ) || !empty( $end_date ) ) : ?>
				<a href="<?php echo add_query_arg( array( 'action' => 'clear_date_filters' ) ); ?>" class="button-secondary"><?php _e( 'Clear Filter', RTB_TEXTDOMAIN ); ?></a>
				<?php endif; ?>
			</div>

			<?php if( !empty( $_GET['status'] ) ) : ?>
				<input type="hidden" name="status" value="<?php echo esc_attr( sanitize_text_field( $_GET['status'] ) ); ?>"/>
			<?php endif; ?>

			<?php
				// @todo Add support for the search box that uses more than just
				// 	the 's' argument in WP_Query. I need to search at least the
				// 	email post meta as well or this search box could be
				//	misleading for people who expect to search across all
				//	visible data
				// $this->search_box( __( 'Search', RTB_TEXTDOMAIN ), 'rtb-bookings' );
			?>

			<?php
				// @todo use a datepicker. need to bring in styles for jquery ui or use pickadate
				// wp_enqueue_script('jquery-ui-datepicker');
			?>

		</div>

<?php
	}

	/**
	 * Retrieve the view types
	 * @since 0.0.1
	 * @todo it would be nice if the default view showed all upcoming pending + confirmed bookings, but not closed ones.
	 */
	public function get_views() {

		$current = isset( $_GET['status'] ) ? $_GET['status'] : '';

		$views = array(
			'all'		=> sprintf( '<a href="%s"%s>%s</a>', remove_query_arg( array( 'status', 'paged' ), $this->query_string ), $current === 'all' || $current == '' ? ' class="current"' : '', __( 'All', RTB_TEXTDOMAIN ) . ' <span class="count">(' . $this->booking_counts['total'] . ')</span>' ),
			'pending'	=> sprintf( '<a href="%s"%s>%s</a>', add_query_arg( array( 'status' => 'pending', 'paged' => FALSE ), $this->query_string ), $current === 'pending' ? ' class="current"' : '', __( 'Pending', RTB_TEXTDOMAIN ) . ' <span class="count">(' . $this->booking_counts['pending'] . ')</span>' ),
			'confirmed'	=> sprintf( '<a href="%s"%s>%s</a>', add_query_arg( array( 'status' => 'confirmed', 'paged' => FALSE ), $this->query_string ), $current === 'confirmed' ? ' class="current"' : '', __( 'Confirmed', RTB_TEXTDOMAIN ) . ' <span class="count">(' . $this->booking_counts['confirmed'] . ')</span>' ),
			'closed'	=> sprintf( '<a href="%s"%s>%s</a>', add_query_arg( array( 'status' => 'closed', 'paged' => FALSE ), $this->query_string ), $current === 'closed' ? ' class="current"' : '', __( 'Closed', RTB_TEXTDOMAIN ) . ' <span class="count">(' . $this->booking_counts['closed'] . ')</span>' ),
			'trash' => sprintf( '<a href="%s"%s>%s</a>', add_query_arg( array( 'status' => 'trash', 'paged' => FALSE ), $this->query_string ), $current === 'trash' ? ' class="current"' : '', __( 'Trash', RTB_TEXTDOMAIN ) . ' <span class="count">(' . $this->booking_counts['trash'] . ')</span>' ),
		);

		return apply_filters( 'rtb_bookings_table_views_status', $views );
	}

	/**
	 * Generates content for a single row of the table
	 * @since 0.0.1
	 */
	function single_row( $item ) {
		static $row_class = '';
		$row_class = ( $row_class == '' ? 'alternate' : '' );

		echo '<tr class="' . esc_attr( $item->post_status );
		echo $row_class == '' ? '' : ' ' . $row_class;
		echo '">';
		$this->single_row_columns( $item );
		echo '</tr>';
	}

	/**
	 * Retrieve the table columns
	 * @since 0.0.1
	 */
	public function get_columns() {
		$columns = array(
			'cb'        => '<input type="checkbox" />', //Render a checkbox instead of text
			'date'     	=> __( 'Date', RTB_TEXTDOMAIN ),
			'party'  	=> __( 'Party', RTB_TEXTDOMAIN ),
			'name'  	=> __( 'Name', RTB_TEXTDOMAIN ),
			'email'  	=> __( 'Email', RTB_TEXTDOMAIN ),
			'phone'  	=> __( 'Phone', RTB_TEXTDOMAIN ),
			'message'  	=> __( 'Message', RTB_TEXTDOMAIN ),
			'status'  	=> __( 'Status', RTB_TEXTDOMAIN )
		);

		return apply_filters( 'rtb_bookings_table_columns', $columns );
	}

	/**
	 * Retrieve the table's sortable columns
	 * @since 0.0.1
	 */
	public function get_sortable_columns() {
		$columns = array(
			'date' 		=> array( 'date', true ),
			'name' 		=> array( 'title', true ),
		);
		return apply_filters( 'rtb_bookings_table_sortable_columns', $columns );
	}

	/**
	 * This function renders most of the columns in the list table.
	 * @since 0.0.1
	 */
	public function column_default( $booking, $column_name ) {
		switch ( $column_name ) {
			case 'date' :
				$value = $booking->format_date( $booking->date );
				break;
			case 'party' :
				$value = $booking->party;
				break;
			case 'name' :
				$value = $booking->name;
				break;
			case 'email' :
				$value = $booking->email;
				break;
			case 'phone' :
				$value = $booking->phone;
				break;
			case 'message' :
				$value = '';
				if ( trim( $booking->message ) ) {
					$value = '<a href="#" data-id="message-' . esc_attr( $booking->ID ) . '"><span class="dashicons dashicons-testimonial"></span></a>';
					$value .= '<div class="rtb-message-data">' . $booking->message . '</div>';
				}
				break;
			case 'status' :
				global $rtb_controller;
				if ( !empty( $rtb_controller->cpts->booking_statuses[$booking->post_status] ) ) {
					$value = $rtb_controller->cpts->booking_statuses[$booking->post_status]['label'];
				} elseif ( $booking->post_status == 'trash' ) {
					$value = _x( 'Trash', 'Status label for bookings put in the trash', RTB_TEXTDOMAIN );
				} else {
					$value = $booking->post_status;
				}
				break;
			default:
				$value = isset( $booking->$column_name ) ? $booking->$column_name : '';
				break;

		}

		return apply_filters( 'rtb_bookings_table_column', $value, $booking, $column_name );
	}

	/**
	 * Render the checkbox column
	 * @since 0.0.1
	 */
	public function column_cb( $booking ) {
		return sprintf(
			'<input type="checkbox" name="%1$s[]" value="%2$s" />',
			'bookings',
			$booking->ID
		);
	}

	/**
	 * Retrieve the bulk actions
	 * @since 0.0.1
	 */
	public function get_bulk_actions() {
		$actions = array(
			'delete'                 => __( 'Delete',                RTB_TEXTDOMAIN ),
			'set-status-confirmed'   => __( 'Set To Confirmed',      RTB_TEXTDOMAIN ),
			'set-status-pending'     => __( 'Set To Pending Review', RTB_TEXTDOMAIN ),
			'set-status-closed'      => __( 'Set To Closed',         RTB_TEXTDOMAIN )
		);

		return apply_filters( 'rtb_bookings_table_bulk_actions', $actions );
	}

	/**
	 * Process the bulk actions
	 * @since 0.0.1
	 */
	public function process_bulk_action() {
		$ids    = isset( $_POST['bookings'] ) ? $_POST['bookings'] : false;
		$action = isset( $_POST['action'] ) ? $_POST['action'] : false;

		if( empty( $action ) || $action == '-1' ) {
			return;
		}

		if ( !current_user_can( 'manage_bookings' ) ) {
			return;
		}

		if ( ! is_array( $ids ) ) {
			$ids = array( $ids );
		}

		global $rtb_controller;
		$results = array();
		foreach ( $ids as $id ) {
			if ( 'delete' === $action ) {
				$results[$id] = $rtb_controller->cpts->delete_booking( $id );
			}

			if ( 'set-status-confirmed' === $action ) {
				$results[$id] = $rtb_controller->cpts->update_booking_status( $id, 'confirmed' );
			}

			if ( 'set-status-pending' === $action ) {
				$results[$id] = $rtb_controller->cpts->update_booking_status( $id, 'pending' );
			}

			if ( 'set-status-closed' === $action ) {
				$results[$id] = $rtb_controller->cpts->update_booking_status( $id, 'closed' );
			}

			$results = apply_filters( 'rtb_bookings_table_bulk_action', $results, $id, $action );
		}

		if( count( $results ) ) {
			$this->results = $results;
			$this->last_action = $action;
			add_action( 'rtb_bookings_table_top', array( $this, 'admin_notice_bulk_actions' ) );
		}

	}

	/**
	 * Process quicklink actions sent out in notification emails
	 * @since 0.0.1
	 */
	public function process_quicklink_action() {
	
		if ( empty( $_REQUEST['rtb-quicklink'] ) ) {
			return;
		}

		if ( !current_user_can( 'manage_bookings' ) ) {
			return;
		}
		
		global $rtb_controller;
		
		$results = array();
		
		$id = !empty( $_REQUEST['booking'] ) ? $_REQUEST['booking'] : false;
		
		if ( $_REQUEST['rtb-quicklink'] == 'confirm' ) {
			$results[$id] = $rtb_controller->cpts->update_booking_status( $id, 'confirmed' );
			$this->last_action = 'set-status-confirmed';
		} elseif ( $_REQUEST['rtb-quicklink'] == 'close' ) {
			$results[$id] = $rtb_controller->cpts->update_booking_status( $id, 'closed' );
			$this->last_action = 'set-status-closed';
		}

		if( count( $results ) ) {
			$this->results = $results;
			add_action( 'rtb_bookings_table_top', array( $this, 'admin_notice_bulk_actions' ) );
		}
	}

	/**
	 * Display an admin notice when a bulk action is completed
	 * @since 0.0.1
	 */
	public function admin_notice_bulk_actions() {

		$success = 0;
		$failure = 0;
		foreach( $this->results as $id => $result ) {
			if ( $result === true || $result === null ) {
				$success++;
			} else {
				$failure++;
			}
		}

		if ( $success > 0 ) :
		?>

		<div id="rtb-admin-notice-bulk-<?php esc_attr( $this->last_action ); ?>" class="updated">

			<?php if ( $this->last_action == 'delete' ) : ?>
			<p><?php echo sprintf( _n( '%d booking deleted successfully.', '%d bookings deleted successfully.', $success, RTB_TEXTDOMAIN ), $success ); ?></p>

			<?php elseif ( $this->last_action == 'set-status-confirmed' ) : ?>
			<p><?php echo sprintf( _n( '%d booking confirmed.', '%d bookings confirmed.', $success, RTB_TEXTDOMAIN ), $success ); ?></p>

			<?php elseif ( $this->last_action == 'set-status-pending' ) : ?>
			<p><?php echo sprintf( _n( '%d booking set to pending.', '%d bookings set to pending.', $success, RTB_TEXTDOMAIN ), $success ); ?></p>

			<?php elseif ( $this->last_action == 'set-status-closed' ) : ?>
			<p><?php echo sprintf( _n( '%d booking closed.', '%d bookings closed.', $success, RTB_TEXTDOMAIN ), $success ); ?></p>

			<?php endif; ?>
		</div>

		<?php
		endif;

		if ( $failure > 0 ) :
		?>

		<div id="rtb-admin-notice-bulk-<?php esc_attr( $this->last_action ); ?>" class="error">
			<p><?php echo sprintf( _n( '%d booking had errors and could not be processed.', '%d bookings had errors and could not be processed.', $failure, RTB_TEXTDOMAIN ), $failure ); ?></p>
		</div>

		<?php
		endif;
	}

	/**
	 * Retrieve the counts of bookings
	 * @since 0.0.1
	 */
	public function get_booking_counts() {

		global $wpdb;

		$where = "WHERE p.post_type = '" . RTB_BOOKING_POST_TYPE . "'";

		if ( $this->filter_start_date !== null || $this->filter_end_date !== null ) {

			if ( $this->filter_start_date !== null ) {
				$start_date = new DateTime( $this->filter_start_date );
				$where .= " AND p.post_date >= '" . $start_date->format( 'Y-m-d H:i:s' ) . "'";
			}

			if ( $this->filter_end_date !== null ) {
				$end_date = new DateTime( $this->filter_end_date );
				$where .= " AND p.post_date <= '" . $end_date->format( 'Y-m-d H:i:s' ) . "'";
			}
		} elseif ( !empty( $_GET['schedule'] ) ) {

			if ( $_GET['schedule'] ==  'today' ) {
				$where .= " AND p.post_date >= '" . date( 'Y-m-d' ) . "' AND p.post_date <= '" . date( 'Y-m-d', current_time( 'timestamp' ) + 86400 ) . "'";

			} elseif ( $_GET['schedule'] ==  'upcoming' ) {
				$where .= " AND p.post_date >= '" . date( 'Y-m-d H:i:s', current_time( 'timestamp' ) - 3600 ) . "'";
			}
		}


		$query = "SELECT p.post_status,count( * ) AS num_posts
			FROM $wpdb->posts p
			$where
			GROUP BY p.post_status
		";

		$count = $wpdb->get_results( $query, ARRAY_A );

		$this->booking_counts = array();
		foreach ( get_post_stati() as $state ) {
			$this->booking_counts[$state] = 0;
		}

		$this->booking_counts['total'] = 0;
		foreach ( (array) $count as $row ) {
			$this->booking_counts[$row['post_status']] = $row['num_posts'];
			$this->booking_counts['total'] += $row['num_posts'];
		}

	}

	/**
	 * Retrieve all the data for all the bookings
	 * @since 0.0.1
	 */
	public function bookings_data() {

		$args = array(
			'post_type'			=> RTB_BOOKING_POST_TYPE,
			'posts_per_page'	=> $this->per_page,
			'paged'				=> isset( $_GET['paged'] ) ? $_GET['paged'] : 1,
			'post_status'		=> isset( $_GET['status'] ) ? $_GET['status'] : array( 'confirmed', 'pending', 'closed' ),
		);

		if ( isset( $_GET['orderby'] ) ) {
			$args['orderby'] = $_GET['orderby'];
		}

		$args['order'] = isset( $_GET['order'] ) ? $_GET['order'] : 'ASC';

		if ( $this->filter_start_date !== null || $this->filter_end_date !== null ) {

			$date_query = array();

			if ( $this->filter_start_date !== null ) {
				$date_query['after'] = $this->filter_start_date;
			}

			if ( $this->filter_end_date !== null ) {
				$date_query['before'] = $this->filter_end_date;
			}

			if ( count( $date_query ) ) {
				$args['date_query'] = $date_query;
			}

		} elseif ( isset( $_GET['schedule'] ) ) {

			if ( $_GET['schedule'] == 'today' ) {
				$today = getdate();
				$args['year'] = $today['year'];
				$args['monthnum'] = $today['mon'];
				$args['day'] = $today['mday'];
			}

			if ( $_GET['schedule'] == 'upcoming' ) {
				$args['date_query'] = array(
					array(
						'after' => '-1 hour', // show bookings that have just passed
					)
				);
				if ( empty( $_GET['order'] ) ) {
					$args['order'] = 'ASC';
				}
			}
		}

		$args = apply_filters( 'rtb_bookings_table_query_args', $args );

		// Make query
		$query = new WP_Query( $args );

		if ( $query->have_posts() ) {
			while ( $query->have_posts() ) {
				$query->the_post();

				require_once( RTB_PLUGIN_DIR . '/includes/Booking.class.php' );
				$booking = new rtbBooking();
				if ( $booking->load_post( $query->post ) ) {
					$this->bookings[] = $booking;
				}
			}
		}
	}

	/**
	 * Setup the final data for the table
	 * @since 0.0.1
	 */
	public function prepare_items() {

		$columns  = $this->get_columns();
		$hidden   = array(); // No hidden columns
		$sortable = $this->get_sortable_columns();

		$this->_column_headers = array( $columns, $hidden, $sortable );

		$this->items = $this->bookings;

		$total_items   = empty( $_GET['status'] ) ? $this->booking_counts['total'] : $this->booking_counts[$_GET['status']];

		$this->set_pagination_args( array(
				'total_items' => $total_items,
				'per_page'    => $this->per_page,
				'total_pages' => ceil( $total_items / $this->per_page )
			)
		);
	}
}
} // endif;
