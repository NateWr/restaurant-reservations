<?php
if ( !defined( 'ABSPATH' ) ) exit;

if ( !class_exists( 'rtbNotificationEmail' ) ) {
/**
 * Class to handle an email notification for Restaurant Table Bookings
 *
 * This class extends rtbNotification and must implement the following methods:
 *	prepare_notification() - set up and validate data
 *	send_notification()
 *
 * @since 0.0.1
 */
class rtbNotificationEmail extends rtbNotification {

	/**
	 * Recipient email
	 * @since 0.0.1
	 */
	public $to_email;

	/**
	 * From email
	 * @since 0.0.1
	 */
	public $from_email;

	/**
	 * From name
	 * @since 0.0.1
	 */
	public $from_name;

	/**
	 * Email subject
	 * @since 0.0.1
	 */
	public $subject;

	/**
	 * Email message body
	 * @since 0.0.1
	 */
	public $message;

	/**
	 * Email headers
	 * @since 0.0.1
	 */
	public $headers;

	/**
	 * Prepare and validate notification data
	 *
	 * @return boolean if the data is valid and ready for transport
	 * @since 0.0.1
	 */
	public function prepare_notification() {

		$this->set_to_email();
		$this->set_from_email();
		$this->set_subject();
		$this->set_headers();
		$this->set_message();

		// @todo validate data and return false if invalid
		return true;

	}

	/**
	 * Set to email
	 * @since 0.0.1
	 */
	public function set_to_email() {

		if ( $this->target == 'user' ) {
			$this->to_email = empty( $this->booking->email ) ? null : $this->booking->email; // @todo full name + email

		} else {
			global $rtb_controller;
			$this->to_email = $rtb_controller->settings->get_setting( 'admin-email-address' );
		}

	}

	/**
	 * Set from email
	 * @since 0.0.1
	 */
	public function set_from_email() {
	
		global $rtb_controller;

		$this->from_email = $rtb_controller->settings->get_setting( 'reply-to-address' );
		$this->from_name = $rtb_controller->settings->get_setting( 'reply-to-name' );

	}

	/**
	 * Set email subject
	 * @since 0.0.1
	 * @todo different subjects for different notifications
	 */
	public function set_subject( $subject = null ) {

		$this->subject = $subject === null ? __( 'Restaurant Booking', RTB_TEXTDOMAIN ) : $subject; // @todo fallback should be something like "Booking at [restaurant name]"

	}

	/**
	 * Set email headers
	 * @since 0.0.1
	 */
	public function set_headers( $headers = null ) {

		$headers = "From: " . stripslashes_deep( html_entity_decode( $this->from_name, ENT_COMPAT, 'UTF-8' ) ) . " <" . $this->from_email . ">\r\n";
		$headers .= "Reply-To: ". $this->from_email . "\r\n";
		$headers .= "Content-Type: text/html; charset=utf-8\r\n";
		$this->headers = apply_filters( 'rtb_notification_email_headers', $headers, $this );

	}

	/**
	 * Set email message body
	 * @since 0.0.1
	 */
	public function set_message() {

		$settings = get_option( 'rtb-settings' );

		if ( $this->event == 'new_submission' ) {
			if ( $this->target == 'user' ) {
				$template = 'template-booking-user';
			} elseif ( $this->target == 'admin' ) { // @todo check if admin notifications are enabled
				$template = 'template-booking-admin';
			}

		} elseif ( $this->event == 'pending_to_confirmed' ) {
			if ( $this->target == 'user' ) {
				$template = 'template-confirmed-user';
			}

		} elseif ( $this->event == 'pending_to_closed' ) {
			if ( $this->target == 'user' ) {
				$template = 'template-rejected-user';
			}
		}

		if ( !isset( $template ) ) {
			$this->message = '';
		} else {
			$this->message = $this->process_template( $this->get_template( $template ) );
		}

	}

	/**
	 * Send notification
	 * @since 0.0.1
	 */
	public function send_notification() {
		wp_mail( $this->to_email, $this->subject, $this->message, $this->headers );
	}
}
} // endif;
