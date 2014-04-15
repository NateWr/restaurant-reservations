<?php

/**
 * Register, display and save a schedule of dates and times.
 *
 * This is designed for use for opening hours, a booking schedule or anything
 * that requires recurring dates and times.
 *
 * @since 2.0
 * @package Simple Admin Pages
 */

class sapAdminPageSettingScheduler_2_0_a_1 extends sapAdminPageSetting_2_0_a_1 {

	public $sanitize_callback = 'sanitize_text_field';

	public $weekdays = array(
		'monday'		=> 'Mo',
		'tuesday'		=> 'Tu',
		'wednesday'		=> 'We',
		'thursday'		=> 'Th',
		'friday'		=> 'Fr',
		'saturday'		=> 'Sa',
		'sunday'		=> 'Su',
	);

	public $weeks = array(
		'first'		=> '1st',
		'second'	=> '2nd',
		'third'		=> '3rd',
		'fourth'	=> '4th',
		'last'		=> 'last',
	);

	/**
	 * Number of minutes between time selection intervals
	 */
	public $time_interval = 15;

	/**
	 * Display format for time selection
	 * See http://amsul.ca/pickadate.js/ for formatting options
	 */
	public $time_format = 'h:i A';

	/**
	 * Display format for date selection
	 * See http://amsul.ca/pickadate.js/ for formatting options
	 */
	public $date_format = 'd mmmm, yyyy';

	/**
	 * Escape the value to display it in text fields and other input fields
	 * @since 2.0
	 */
	public function esc_value( $val ) {

		$value = array();

		if ( empty( $val ) ) {
			return $value;
		}

		foreach ( $val as $i => $rule ) {

			if ( !empty( $rule['weekdays'] ) ) {
				$value[$i]['weekdays'] = array();
				foreach ( $rule['weekdays'] as $day => $flag ) {
					if ( $flag !== '1' ) {
						continue;
					}

					$value[$i]['weekdays'][$day] = $flag;
				}
			}

			if ( !empty( $rule['weeks'] ) ) {
				$value[$i]['weeks'] = array();
				foreach ( $rule['weeks'] as $week => $flag ) {
					if ( $flag !== '1' ) {
						continue;
					}

					$value[$i]['weeks'][$week] = $flag;
				}
			}

			if ( !empty( $rule['date'] ) ) {
				$value[$i]['date'] = esc_attr( $rule['date'] );
			}

			if ( !empty( $rule['time']['start'] ) ) {
				$value[$i]['time']['start'] = esc_attr( $rule['time']['start'] );
			}
			if ( !empty( $rule['time']['end'] ) ) {
				$value[$i]['time']['end'] = esc_attr( $rule['time']['end'] );
			}
		}

		return $value;
	}

	/**
	 * Compile and pass configurable variables to the javascript file, so they
	 * can be used when we initialize the pickadate components
	 * @since 2.0
	 */
	public function pass_to_scripts() {

		// Create a global variable containing settings for all schedulers
		// that are being rendered on the page. This allows us to pass different
		// settings for different schedulers on the same page.
		global $sap_scheduler_settings;

		if ( !isset( $sap_scheduler_settings ) ) {
			$sap_scheduler_settings = array();
		}

		$sap_scheduler_settings[ $this->id ] = array(
			'time_interval' => $this->time_interval,
			'time_format'	=> $this->time_format,
			'date_format'	=> $this->date_format,
			'template'		=> $this->get_template(),
			'weekdays'		=> $this->weekdays,
			'weeks'			=> $this->weeks,
		);

		// This gets called multiple times, but only the last call is actually
		// pushed to the script.
		wp_localize_script(
			'sap-admin-script',
			'sap_scheduler',
			array(
				'settings' => $sap_scheduler_settings,
				'summaries'  => $this->schedule_summaries,
			)
		);

	}

	/**
	 * Display this setting
	 * @since 2.0
	 */
	public function display_setting() {

		$this->display_description();

		// Define summary text to use when a rule is displayed in brief
		$this->set_schedule_summaries();

		// Pass data to the script files to handle js interactions
		$this->pass_to_scripts();

		?>

			<div class="sap-scheduler" id="<?php echo $this->id; ?>">
			<?php
				foreach ( $this->value as $id => $rule ) {
					echo $this->get_template( $id, $rule, true );
				}
			?>
			</div>
			<div class="sap-add-scheduler">
				<div class="dashicons dashicons-plus"></div>
				<a href="#">
					<?php _e( 'Add new scheduling rule', SAP_TEXTDOMAIN ); ?>
				</a>
			</div>

		<?php
	}

	/**
	 * Retrieve the template for a scheduling rule
	 * @since 2.0
	 */
	public function get_template( $id = 0, $values = array(), $list = false ) {

		$date_format = $this->get_date_format( $values );
		$time_format = $this->get_time_format( $values );

		ob_start();
		?>

		<div class="sap-scheduler-rule clearfix<?php echo $list ? ' list' : ''; ?>">
			<div class="sap-scheduler-date <?php echo $date_format; ?>">
				<ul class="sap-selector">
					<li>
						<div class="dashicons dashicons-calendar"></div>
						<a href="#" data-format="weekly"<?php echo $date_format == 'weekly' ? ' class="selected"' : ''; ?>>
							<?php _ex( 'Weekly', 'Format of a scheduling rule', SAP_TEXTDOMAIN ); ?>
						</a>
					</li>
					<li>
						<a href="#" data-format="monthly"<?php echo $date_format == 'monthly' ? ' class="selected"' : ''; ?>>
							<?php _ex( 'Monthly', 'Format of a scheduling rule', SAP_TEXTDOMAIN ); ?>
						</a>
					</li>
					<li>
						<a href="#" data-format="date"<?php echo $date_format == 'date' ? ' class="selected"' : ''; ?>>
							<?php _ex( 'Date', 'Format of a scheduling rule', SAP_TEXTDOMAIN ); ?>
						</a>
					</li>
				</ul>
				<ul class="sap-scheduler-weekdays">
					<li class="label">
						<?php _ex( 'Days of the week', 'Label for selecting days of the week in a scheduling rule', SAP_TEXTDOMAIN ); ?>
					</li>
				<?php
					foreach ( $this->weekdays as $slug => $label ) :
						$input_name = $this->get_input_name() . '[' . $id . '][weekdays][' . esc_attr( $slug ) . ']';
				?>
					<li>
						&nbsp;<input type="checkbox" name="<?php echo $input_name; ?>" id="<?php echo $input_name; ?>" value="1"<?php echo empty( $values['weekdays'][$slug] ) ? '' : ' checked="checked"'; ?> data-day="<?php echo esc_attr( $slug ); ?>"><label for="<?php echo $input_name; ?>"><?php echo ucfirst( $label ); ?></label>
					</li>
				<?php endforeach; ?>
				</ul>
				<ul class="sap-scheduler-weeks">
					<li class="label">
						<?php _ex( 'Weeks of the month', 'Label for selecting weeks of the month in a scheduling rule', SAP_TEXTDOMAIN ); ?>
					</li>
				<?php
					foreach ( $this->weeks as $slug => $label ) :
						$input_name = $this->get_input_name() . '[' . $id . '][weeks][' . esc_attr( $slug ) . ']';
				?>
					<li>
						&nbsp;<input type="checkbox" name="<?php echo $input_name; ?>" id="<?php echo $input_name; ?>" value="1"<?php echo empty( $values['weeks'][$slug] ) ? '' : ' checked="checked"'; ?> data-week="<?php echo esc_attr( $slug ); ?>"><label for="<?php echo $input_name; ?>"><?php echo ucfirst( $label ); ?></label>
					</li>
				<?php endforeach; ?>
				</ul>
				<div class="sap-scheduler-date-input">
					<label for="<?php echo $this->get_input_name(); ?>[<?php echo $id; ?>][date]">
						<?php _e( 'Date', SAP_TEXTDOMAIN ); ?>
					</label>
					<input type="text" name="<?php echo $this->get_input_name(); ?>[<?php echo $id; ?>][date]" id="<?php echo $this->get_input_name(); ?>[<?php echo $id; ?>][date]" value="<?php echo empty( $values['date'] ) ? '' : $values['date']; ?>">
				</div>
			</div>
			<div class="sap-scheduler-time <?php echo $time_format; ?>">
				<ul class="sap-selector">
					<li>
						<div class="dashicons dashicons-clock"></div>
						<a href="#" data-format="time-slot"<?php echo $time_format == 'time-slot' ? ' class="selected"' : ''; ?>>
							<?php _ex( 'Time', 'Label to select time slot for a scheduling rule', SAP_TEXTDOMAIN ); ?>
						</a>
					</li>
					<li>
						<a href="#" data-format="all-day"<?php echo $time_format == 'all-day' ? ' class="selected"' : ''; ?>>
							<?php _ex( 'All day', 'Label to set a scheduling rule to last all day', SAP_TEXTDOMAIN ); ?>
						</a>
					</li>
				</ul>
				<div class="sap-scheduler-time-input clearfix">
					<div class="start">
						<label for="<?php echo $this->get_input_name(); ?>[<?php echo $id; ?>][time][start]">
							<?php _ex( 'Start', 'Label for the starting time of a scheduling rule', SAP_TEXTDOMAIN ); ?>
						</label>
						<input type="text" name="<?php echo $this->get_input_name() . '[' . $id . '][time][start]'; ?>" id="<?php echo $this->get_input_name() . '[' . $id . '][time][start]'; ?>" value="<?php echo empty( $values['time']['start'] ) ? '' : $values['time']['start']; ?>">
					</div>
					<div class="end">
						<label for="<?php echo $this->get_input_name(); ?>[<?php echo $id; ?>][time][end]">
							<?php _ex( 'End', 'Label for the ending time of a scheduling rule', SAP_TEXTDOMAIN ); ?>
						</label>
						<input type="text" name="<?php echo $this->get_input_name() . '[' . $id . '][time][end]'; ?>" id="<?php echo $this->get_input_name() . '[' . $id . '][time][end]'; ?>" value="<?php echo empty( $values['time']['end'] ) ? '' : $values['time']['end']; ?>">
					</div>
				</div>
				<div class="sap-scheduler-all-day">
					<?php _ex( 'All day long. Want to <a href="#" data-format="time-slot">set a time slot</a>?', 'Prompt displayed when a scheduling rule is set without any time restrictions.', SAP_TEXTDOMAIN ); ?>
				</div>
			</div>
			<div class="sap-scheduler-brief">
				<div class="date">
					<div class="dashicons dashicons-calendar"></div>
					<span class="value"><?php echo $this->get_date_summary( $values ); ?></span>
				</div>
				<div class="time">
					<div class="dashicons dashicons-clock"></div>
					<span class="value"><?php echo $this->get_time_summary( $values ); ?></span>
				</div>
			</div>
			<div class="sap-scheduler-control">
				<a href="#" class="toggle" title="<?php _e( 'Close rule', SAP_TEXTDOMAIN ); ?>">
					<div class="dashicons dashicons-<?php echo $list ? 'edit' : 'arrow-up-alt2'; ?>"></div>
					<span class="screen-reader-text">
						<?php _e( 'Close scheduling rule', SAP_TEXTDOMAIN ); ?>
					</span>
				</a>
				<a href="#" class="delete" title="<?php _e( 'Delete rule', SAP_TEXTDOMAIN ); ?>">
					<div class="dashicons dashicons-dismiss"></div>
					<span class="screen-reader-text">
						<?php _e( 'Delete scheduling rule', SAP_TEXTDOMAIN ); ?>
					</span>
				</a>
			</div>
		</div>

		<?php
		$output = ob_get_clean();

		return $output;
	}

	/**
	 * Determine the date format of a rule (weeky/monthly/date)
	 * @since 2.0
	 */
	public function get_date_format( $values ) {
		if ( !empty( $values['date'] ) ) {
			return 'date';
		} elseif ( !empty( $values['weeks'] ) ) {
			return 'monthly';
		}

		return 'weekly';
	}

	/**
	 * Determine the time format of a rule (time-slot/all-day)
	 * @since 2.0
	 */
	public function get_time_format( $values ) {
		if ( empty( $values['time']['start'] ) && empty( $values['time']['end'] ) ) {
			return 'all-day';
		}

		return 'time-slot';
	}

	/**
	 * Set some default summary strings that can be used when the scheduler
	 * rule is shown in brief
	 * @since 2.0
	 */
	public function set_schedule_summaries() {

		if ( !empty( $this->schedule_summaries ) ) {
			return;
		}

		$this->schedule_summaries = array(
			'never' 				=> _x( 'Never', 'Brief description of a scheduling rule when no weekdays or weeks are included in the rule.', SAP_TEXTDOMAIN ),
			'weekly_always' 		=> _x( 'Every day', 'Brief description of a scheduling rule when all the weekdays/weeks are included in the rule.', SAP_TEXTDOMAIN ),
			'monthly_partial' 		=> _x( '{days} on the {weeks} week of the month', 'Brief description of a scheduling rule when some weekdays are included on only some weeks of the month. The {days} and {weeks} bits should be left alone and will be replaced by a comma-separated list of days (the first one) and weeks (the second one) in the following format: M, T, W on the first, second week of the month', SAP_TEXTDOMAIN ),
			'all_day' 				=> _x( 'All day', 'Brief description of a scheduling rule when no times are set', SAP_TEXTDOMAIN ),
			'before' 				=> _x( 'Ends at', 'Brief description of a scheduling rule when an end time is set but no start time. If the end time is 6pm, it will read: Ends at 6pm.', SAP_TEXTDOMAIN ),
			'after' 				=> _x( 'Starts at', 'Brief description of a scheduling rule when a start time is set but no end time. If the start time is 6pm, it will read: Starts at 6pm.', SAP_TEXTDOMAIN ),
			'separator'				=> _x( '&mdash;', 'Separator between times of a scheduling rule.', SAP_TEXTDOMAIN ),
		);
	}

	/**
	 * Print the date phrase, a brief description of the date settings
	 * @since 2.0
	 */
	public function get_date_summary( $values = array() ) {

		if ( !empty( $values['date'] ) ) {
			return $values['date'];
		}

		if ( empty( $values['weekdays'] ) ) {
			return $this->schedule_summaries['never'];
		}

		if ( count( $values['weekdays'] ) == 7 ) {
			$weekdays = $this->schedule_summaries['weekly_always'];
		} else {
			$arr = array();
			foreach ( $values['weekdays'] as $weekday => $state ) {
				$arr[] = $this->weekdays[$weekday];
			}
			$weekdays = join( ', ', $arr );
		}

		if ( empty( $values['weeks'] ) || count( $values['weeks'] ) == 5 ) {
			return $weekdays;
		}

		$arr = array();
		foreach ( $values['weeks'] as $weeks => $state ) {
			$arr[] = $this->weeks[$weeks];
		}
		$weeks = join( ', ', $arr );

		return str_replace( array( '{days}', '{weeks}' ), array( $weekdays, $weeks ), $this->schedule_summaries['monthly_partial'] );

	}

	/**
	 * Print the time phrase, a brief description of the time settings
	 * @since 2.0
	 */
	public function get_time_summary( $values = array() ) {

		if ( empty( $values['time']['start'] ) && empty( $values['time']['end'] ) ) {
			return $this->schedule_summaries['all_day'];
		}

		if ( empty( $values['time']['start'] ) ) {
			return $this->schedule_summaries['before'] . ' ' . $values['time']['end'];
		}

		if ( empty( $values['time']['end'] ) ) {
			return $this->schedule_summaries['after'] . ' ' . $values['time']['start'];
		}

		return $values['time']['start'] . $this->schedule_summaries['separator'] . $values['time']['end'];

	}

	/**
	 * Sanitize the array of text inputs for this setting
	 * @since 2.0
	 */
	public function sanitize_callback_wrapper( $values ) {

		$output = array();

		foreach ( $values as $i => $rule ) {

			if ( !empty( $rule['weekdays'] ) ) {
				$output[$i]['weekdays'] = array();
				foreach ( $rule['weekdays'] as $day => $flag ) {
					if ( $flag !== '1' ||
							( $day !== 'monday' && $day !== 'tuesday' && $day !== 'wednesday' && $day !== 'thursday' && $day !== 'friday' && $day !== 'saturday' && $day !== 'sunday' ) ) {
						continue;
					}

					$output[$i]['weekdays'][$day] = $flag;
				}
			}

			if ( !empty( $rule['weeks'] ) ) {
				$output[$i]['weeks'] = array();
				foreach ( $rule['weeks'] as $week => $flag ) {
					if ( $flag !== '1' ||
							( $week !== 'first' && $week !== 'second' && $week !== 'third' && $week !== 'fourth' && $week !== 'last' ) ) {
						continue;
					}

					$output[$i]['weeks'][$week] = $flag;
				}
			}

			if ( !empty( $rule['date'] ) ) {
				$date = new DateTime( $rule['date'] );
				if ( checkdate( $date->format( 'n' ), $date->format( 'j' ), $date->format( 'Y' ) ) ) {
					$output[$i]['date'] = call_user_func( $this->sanitize_callback, $rule['date'] );
				}
			}

			if ( !empty( $rule['time']['start'] ) ) {
				$output[$i]['time']['start'] = call_user_func( $this->sanitize_callback, $rule['time']['start'] );
			}
			if ( !empty( $rule['time']['end'] ) ) {
				$output[$i]['time']['end'] = call_user_func( $this->sanitize_callback, $rule['time']['end'] );
			}
		}

		return $output;
	}

}
