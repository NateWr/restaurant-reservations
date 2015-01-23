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

	// Register clicks on edit/delete links
	$( '#rtb-bookings-table .column-date .actions' ).click( function(e) {

		var target = $(e.target);
		var cell = target.parent().parent();

		rtb_booking_loading_spinner( true, cell );

		if ( target.data( 'action' ) == 'edit' ) {
			rtb_get_booking( target.data( 'id' ), cell );

		} else if ( target.data( 'action' ) == 'trash' ) {
			rtb_trash_booking( target.data( 'id' ), cell );
		}

		e.preventDefault();
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
	 * Modal to add/edit bookings from the admin
	 */
	var rtb_booking_modal = $( '#rtb-booking-modal' );
	var rtb_booking_modal_fields = rtb_booking_modal.find( '#rtb-booking-form-fields' );
	var rtb_booking_modal_submit = rtb_booking_modal.find( 'button' );
	var rtb_booking_modal_cancel = rtb_booking_modal.find( '#rtb-cancel-booking-modal' );
	var rtb_booking_modal_action_status = rtb_booking_modal.find( '.action-status' );
	var rtb_booking_modal_error = $( '#rtb-error-modal' );
	var rtb_booking_modal_error_msg = rtb_booking_modal_error.find( '.rtb-error-msg' );

	/**
	 * Show or hide the booking form modal
	 */
	function rtb_toggle_booking_form_modal( show, fields, booking ) {

		if ( show ) {
			rtb_booking_modal.scrollTop( 0 ).addClass( 'is-visible' );

			if ( typeof fields !== 'undefined' ) {
				rtb_booking_modal_fields.html( fields );
				rtb_booking_form.init();
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
	 * Initialize form field events
	 */
	function rtb_init_booking_form_modal_fields() {

		// Show full description for notifications toggle
		rtb_booking_modal_fields.find( '.rtb-description-prompt' ).click( function() {
			$(this).parent().siblings( '.rtb-description' ).addClass( 'is-visible' );
		});
	}

	/**
	 * Reset booking form fields
	 */
	function rtb_reset_booking_form_modal_fields() {
		rtb_booking_modal_fields.find( 'input, select, textarea' ).val( '' );
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
	function rtb_show_action_status( status ) {

		rtb_booking_modal_action_status.find( 'span' ).hide();

		if ( status === true ) {
			rtb_booking_modal_action_status.find( '.success' ).show();
		} else if ( status === false ) {
			rtb_booking_modal_action_status.find( '.error' ).show();
		} else {
			rtb_booking_modal_action_status.find( '.spinner' ).show();
		}
	}

	// Initialize form field events on load
	rtb_init_booking_form_modal_fields();

	// Reset the form on load
	// This fixes a strange bug in Firefox where disabled buttons would
	// persist after the page refreshed. I'm guessing its a cache issue
	// but this will just reset everything again
	rtb_toggle_booking_form_modal( false );

	// Show booking form modal
	$( '.add-booking' ).click( function() {
		rtb_toggle_booking_form_modal( true );
	});

	// Close booking form modal when background or cancel button is clicked
	rtb_booking_modal.click( function(e) {
		if ( $(e.target).is( rtb_booking_modal ) ) {
			rtb_toggle_booking_form_modal( false );
		}

		if ( $(e.target).is( rtb_booking_modal_cancel ) && rtb_booking_modal_cancel.prop( 'disabled' ) === false ) {
			rtb_toggle_booking_form_modal( false );
		}
	});

	// Close booking form modal and error modal when ESC is keyed
	$(document).keyup( function(e) {
		if ( e.which == '27' ) {
			rtb_toggle_booking_form_modal( false );
			rtb_toggle_booking_form_error_modal( false );
		}
	});

	// Close booking form error modal when background or cancel button is clicked
	rtb_booking_modal_error.click( function(e) {
		if ( $(e.target).is( rtb_booking_modal_error ) || $(e.target).is( rtb_booking_modal_error.find( 'a.button' ) ) ) {
			rtb_toggle_booking_form_error_modal( false );
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
		rtb_show_action_status( 'loading' );

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
					rtb_booking_form.init();
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

			rtb_show_action_status( r.success );

			// Hide result status icon after a few seconds
			setTimeout( function() {
				rtb_booking_modal.find( '.action-status' ).removeClass( 'is-visible' );
			}, 4000 );
		});
	});

	// Show the addons
	if ( $( '#rtb-addons' ).length ) {

		var rtbAddons = {

			el: $( '#rtb-addons' ),

			load: function() {

				var params = {
					action: 'rtb-addons',
					nonce: rtb_addons.nonce
				};

				var data = $.param( params );

				// Send Ajax request
				var jqxhr = $.post( ajaxurl, data, function( r ) {

					rtbAddons.el.find( '.rtb-loading' ).fadeOut( '250', function() {
						if ( r.success ) {
							rtbAddons.showAddons( r );
						} else {
							rtbAddons.showError( r );
						}
					});


				});
			},

			showAddons: function( r ) {

				if ( typeof r.data == 'undefined' || !r.data.length ) {
					rtbAddons.showError();
					return false;
				}

				for( var i in r.data ) {
					rtbAddons.el.append( rtbAddons.getAddonHTML( r.data[i] ) );
					rtbAddons.el.find( '.addon.' + r.data[i].id ).fadeIn();
				}
			},

			showError: function( r ) {

				if ( typeof r.data == 'undefined' || typeof r.data.msg == 'undefined' ) {
					rtbAddons.el.html( '<span class="error">' + rtb_addons.strings.error_unknown + '</span>' );
				} else {
					rtbAddons.el.html( '<span class="error">' + r.data.msg + '</span>' );
				}

			},

			getAddonHTML: function( addon ) {

				if ( typeof addon.id === 'undefined' && typeof addon.title === 'undefined' ) {
					return;
				}

				var html = '<div class="addon ' + addon.id + '">';

				if ( typeof addon.url !== 'undefined' && typeof addon.img !== 'undefined' ) {
					html += '<a href="' + addon.url + '"><img src="' + addon.img + '"></a>';
				} else if ( typeof addon.img !== 'undefind' ) {
					html += '<img src="' + addon.img + '">';
				}

				html += '<h3>' + addon.title + '</h3>';

				html += '<div class="details">';

				if ( typeof addon.description !== 'undefined' ) {
					html += '<div class="description">' + addon.description + '</div>';
				}

				if ( typeof addon.status !== 'undefined' ) {

					html += '<div class="action">';

					if ( addon.status === 'released' && typeof addon.url !== 'undefined' ) {
						html += '<a href="' + addon.url + '" class="button button-primary" target="_blank">';

						if ( typeof addon.price !== 'undefined' && addon.price.length ) {
							html += rtb_addons.strings.learn_more;
						} else {
							html += rtb_addons.strings.free;
						}

						html += '</a>';

					} else if ( addon.status === 'installed' ) {
						html += '<span class="installed">' + rtb_addons.strings.installed + '</span>';

					} else {
						html += '<span class="soon">' + rtb_addons.strings.coming_soon + '</span>';
					}

					html += '</div>'; // .action
				}

				html += '</div>'; // .details

				html += '</div>'; // .addon

				return html;
			}
		};

		rtbAddons.load();
	}

});
