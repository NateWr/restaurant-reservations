<?php
if ( !defined( 'ABSPATH' ) ) exit;

if ( !class_exists( 'rtbNotification' ) ) {
/**
 * Base class to handle a notification for Restaurant Table Bookings
 *
 * This class sets up the notification content and sends it when run by
 * rtbNotifications. This class should be extended for each type of
 * notification. So, there would be a rtbNotificationEmail class or a
 * rtbNotificationSMS class.
 *
 * @since 0.0.1
 */
abstract class rtbNotification {

	/**
	 * Event which should trigger this notification
	 * @since 0.0.1
	 */
	public $event;

	/**
	 * Target of the notification (who/what will receive it)
	 * @since 0.0.1
	 */
	public $target;

	/**
	 * Define the notification essentials
	 * @since 0.0.1
	 */
	public function __construct( $event, $target ) {

		$this->event = $event;
		$this->target = $target;

	}

	/**
	 * Set booking data passed from rtbNotifications
	 *
	 * @var object $booking
	 * @since 0.0.1
	 */
	public function set_booking( $booking ) {
		$this->booking = $booking;
	}

	/**
	 * Prepare and validate notification data
	 *
	 * @return boolean if the data is valid and ready for transport
	 * @since 0.0.1
	 */
	abstract public function prepare_notification();
	
	/**
	 * Process a template and insert booking details
	 * @since 0.0.1
	 */
	public function process_template( $message ) {
	
		$template_tags = array(
			'{target}'	=> $this->target,
			'{event}'	=> $this->event,
		);
		
		$template_tags = apply_filters( 'rtb_notification_template_tags', $template_tags, $this );
		
		return str_replace( array_keys( $template_tags ), array_values( $template_tags ), $message );
	
	}

	/**
	 * Send notification
	 * @since 0.0.1
	 */
	abstract public function send_notification();

}
} // endif;
