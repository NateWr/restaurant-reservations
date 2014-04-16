/* Javascript for Restaurant Table Bookings admin */
jQuery(document).ready(function ($) {

	// Show message field in booking form
	$( '.rtb-booking-form .add-message a' ).click( function() {
		$(this).hide();
		$(this).parent().siblings( '.message' ).addClass( 'message-open' );
		
		return false;
	});

});
