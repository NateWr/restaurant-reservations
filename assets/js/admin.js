/* Javascript for Restaurant Reservations admin */
jQuery(document).ready(function ($) {

	// Show/hide date filter in bookings list
	$( '#rtb-date-filter-link' ).click( function() {
		$( '#rtb-filters' ).toggleClass( 'date-filters-visible' );
	});

	// Add date picker to date filter in admin
	$( '#start-date, #end-date' ).each( function() {
		var input = $(this);

		input.pickadate({
			format: rtb_pickadate.date_format,
			formatSubmit: 'yyyy/mm/dd',
			hiddenName: true,

			onStart: function() {
				if ( input.val() !== '' ) {
					var date = new Date( input.val() );
					if ( Object.prototype.toString.call( date ) === "[object Date]" ) {
						this.set( 'select', date );
					}
				}
			}
		});
	});

	// Show or hide extra booking details in the bookings table
	$( '.rtb-show-details' ).click( function (e) {
		e.preventDefault();
		rtb_toggle_details_modal( true, $(this).siblings( '.rtb-details-data' ).html() );
	});

	// Register clicks on action links
	$( '#rtb-bookings-table tr .actions' ).click( function(e) {

		e.stopPropagation();

		var target = $(e.target);
		var action = target.data( 'action' );

		if ( !action ) {
			return;
		}

		var cell = target.parent().parent();

		if ( target.data( 'action' ) == 'edit' ) {
			rtb_booking_loading_spinner( true, cell );
			rtb_get_booking( target.data( 'id' ), cell );

		} else if ( target.data( 'action' ) == 'trash' ) {
			rtb_booking_loading_spinner( true, cell );
			rtb_trash_booking( target.data( 'id' ), cell );

		} else if ( target.data( 'action' ) == 'email') {
			rtb_toggle_email_modal( true, target.data( 'id'), target.data( 'email' ), target.data( 'name' ) );

		} else if ( target.data( 'action' ) == 'ban') {
			rtb_toggle_ban_modal( true, target.data( 'id'), target.data( 'email' ), target.data( 'ip' ) );
		}

		e.preventDefault();
	});

	// Show booking form modal
	$( '.add-booking' ).click( function( e ) {
		e.preventDefault();
		rtb_toggle_booking_form_modal( true );
	});

	// Show column configuration modal
	$( '.rtb-columns-button' ).click( function( e ) {
		e.preventDefault();
		rtb_toggle_column_modal( true );
	});

	/**
	 * Show/hide loading spinner when edit/delete link clicked
	 */
	function rtb_booking_loading_spinner( loading, cell ) {
		if ( loading ) {
			cell.addClass( 'loading' );
		} else {
			cell.removeClass( 'loading' );
		}
	}

	/**
	 * Modals for the admin page
	 */
	var rtb_booking_modal = $( '#rtb-booking-modal' ),
		rtb_booking_modal_fields = rtb_booking_modal.find( '#rtb-booking-form-fields' ),
		rtb_booking_modal_submit = rtb_booking_modal.find( 'button' ),
		rtb_booking_modal_cancel = rtb_booking_modal.find( '#rtb-cancel-booking-modal' ),
		rtb_booking_modal_action_status = rtb_booking_modal.find( '.action-status' ),
		rtb_email_modal = $( '#rtb-email-modal' ),
		rtb_email_modal_submit = rtb_email_modal.find( 'button' ),
		rtb_email_modal_cancel = rtb_email_modal.find( '#rtb-cancel-email-modal' ),
		rtb_email_modal_action_status = rtb_email_modal.find( '.action-status' ),
		rtb_column_modal = $( '#rtb-column-modal' ),
		rtb_column_modal_submit = rtb_column_modal.find( 'button' ),
		rtb_column_modal_cancel = rtb_column_modal.find( '#rtb-cancel-column-modal' ),
		rtb_column_modal_action_status = rtb_column_modal.find( '.action-status' ),
		rtb_details_modal = $( '#rtb-details-modal' ),
		rtb_details_modal_close = rtb_details_modal.find( '#rtb-close-details-modal' ),
		rtb_details_modal_cancel = rtb_details_modal.find( '#rtb-cancel-details-modal' ),
		rtb_booking_modal_error = $( '#rtb-error-modal' ),
		rtb_booking_modal_error_msg = rtb_booking_modal_error.find( '.rtb-error-msg' ),
		rtb_ban_modal = $( '#rtb-ban-modal' ),
		rtb_ban_modal_submit_email = rtb_ban_modal.find( '#rtb-ban-modal-email-btn' ),
		rtb_ban_modal_submit_ip = rtb_ban_modal.find( '#rtb-ban-modal-ip-btn' ),
		rtb_ban_modal_cancel = rtb_ban_modal.find( '#rtb-cancel-ban-modal' ),
		rtb_ban_modal_action_status = rtb_ban_modal.find( '.action-status' );

	/**
	 * Show or hide the booking form modal
	 */
	function rtb_toggle_booking_form_modal( show, fields, booking ) {

		if ( show ) {
			rtb_booking_modal.scrollTop( 0 ).addClass( 'is-visible' );

			if ( typeof fields !== 'undefined' ) {
				rtb_booking_modal_fields.html( fields );
				rtb_init_booking_form_modal_fields();
			}

			if ( typeof booking == 'undefined' ) {
				rtb_booking_modal_fields.find( '#rtb-post-status' ).val( 'confirmed' );
				rtb_booking_modal_submit.html( rtb_admin.strings.add_booking );
			} else {
				rtb_booking_modal_submit.html( rtb_admin.strings.edit_booking );
				rtb_booking_modal.find( 'input[name=ID]' ).val( booking.ID );
			}

			$( 'body' ).addClass( 'rtb-hide-body-scroll' );

		} else {
			rtb_booking_modal.removeClass( 'is-visible' );
			rtb_booking_modal.find( '.rtb-error' ).remove();
			rtb_booking_modal.find( '.notifications-description' ).removeClass( 'is-visible' );
			rtb_booking_modal_action_status.removeClass( 'is-visible' );
			rtb_reset_booking_form_modal_fields();
			rtb_booking_modal_submit.removeData( 'id' );
			rtb_booking_modal_submit.prop( 'disabled', false );
			rtb_booking_modal_cancel.prop( 'disabled', false );
			rtb_booking_modal.find( 'input[name=ID]' ).val( '' );

			$( 'body' ).removeClass( 'rtb-hide-body-scroll' );
		}
	}

	/**
	 * Show or hide the booking form error modal
	 */
	function rtb_toggle_booking_form_error_modal( show, msg ) {

		if ( show ) {
			rtb_booking_modal_error_msg.html( msg );
			rtb_booking_modal_error.addClass( 'is-visible' );

		} else {
			rtb_booking_modal_error.removeClass( 'is-visible' );
		}
	}

	/**
	 * Show or hide the email form modal
	 */
	function rtb_toggle_email_modal( show, id, email, name ) {

		if ( show ) {
			rtb_email_modal.scrollTop( 0 ).addClass( 'is-visible' );
			rtb_email_modal.find( 'input[name=ID]' ).val( id );
			rtb_email_modal.find( 'input[name=email]' ).val( email );
			rtb_email_modal.find( 'input[name=name]' ).val( name );
			rtb_email_modal.find( '.rtb-email-to' ).html( name + ' &lt;' + email + '&gt;' );

			$( 'body' ).addClass( 'rtb-hide-body-scroll' );

		} else {
			rtb_email_modal.removeClass( 'is-visible' );
			rtb_email_modal.find( '.rtb-email-to' ).html( '' );
			rtb_email_modal.find( 'textarea, input[type="hidden"], input[type="text"]' ).val( '' );
			rtb_email_modal_submit.prop( 'disabled', false );
			rtb_email_modal_cancel.prop( 'disabled', false );

			$( 'body' ).removeClass( 'rtb-hide-body-scroll' );
		}
	}

	/**
	 * Show or hide the column configuration modal
	 */
	function rtb_toggle_column_modal( show ) {

		if ( show ) {
			rtb_column_modal.scrollTop( 0 ).addClass( 'is-visible' );
			$( 'body' ).addClass( 'rtb-hide-body-scroll' );

		} else {
			rtb_column_modal.removeClass( 'is-visible' );
			$( 'body' ).removeClass( 'rtb-hide-body-scroll' );
		}
	}

	/**
	 * Show or hide the booking details modal
	 */
	function rtb_toggle_details_modal( show, content ) {

		if ( show ) {
			rtb_details_modal.addClass( 'is-visible' ).scrollTop( 0 )
				.find( '.rtb-details-data' ).html( content );
			$( 'body' ).addClass( 'rtb-hide-body-scroll' );
			rtb_details_modal.find( '.actions' ).click( function(e) {
				var target = $( e.target );
				rtb_toggle_details_modal( false );
				if ( target.data( 'action' ) == 'email') {
					rtb_toggle_email_modal( true, target.data( 'id'), target.data( 'email' ), target.data( 'name' ) );
				} else if ( target.data( 'action' ) == 'ban') {
					rtb_toggle_ban_modal( true, target.data( 'id'), target.data( 'email' ), target.data( 'ip' ) );
				}
			});

		} else {
			rtb_details_modal.removeClass( 'is-visible' );
			$( 'body' ).removeClass( 'rtb-hide-body-scroll' );
			setTimeout( function() {
				rtb_details_modal.find( '.rtb-details-data' ).empty();
			}, 300 );
		}
	}

	/**
	 * Show or hide the ban ip/email form modal
	 */
	function rtb_toggle_ban_modal( show, id, email, ip ) {

		if ( show ) {
			rtb_ban_modal.scrollTop( 0 ).addClass( 'is-visible' );
			rtb_ban_modal.find( '#rtb-ban-modal-email' ).text( email );
			rtb_ban_modal.find( '#rtb-ban-modal-ip' ).text( ip );
			if ( rtb_admin.banned_emails.indexOf( email ) > -1 ) {
				rtb_ban_modal_submit_email.prop( 'disabled', true );
			}
			if ( rtb_admin.banned_ips.indexOf( ip ) > -1 ) {
				rtb_ban_modal_submit_ip.prop( 'disabled', true );
			}

			$( 'body' ).addClass( 'rtb-hide-body-scroll' );

		} else {
			rtb_ban_modal.removeClass( 'is-visible' );
			rtb_ban_modal.find( '#rtb-ban-modal-email' ).text( '' );
			rtb_ban_modal.find( '#rtb-ban-modal-ip' ).text( '' );
			rtb_ban_modal_submit_email.prop( 'disabled', false );
			rtb_ban_modal_submit_ip.prop( 'disabled', false );
			rtb_ban_modal_cancel.prop( 'disabled', false );

			$( 'body' ).removeClass( 'rtb-hide-body-scroll' );
		}
	}

	/**
	 * Initialize form field events
	 */
	function rtb_init_booking_form_modal_fields() {

		// Run init on the form
		rtb_booking_form.init();

		// Show full description for notifications toggle
		rtb_booking_modal_fields.find( '.rtb-description-prompt' ).click( function() {
			$(this).parent().siblings( '.rtb-description' ).addClass( 'is-visible' );
		});
	}

	/**
	 * Reset booking form fields
	 */
	function rtb_reset_booking_form_modal_fields() {
		rtb_booking_modal_fields.find( 'input,select, textarea' ).not( 'input[type="checkbox"],input[type="radio"]' ).val( '' );
		rtb_booking_modal_fields.find( 'input[name=rtb-notifications]' ).removeAttr( 'checked' );
	}

	/**
	 * Retrieve booking from the database
	 */
	function rtb_get_booking( id, cell ) {

		var params = {};

		params.action = 'rtb-admin-booking-modal';
		params.nonce = rtb_admin.nonce;
		params.booking = {
			'ID':	id
		};

		var data = $.param( params );

		var jqhxr = $.get( ajaxurl, data, function( r ) {

			if ( r.success ) {
				rtb_toggle_booking_form_modal( true, r.data.fields, r.data.booking );

			} else {

				if ( typeof r.data.error == 'undefined' ) {
					rtb_toggle_booking_form_error_modal( true, rtb_admin.strings.error_unspecified );
				} else {
					rtb_toggle_booking_form_error_modal( true, r.data.msg );
				}
			}

			rtb_booking_loading_spinner( false, cell );
		});
	}

	/**
	 * Trash booking
	 */
	function rtb_trash_booking( id, cell ) {

		var params = {};

		params.action = 'rtb-admin-trash-booking';
		params.nonce = rtb_admin.nonce;
		params.booking = id;

		var data = $.param( params );

		var jqhxr = $.post( ajaxurl, data, function( r ) {

			if ( r.success ) {

				cell.parent().fadeOut( 500, function() {
					$(this).remove();
				});

				var trash_count_el = $( '#rtb-bookings-table .subsubsub .trash .count' );
				var trash_count = parseInt( trash_count_el.html().match(/\d+/), 10 ) + 1;
				trash_count_el.html( '(' + trash_count + ')' );

			} else {

				if ( typeof r.data == 'undefined' || typeof r.data.error == 'undefined' ) {
					rtb_toggle_booking_form_error_modal( true, rtb_admin.strings.error_unspecified );
				} else {
					rtb_toggle_booking_form_error_modal( true, r.data.msg );
				}
			}

			rtb_booking_loading_spinner( false, cell );
		});

	}

	/**
	 * Show the appropriate result status icon
	 */
	function rtb_show_action_status( el, status ) {

		el.find( 'span' ).hide();

		if ( status === true ) {
			el.find( '.success' ).show();
		} else if ( status === false ) {
			el.find( '.error' ).show();
		} else {
			el.find( '.spinner' ).show();
		}
	}

	// Reset the forms on load
	// This fixes a strange bug in Firefox where disabled buttons would
	// persist after the page refreshed. I'm guessing its a cache issue
	// but this will just reset everything again
	rtb_toggle_booking_form_modal( false );
	rtb_toggle_email_modal( false );
	rtb_toggle_column_modal( false );

	// Close booking form modal when background or cancel button is clicked
	rtb_booking_modal.click( function(e) {
		if ( $(e.target).is( rtb_booking_modal ) ) {
			rtb_toggle_booking_form_modal( false );
		}

		if ( $(e.target).is( rtb_booking_modal_cancel ) && rtb_booking_modal_cancel.prop( 'disabled' ) === false ) {
			rtb_toggle_booking_form_modal( false );
		}
	});

	// Close email modal when background or cancel button is clicked
	rtb_email_modal.click( function(e) {
		if ( $(e.target).is( rtb_email_modal ) ) {
			rtb_toggle_email_modal( false );
		}

		if ( $(e.target).is( rtb_email_modal_cancel ) && rtb_email_modal_cancel.prop( 'disabled' ) === false ) {
			rtb_toggle_email_modal( false );
		}
	});

	// Close column modal when background or cancel button is clicked
	rtb_column_modal.click( function(e) {
		if ( $(e.target).is( rtb_column_modal ) ) {
			rtb_toggle_column_modal( false );
		}

		if ( $(e.target).is( rtb_column_modal_cancel ) && rtb_column_modal_cancel.prop( 'disabled' ) !== true ) {
			rtb_toggle_column_modal( false );
		}
	});

	// Close details modal when background or cancel button is clicked
	rtb_details_modal.click( function(e) {
		if ( $(e.target).is( rtb_details_modal ) ) {
			rtb_toggle_details_modal( false );
		}

		if ( $(e.target).is( rtb_details_modal_cancel ) ) {
			rtb_toggle_details_modal( false );
		}
	});

	// Close booking form error modal when background or cancel button is clicked
	rtb_booking_modal_error.click( function(e) {
		if ( $(e.target).is( rtb_booking_modal_error ) || $(e.target).is( rtb_booking_modal_error.find( 'a.button' ) ) ) {
			rtb_toggle_booking_form_error_modal( false );
		}
	});

	// Close ban modal when background or cancel button is clicked
	rtb_ban_modal.click( function(e) {
		if ( $(e.target).is( rtb_ban_modal ) ) {
			rtb_toggle_ban_modal( false );
		}

		if ( $(e.target).is( rtb_ban_modal_cancel ) && rtb_ban_modal_cancel.prop( 'disabled' ) !== true ) {
			rtb_toggle_ban_modal( false );
		}
	});

	// Close modals when ESC is keyed
	$(document).keyup( function(e) {
		if ( e.which == '27' ) {
			rtb_toggle_booking_form_modal( false );
			rtb_toggle_email_modal( false );
			rtb_toggle_column_modal( false );
			rtb_toggle_details_modal( false );
			rtb_toggle_booking_form_error_modal( false );
			rtb_toggle_ban_modal( false );
		}
	});

	// Submit booking form modal
	rtb_booking_modal_submit.click( function(e) {

		e.preventDefault();
		e.stopPropagation();

		if ( $(this).prop( 'disabled' ) === true ) {
			return;
		}

		// Loading
		rtb_booking_modal_submit.prop( 'disabled', true );
		rtb_booking_modal_cancel.prop( 'disabled', true );
		rtb_booking_modal_action_status.addClass( 'is-visible' );
		rtb_show_action_status( rtb_booking_modal_action_status, 'loading' );

		var params = {};

		params.action = 'rtb-admin-booking-modal';
		params.nonce = rtb_admin.nonce;
		params.booking = rtb_booking_modal.find( 'form' ).serializeArray();

		var data = $.param( params );

		var jqhxr = $.post( ajaxurl, data, function( r ) {

			if ( r.success ) {

				// Refresh the page so that the new details are visible
				window.location.reload();

			} else {

				// Validation failed
				if ( r.data.error == 'invalid_booking_data' ) {

					// Replace form fields with HTML returned
					rtb_booking_modal_fields.html( r.data.fields );
					rtb_init_booking_form_modal_fields();

				// Logged out
				} else if ( r.data.error == 'loggedout' ) {
					rtb_booking_modal_fields.after( '<div class="rtb-error">' + r.data.msg + '</div>' );

				// Unspecified error
				} else {
					rtb_booking_modal_fields.after( '<div class="rtb-error">' + rtb_admin.strings.error_unspecified + '</div>' );
				}

				rtb_booking_modal_cancel.prop( 'disabled', false );
				rtb_booking_modal_submit.prop( 'disabled', false );
			}

			rtb_show_action_status( rtb_booking_modal_action_status, r.success );

			// Hide result status icon after a few seconds
			setTimeout( function() {
				rtb_booking_modal.find( '.action-status' ).removeClass( 'is-visible' );
			}, 4000 );
		});
	});

	// Submit email form modal
	rtb_email_modal_submit.click( function(e) {

		e.preventDefault();
		e.stopPropagation();

		if ( $(this).prop( 'disabled' ) === true ) {
			return;
		}

		// Loading
		rtb_email_modal_submit.prop( 'disabled', true );
		rtb_email_modal_cancel.prop( 'disabled', true );
		rtb_email_modal_action_status.addClass( 'is-visible' );
		rtb_show_action_status( rtb_email_modal_action_status, 'loading' );

		var params = {};

		params.action = 'rtb-admin-email-modal';
		params.nonce = rtb_admin.nonce;
		params.email = rtb_email_modal.find( 'form' ).serializeArray();

		var data = $.param( params );

		var jqhxr = $.post( ajaxurl, data, function( r ) {

			if ( r.success ) {

				rtb_show_action_status( rtb_email_modal_action_status, r.success );

				// Hide result status icon after a few seconds
				setTimeout( function() {
					rtb_email_modal.find( '.action-status' ).removeClass( 'is-visible' );
					rtb_toggle_email_modal( false );
				}, 1000 );

			} else {

				if ( typeof r.data == 'undefined' || typeof r.data.error == 'undefined' ) {
					rtb_toggle_booking_form_error_modal( true, rtb_admin.strings.error_unspecified );
				} else {
					rtb_toggle_booking_form_error_modal( true, r.data.msg );
				}

				rtb_email_modal_cancel.prop( 'disabled', false );
				rtb_email_modal_submit.prop( 'disabled', false );

				rtb_show_action_status( rtb_email_modal_action_status, false );

				// Hide result status icon after a few seconds
				setTimeout( function() {
					rtb_email_modal.find( '.action-status' ).removeClass( 'is-visible' );
				}, 4000 );
			}
		});
	});

	// Submit column configuration modal
	rtb_column_modal_submit.click( function(e) {

		e.preventDefault();
		e.stopPropagation();

		if ( $(this).prop( 'disabled' ) === true ) {
			return;
		}

		// Loading
		rtb_column_modal_submit.prop( 'disabled', true );
		rtb_column_modal_cancel.prop( 'disabled', true );
		rtb_column_modal_action_status.addClass( 'is-visible' );
		rtb_show_action_status( rtb_column_modal_action_status, 'loading' );

		var params = {};

		params.action = 'rtb-admin-column-modal';
		params.nonce = rtb_admin.nonce;

		params.columns = [];
		rtb_column_modal.find( 'input[name="rtb-columns-config"]:checked' ).each( function() {
			params.columns.push( $(this).val() );
		});

		var data = $.param( params );

		var jqhxr = $.post( ajaxurl, data, function( r ) {

			if ( r.success ) {

				// Refresh the page so that the new details are visible
				window.location.reload();

			} else {

				if ( typeof r.data == 'undefined' || typeof r.data.error == 'undefined' ) {
					rtb_toggle_booking_form_error_modal( true, rtb_admin.strings.error_unspecified );
				} else {
					rtb_toggle_booking_form_error_modal( true, r.data.msg );
				}

				rtb_column_modal_cancel.prop( 'disabled', false );
				rtb_column_modal_submit.prop( 'disabled', false );
			}

			rtb_show_action_status( rtb_column_modal_action_status, r.success );

			// Hide result status icon after a few seconds
			setTimeout( function() {
				rtb_column_modal.find( '.action-status' ).removeClass( 'is-visible' );
			}, 4000 );
		});
	});

	// Shared function for banning emails and IPs
	function rtb_ban_modal_submit( e, type ) {

		e.preventDefault();
		e.stopPropagation();

		if ( $(this).prop( 'disabled' ) === true ) {
			return;
		}

		// Loading
		if ( type === 'email' ) {
			rtb_ban_modal_submit_email.prop( 'disabled', true );
		} else if ( type === 'ip' ) {
			rtb_ban_modal_submit_ip.prop( 'disabled', true );
		}
		rtb_ban_modal_cancel.prop( 'disabled', true );
		rtb_ban_modal_action_status.addClass( 'is-visible' );
		rtb_show_action_status( rtb_ban_modal_action_status, 'loading' );

		var params = {};

		params.action = 'rtb-admin-ban-modal';
		params.nonce = rtb_admin.nonce;
		if ( type === 'email' ) {
			params.email = rtb_ban_modal.find( '#rtb-ban-modal-email' ).text();
		} else if ( type === 'ip' ) {
			params.ip = rtb_ban_modal.find( '#rtb-ban-modal-ip' ).text();
		}

		var data = $.param( params );

		var jqhxr = $.post( ajaxurl, data, function( r ) {

			if ( r.success ) {

				if ( type === 'email' ) {
					rtb_admin.banned_emails.push( params.email );
				} else if ( type === 'ip' ) {
					rtb_admin.banned_ips.push( params.ip );
				}

				rtb_show_action_status( rtb_ban_modal_action_status, r.success );

				// Hide result status icon after a few seconds
				setTimeout( function() {
					rtb_ban_modal.find( '.action-status' ).removeClass( 'is-visible' );
				}, 1000 );

			} else {

				if ( typeof r.data == 'undefined' || typeof r.data.error == 'undefined' ) {
					rtb_toggle_booking_form_error_modal( true, rtb_admin.strings.error_unspecified );
				} else {
					rtb_toggle_booking_form_error_modal( true, r.data.msg );
				}

				rtb_ban_modal_cancel.prop( 'disabled', false );
				if ( type === 'email' ) {
					rtb_ban_modal_submit_email.prop( 'disabled', false );
				} else if ( type === 'ip' ) {
					rtb_ban_modal_submit_ip.prop( 'disabled', false );
				}

				rtb_show_action_status( rtb_ban_modal_action_status, false );

				// Hide result status icon after a few seconds
				setTimeout( function() {
					rtb_ban_modal.find( '.action-status' ).removeClass( 'is-visible' );
				}, 4000 );
			}
		});
	}

	// Submit ban email form modal
	rtb_ban_modal_submit_email.click( function(e) {
		rtb_ban_modal_submit( e, 'email' );
	});

	// Submit ban ip form modal
	rtb_ban_modal_submit_ip.click( function(e) {
		rtb_ban_modal_submit( e, 'ip' );
	});

});
