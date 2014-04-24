/* Javascript for Restaurant Reservations admin */
jQuery(document).ready(function ($) {

	// Show or hide a booking message in the bookings table
	$( '#rtb-bookings-table .column-message a' ).click( function () {
		var message_id = $(this).data( 'id' );
		if ( $(this).parent().parent().siblings( '#' + message_id ).length ) {
			$( '#' + $(this).data( 'id' ) ).fadeOut( function() {
				$(this).remove();
			});
			$(this).children( '.dashicons' ).removeClass( 'dashicons-welcome-comments' ).addClass( 'dashicons-testimonial' );
		} else {
			var row = $(this).closest( 'tr' );
			row.after( '<tr class="' + row.attr( 'class' ) + ' message-row" id="' + message_id + '"><td colspan="' + row.children( 'th, td' ).length + '">' + $(this).siblings( '.rtb-message-data' ).html() + '</td></tr>' );
			$( '#' + message_id ).fadeIn();
			$(this).children( '.dashicons' ).removeClass( 'dashicons-testimonial' ).addClass( 'dashicons-welcome-comments' );
		}
			
		return false;
	});

});
