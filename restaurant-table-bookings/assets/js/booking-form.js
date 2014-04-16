/* Javascript for Restaurant Table Bookings admin */
jQuery(document).ready(function ($) {

	/**
	 * Show the message field on the booking form
	 */
	$( '.rtb-booking-form .add-message a' ).click( function() {
		$(this).hide();
		$(this).parent().siblings( '.message' ).addClass( 'message-open' );

		return false;
	});

	/**
	 * Enable datepickers on load
	 */
	if ( typeof rtb_pickadate !== 'undefined' ) {
	
		// Minimum date (true = today)
		var min = true;
		if ( rtb_pickadate.late_bookings == 1440 ) {
			min = 1;
		}
			
		$( '#rtb-date' ).pickadate({
			format: rtb_pickadate.date_format,
			min: min,
		});
		$( '#rtb-time' ).pickatime({
			format: rtb_pickadate.time_format,
		});
	}

});
