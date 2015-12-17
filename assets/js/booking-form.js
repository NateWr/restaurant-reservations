/* Javascript for Restaurant Reservations booking form */

var rtb_booking_form = rtb_booking_form || {};

jQuery(document).ready(function ($) {

	/**
	 * Initialize the booking form when loaded
	 */
	rtb_booking_form.init = function() {

		// Scroll to the first error message on the booking form
		if ( $( '.rtb-booking-form .rtb-error' ).length ) {
			$('html, body').animate({
				scrollTop: $( '.rtb-booking-form .rtb-error' ).first().offset().top + -40
			}, 500);
		}

		// Show the message field on the booking form
		$( '.rtb-booking-form .add-message a' ).click( function() {
			$(this).hide();
			$(this).parent().siblings( '.message' ).addClass( 'message-open' )
				.find( 'label' ).focus();

			return false;
		});

		// Show the message field on load if not empty
		if ( $.trim( $( '.rtb-booking-form .message textarea' ).val() ) ) {
			$( '.rtb-booking-form .add-message a' ).trigger( 'click' );
		}

		// Enable datepickers on load
		if ( typeof rtb_pickadate !== 'undefined' ) {

			// Declare datepicker
			var date_input = $( '#rtb-date' ).pickadate({
				format: rtb_pickadate.date_format,
				formatSubmit: 'yyyy/mm/dd',
				hiddenName: true,
				min: true,
				container: 'body',

				// Select the value when loaded if a value has been set
				onStart: function() {
					if ( $( '#rtb-date' ).val()	!== '' ) {
						var date = new Date( $( '#rtb-date' ).val() );
						if ( Object.prototype.toString.call( date ) === "[object Date]" ) {
							this.set( 'select', date );
						}
					}
				}
			});

			// Declare timepicker
			var time_input = $( '#rtb-time' ).pickatime({
				format: rtb_pickadate.time_format,
				formatSubmit: 'h:i A',
				hiddenName: true,
				interval: parseInt( rtb_pickadate.time_interval, 10 ),
				container: 'body',

				// Select the value when loaded if a value has been set
				onStart: function() {
					if ( $( '#rtb-time' ).val()	!== '' ) {
						var today = new Date();
						var today_date = today.getFullYear() + '/' + ( today.getMonth() + 1 ) + '/' + today.getDate();
						var time = new Date( today_date + ' ' + $( '#rtb-time' ).val() );
						if ( Object.prototype.toString.call( time ) === "[object Date]" ) {
							this.set( 'select', time );
						}
					}
				}
			});

			rtb_booking_form.datepicker = date_input.pickadate( 'picker' );
			rtb_booking_form.timepicker = time_input.pickatime( 'picker' );

			// We need to check both to support different jQuery versions loaded
			// by older versions of WordPress. In jQuery v1.10.2, the property
			// is undefined. But in v1.11.3 it's set to null.
			if ( rtb_booking_form.datepicker === null || typeof rtb_booking_form.datepicker == 'undefined' ) {
				return;
			}

			// Pass conditional configuration parameters
			if ( rtb_pickadate.disable_dates.length ) {

				// Update weekday dates if start of the week has been modified
				var disable_dates = jQuery.extend( true, [], rtb_pickadate.disable_dates );
				if ( typeof rtb_booking_form.datepicker.component.settings.firstDay == 'number' ) {
					var weekday_num = 0;
					for ( var disable_key in rtb_pickadate.disable_dates ) {
						if ( typeof rtb_pickadate.disable_dates[disable_key] == 'number' ) {
							weekday_num = rtb_pickadate.disable_dates[disable_key] - rtb_booking_form.datepicker.component.settings.firstDay;
							if ( weekday_num < 1 ) {
								weekday_num = 7;
							}
							disable_dates[disable_key] =  weekday_num;
						}
					}
				}

				rtb_booking_form.datepicker.set( 'disable', disable_dates );
			}

			rtb_pickadate.late_bookings = parseInt( rtb_pickadate.late_bookings, 10 );
			if ( rtb_pickadate.late_bookings >= 1440 ) {
				var min = Math.floor( rtb_pickadate.late_bookings / 1440 );
				rtb_booking_form.datepicker.set( 'min', min );
			}

			// If no date has been set, select today's date if it's a valid
			// date. User may opt not to do this in the settings.
			if ( $( '#rtb-date' ).val() === '' && !$( '.rtb-booking-form .date .rtb-error' ).length ) {

				if ( rtb_pickadate.date_onload == 'soonest' ) {
					rtb_booking_form.datepicker.set( 'select', new Date() );
				} else if ( rtb_pickadate.date_onload !== 'empty' ) {
					var dateToVerify = rtb_booking_form.datepicker.component.create( new Date() );
					var isDisabled = rtb_booking_form.datepicker.component.disabled( dateToVerify );
					if ( !isDisabled ) {
						rtb_booking_form.datepicker.set( 'select', dateToVerify );
					}
				}
			}

			// Update timepicker on pageload and whenever the datepicker is closed
			rtb_booking_form.update_timepicker_range();
			rtb_booking_form.datepicker.on( {
				close: function() {
					rtb_booking_form.update_timepicker_range();
				}
			});
		}
	};

	/**
	 * Update the timepicker's range based on the currently selected date
	 */
	rtb_booking_form.update_timepicker_range = function() {

		// Reset enabled/disabled rules on this timepicker
		rtb_booking_form.timepicker.set( 'enable', false );
		rtb_booking_form.timepicker.set( 'disable', false );

		if ( rtb_booking_form.datepicker.get() === '' ) {
			rtb_booking_form.timepicker.set( 'disable', true );
			return;
		}

		selected_date = new Date( rtb_booking_form.datepicker.get( 'select', 'yyyy/mm/dd' ) );
		var selected_date_year = selected_date.getFullYear();
		var selected_date_month = selected_date.getMonth();
		var selected_date_date = selected_date.getDate();

		// Declaring the first element true inverts the timepicker settings. All
		// times subsequently declared are valid. Any time that doesn't fall
		// within those declarations is invalid.
		// See: http://amsul.ca/pickadate.js/time.htm#disable-times-all
		var valid_times = [ rtb_booking_form.get_outer_time_range() ];

		// Check if this date is an exception to the rules
		if ( typeof rtb_pickadate.schedule_closed != 'undefined' ) {

			var excp_date = [];
			var excp_start_date = [];
			var excp_start_time = [];
			var excp_end_date = [];
			var excp_end_time = [];
			for ( var closed_key in rtb_pickadate.schedule_closed ) {

				excp_date = new Date( rtb_pickadate.schedule_closed[closed_key].date );
				if ( excp_date.getFullYear() == selected_date_year &&
						excp_date.getMonth() == selected_date_month &&
						excp_date.getDate() == selected_date_date
						) {

					// Closed all day
					if ( typeof rtb_pickadate.schedule_closed[closed_key].time == 'undefined' ) {
						rtb_booking_form.timepicker.set( 'disable', [ true ] );

						return;
					}

					if ( typeof rtb_pickadate.schedule_closed[closed_key].time.start !== 'undefined' ) {
						excp_start_date = new Date( '1 January 2000 ' + rtb_pickadate.schedule_closed[closed_key].time.start );
						excp_start_time = [ excp_start_date.getHours(), excp_start_date.getMinutes() ];
					} else {
						excp_start_time = [ 0, 0 ]; // Start of the day
					}

					if ( typeof rtb_pickadate.schedule_closed[closed_key].time.end !== 'undefined' ) {
						excp_end_date = new Date( '1 January 2000 ' + rtb_pickadate.schedule_closed[closed_key].time.end );
						excp_end_time = [ excp_end_date.getHours(), excp_end_date.getMinutes() ];
					} else {
						excp_end_time = [ 24, 0 ]; // End of the day
					}

					valid_times.push( { from: excp_start_time, to: excp_end_time, inverted: true } );
				}
			}

			excp_date = excp_start_date = excp_start_time = excp_end_date = excp_end_time = null;

			// Exit early if this date is an exception
			if ( valid_times.length > 1 ) {
				rtb_booking_form.timepicker.set( 'disable', valid_times );

				return;
			}
		}

		// Get any rules which apply to this weekday
		if ( typeof rtb_pickadate.schedule_open != 'undefined' ) {

			var selected_date_weekday = selected_date.getDay();

			var weekdays = {
				sunday: 0,
				monday: 1,
				tuesday: 2,
				wednesday: 3,
				thursday: 4,
				friday: 5,
				saturday: 6,
			};

			var rule_start_date = [];
			var rule_start_time = [];
			var rule_end_date = [];
			var rule_end_time = [];
			for ( var open_key in rtb_pickadate.schedule_open ) {

				if ( typeof rtb_pickadate.schedule_open[open_key].weekdays !== 'undefined' ) {
					for ( var weekdays_key in rtb_pickadate.schedule_open[open_key].weekdays ) {
						if ( weekdays[weekdays_key] == selected_date_weekday ) {

							// Closed all day
							if ( typeof rtb_pickadate.schedule_open[open_key].time == 'undefined' ) {
								rtb_booking_form.timepicker.set( 'disable', [ true ] );

								return;
							}

							if ( typeof rtb_pickadate.schedule_open[open_key].time.start !== 'undefined' ) {
								rule_start_date = new Date( '1 January 2000 ' + rtb_pickadate.schedule_open[open_key].time.start );
								rule_start_time = [ rule_start_date.getHours(), rule_start_date.getMinutes() ];
							} else {
								rule_start_time = [ 0, 0 ]; // Start of the day
							}

							if ( typeof rtb_pickadate.schedule_open[open_key].time.end !== 'undefined' ) {
								rule_end_date = new Date( '1 January 2000 ' + rtb_pickadate.schedule_open[open_key].time.end );
								rule_end_time = rtb_booking_form.get_latest_viable_time( rule_end_date.getHours(), rule_end_date.getMinutes() );
							} else {
								rule_end_time = [ 24, 0 ]; // End of the day
							}

							valid_times.push( { from: rule_start_time, to: rule_end_time, inverted: true } );

						}
					}
				}
			}

			rule_start_date = rule_start_time = rule_end_date = rule_end_time = null;

			// Pass any valid times located
			if ( valid_times.length > 1 ) {
				rtb_booking_form.timepicker.set( 'disable', valid_times );

				return;
			}

		}

		// Set it to always open if no rules have been defined
		rtb_booking_form.timepicker.set( 'enable', true );
		rtb_booking_form.timepicker.set( 'disable', false );

		return;
	};

	/**
	 * Get the outer times to exclude based on the time interval
	 *
	 * This is a work-around for a bug in pickadate.js
	 * See: https://github.com/amsul/pickadate.js/issues/614
	 */
	rtb_booking_form.get_outer_time_range = function() {

		var interval = rtb_booking_form.timepicker.get( 'interval' );

		var hour = 24;

		while ( interval >= 60 ) {
			hour--;
			interval -= 60;
		}

		if ( interval > 0 ) {
			hour--;
			interval = 60 - interval;
		}

		return { from: [0, 0], to: [ hour, interval ] };
	};

	/**
	 * Get the latest working opening hour/minute value
	 *
	 * This is a workaround for a bug in pickadate.js. The end time of a valid
	 * time value must NOT fall within the last timepicker interval and midnight
	 * See: https://github.com/amsul/pickadate.js/issues/614
	 */
	rtb_booking_form.get_latest_viable_time = function( hour, minute ) {

		var outer_time_range = this.get_outer_time_range();

		if ( hour > outer_time_range.to[0] || minute > outer_time_range.to[1] ) {
			return outer_time_range.to;
		} else {
			return [ hour, minute ];
		}
	};


	rtb_booking_form.init();
});
