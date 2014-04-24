/**
 * Javascript functions for the admin interface components of Simple Admin Pages
 *
 * @package Simple Admin Pages
 */

jQuery(document).ready(function ($) {

	/**
	 * Opening Hours
	 ***************/

	/**
	 * Update the name of each day when the select option is changed
	 */
	$( '.sap-opening-hours-day' ).change( function() {
		$( $(this).data( 'target' ) ).val( $(this).children( 'option:selected' ).data( 'name' ) );
	});

	/**
	 * Scheduler
	 ***********/

	 if ( typeof sap_scheduler != 'undefined' ) {

		/**
		 * Register click events on load
		 */
		sap_scheduler_register_events();

		/**
		 * Enable datepickers on load
		 */
		if ( typeof sap_scheduler.settings != 'undefined' ) {
			for ( var key in sap_scheduler.settings ) {
				var obj = sap_scheduler.settings[key];
				$( '#' + key + ' .sap-scheduler-date-input input' ).pickadate({
					format: obj.date_format,
				});
				$( '#' + key + ' .sap-scheduler-time-input input' ).pickatime({
					interval: obj.time_interval,
					format: obj.time_format,
				});
			}
		}

		/**
		 * Add a new scheduler panel
		 */
		$( '.sap-add-scheduler a' ).click( function() {
			var scheduler = $(this).parent().siblings( '.sap-scheduler' );
			var scheduler_id = scheduler.attr( 'id' );
			var scheduler_settings = sap_scheduler.settings[ scheduler_id ];
			scheduler.append( scheduler_settings.template.replace( /\[0\]/g, '[' + scheduler.children( '.sap-scheduler-rule' ).length + ']' ) );
			scheduler.last( '#' + scheduler_id + ' .sap-scheduler-rule' ).find( '.sap-scheduler-date-input input' ).pickadate({
				format: scheduler_settings.date_format,
			});
			scheduler.last( '#' + scheduler_id + ' .sap-scheduler-rule' ).find( '.sap-scheduler-time-input input' ).pickatime({
				interval: scheduler_settings.time_interval,
				format: scheduler_settings.time_format,
			});
			sap_scheduler_register_events();

			return false;
		});

		/**
		 * Register event handlers on the scheduler rules. This is run on page load
		 * and every time a rule is added.
		 */
		function sap_scheduler_register_events() {

			/**
			 * Open and close the full view of a scheduling rule
			 */
			$( '.sap-scheduler-rule .toggle' ).off( 'click' ).click( function() {

				var controls = $(this).parent();

				if ( $(this).parent().parent().hasClass( 'list' ) ) {
					controls.hide();
					$(this).children( '.dashicons-edit' ).removeClass( 'dashicons-edit' ).addClass( 'dashicons-arrow-up-alt2' );
					controls.siblings( '.sap-scheduler-brief' ).hide();
					controls.siblings( '.sap-scheduler-date, .sap-scheduler-time' ).slideDown( function() {
						$(this).parent().removeClass( 'list' );
						controls.fadeIn();
					});

				} else {
					controls.hide();
					$(this).children( '.dashicons-arrow-up-alt2' ).removeClass( 'dashicons-arrow-up-alt2' ).addClass( 'dashicons-edit' );
					controls.siblings( '.sap-scheduler-brief' ).fadeIn();
					controls.siblings( '.sap-scheduler-time' ).slideUp();
					controls.siblings( '.sap-scheduler-date' ).slideUp( function() {

						var scheduler_rule = $(this).parent();
						var scheduler_id = scheduler_rule.parent().attr( 'id' );

						scheduler_rule.addClass( 'list' );
						controls.fadeIn();

						sap_scheduler_set_date_phrase( scheduler_rule, scheduler_id );
						sap_scheduler_set_time_phrase( scheduler_rule, scheduler_id );
					});
				}

				return false;
			});

			/**
			 * Update current selection for selector lists
			 */
			$( '.sap-selector a' ).off( 'switch.sap' ).on( 'switch.sap', function() {
				$(this).parent().parent().find( 'a' ).removeClass( 'selected' );
				$(this).addClass( 'selected' );

				return false;
			});

			/**
			 * Switch between weekly, monthly and date options
			 */
			$( '.sap-scheduler-date .sap-selector a' ).off( 'click' ).click( function() {

				$(this).trigger( 'switch.sap' );

				var date = $(this).closest( '.sap-scheduler-date' );

				if ( $(this).data( 'format' ) == 'weekly' && date.hasClass( 'weekly' ) === false ) {
					date.children( '.sap-scheduler-weeks' ).slideUp( function() {
						$(this).find( 'input' ).prop('checked', false);
					});
					date.children( '.sap-scheduler-date-input' ).slideUp( function() {
						$(this).find( 'input' ).val( '' );
					});
					date.children( '.sap-scheduler-weekdays' ).slideDown( function() {
						date.removeClass( 'monthly date' );
						date.addClass( 'weekly' );
					});

				} else if ( $(this).data( 'format' ) == 'monthly' && date.hasClass( 'monthly' ) === false ) {
					date.children( '.sap-scheduler-date-input' ).slideUp( function() {
						$(this).find( 'input' ).val( '' );
					});
					date.children( '.sap-scheduler-weekdays' ).slideDown();
					date.children( '.sap-scheduler-weeks' ).slideDown( function() {
						date.removeClass( 'weekly date' );
						date.addClass( 'monthly' );
					});

				} else if ( $(this).data( 'format' ) == 'date' && date.hasClass( 'date' ) === false ) {
					date.children( '.sap-scheduler-weekdays' ).slideUp( function() {
						$(this).find( 'input' ).prop('checked', false);
					});
					date.children( '.sap-scheduler-weeks' ).slideUp( function() {
						$(this).find( 'input' ).prop('checked', false);
					});
					date.children( '.sap-scheduler-date-input' ).slideDown( function() {
						date.removeClass( 'weekly monthly' );
						date.addClass( 'date' );
					});
				}

				return false;
			});

			/**
			 * Show or hide time slot options
			 */
			$( '.sap-scheduler-time .sap-selector a' ).off( 'click' ).click( function() {

				$(this).trigger( 'switch.sap' );

				var time = $(this).closest( '.sap-scheduler-time' );

				if ( $(this).data( 'format' ) == 'time-slot' && time.hasClass( 'time-slot' ) === false ) {
					time.children( '.sap-scheduler-time-input' ).slideDown();
					time.children( '.sap-scheduler-all-day' ).slideUp( function() {
						time.removeClass( 'all-day' );
						time.addClass( 'time-slot' );
					});

				} else if ( $(this).data( 'format' ) == 'all-day' && time.hasClass( 'all-day' ) === false ) {
					time.children( '.sap-scheduler-all-day' ).slideDown();
					time.children( '.sap-scheduler-time-input' ).slideUp( function() {
						time.removeClass( 'time-slot' );
						time.addClass( 'all-day' );
						time.find( 'input' ).val( '' );
					});
				}

				return false;
			});

			/**
			 * Show time slot options from the link in the all-day notice
			 */
			$( '.sap-scheduler-all-day a' ).off( 'click' ).click( function() {
				$(this).closest( '.sap-scheduler-time' ).children( '.sap-selector' ).find( 'a[data-format="time-slot"]' ).trigger( 'click' );

				return false;
			});

			/**
			 * Delete a scheduling rule panel
			 */
			$( '.sap-scheduler-control .delete' ).off( 'click' ).click( function() {
				var scheduler = $(this).closest( '.sap-scheduler' );
				$(this).parent().parent().fadeOut( function() {
					$(this).remove();

					// Reset the index of each rule
					// @todo optimize this excessive use of regex (32x per rule).
					//	maybe set a data-slug attribute on .sap-scheduler-rule and a
					//	data-slug prop on each input/select/label, then use these to
					//	construct the new attributes: rule-slug[index][input-slug]
					scheduler.children( '.sap-scheduler-rule' ).each( function( i ) {
						var index = i.toString();
						$(this).find( 'input' ).each( function() {
							var name = $(this).attr( 'name' ).replace( /\[\d*\]/g, '[' + index + ']' );
							$(this).attr( 'name', name );
							$(this).attr( 'id', name );
							var aria_owns = $(this).attr( 'aria-owns' );
							if ( typeof aria_owns !== 'undefined' && aria_owns !== false) {
								$(this).attr( 'aria-owns', name );
							}
						});
						$(this).find( 'label' ).each( function() {
							var name = $(this).attr( 'for' ).replace( /\[\d*\]/g, '[' + index + ']' );
							$(this).attr( 'for', name );
						});
						$(this).find( '.picker' ).each( function() {
							var name = $(this).attr( 'id' ).replace( /\[\d*\]/g, '[' + index + ']' );
							$(this).attr( 'id', name );
						});
					});
				});

				return false;
			});
		}

		/**
		 * Set the summary phrase for a scheduler rule's date. This phrase is shown
		 * when the rule's view is minimized.
		 */
		function sap_scheduler_set_date_phrase( scheduler_rule, scheduler_id ) {

			var date_value = scheduler_rule.find( '.sap-scheduler-date-input input' ).val();
			if ( typeof date_value !== 'undefined' && date_value != '' ) {
				scheduler_rule.find( '.sap-scheduler-brief .date .value' ).html( date_value );

				return;
			}

			var weekdays = 0;
			var weekday_arr = new Array();
			scheduler_rule.find( '.sap-scheduler-weekdays input' ).each( function() {
				if ( $(this).prop( 'checked' ) !== false ) {
					weekdays += 1;
					weekday_arr.push( sap_scheduler.settings[scheduler_id]['weekdays'][ $(this).data( 'day' ) ] );
				}
			});

			if ( weekdays == 0 && sap_scheduler.settings[ scheduler_id ].disable_weekdays === false ) {
				scheduler_rule.find( '.sap-scheduler-brief .date .value' ).html( sap_scheduler.settings[scheduler_id].summaries['never'] );

				return;

			} else if ( weekdays == 7 ) {
				var weekday_string = sap_scheduler.settings[scheduler_id].summaries['weekly_always'];

			} else {
				var weekday_string = weekday_arr.join( ', ' );
			}

			var weeks = 0;
			var weeks_arr = new Array();
			scheduler_rule.find( '.sap-scheduler-weeks input' ).each( function() {
				if ( $(this).prop( 'checked' ) !== false ) {
					weeks +=1;
					weeks_arr.push( sap_scheduler.settings[scheduler_id]['weeks'][ $(this).data( 'week' ) ] );
				}
			});

			if ( ( weeks == 0 || weeks == 5 ) && sap_scheduler.settings[ scheduler_id ].disable_weekdays === false ) {
				scheduler_rule.find( '.sap-scheduler-brief .date .value' ).html( weekday_string );

				return;
			}

			if ( weeks == 0 ) {
				scheduler_rule.find( '.sap-scheduler-brief .date .value' ).html( sap_scheduler.settings[scheduler_id].summaries['never'] );

				return;
			}

			if ( weekday_string != '' ) {
				scheduler_rule.find( '.sap-scheduler-brief .date .value' ).html( sap_scheduler.settings[scheduler_id].summaries['monthly_weekdays'].replace( '{days}', weekday_arr.join( ', ' ) ).replace( '{weeks}', weeks_arr.join( ', ' ) ) );
			} else {
				scheduler_rule.find( '.sap-scheduler-brief .date .value' ).html( sap_scheduler.settings[scheduler_id].summaries['monthly_weeks'].replace( '{weeks}', weeks_arr.join( ', ' ) ) );
			}
		}

		/**
		 * Set the summary phrase for a scheduler rule's time. This phrase is shown
		 * when the rule's view is minimized.
		 */
		function sap_scheduler_set_time_phrase( scheduler_rule, scheduler_id ) {

			var start = scheduler_rule.find( '.sap-scheduler-time-input .start input' ).val();
			var end = scheduler_rule.find( '.sap-scheduler-time-input .end input' ).val();

			if ( start == '' && ( end == '' || typeof end == 'undefined' ) ) {
				scheduler_rule.find( '.sap-scheduler-brief .time .value' ).html( sap_scheduler.settings[scheduler_id].summaries['all_day'] );

				return;
			}

			if ( start == '' ) {
				scheduler_rule.find( '.sap-scheduler-brief .time .value' ).html( sap_scheduler.settings[scheduler_id].summaries['before'] + ' ' + end );

				return;
			}

			if ( end == '' || typeof end == 'undefined' ) {
				scheduler_rule.find( '.sap-scheduler-brief .time .value' ).html( sap_scheduler.settings[scheduler_id].summaries['after'] + ' ' + start );

				return;
			}

			if ( typeof end == 'undefined' ) {
				return scheduler_rule.find( '.sap-scheduler-brief .time .value' ).html( start );
			} else {
				return scheduler_rule.find( '.sap-scheduler-brief .time .value' ).html( start + sap_scheduler.settings[scheduler_id].summaries['separator'] + end );
			}
		}
	}

});
