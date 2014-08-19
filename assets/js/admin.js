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
