/**
 * Javascript functions for the admin interface components of Simple Admin Pages
 *
 * @package Simple Admin Pages
 */

/**
 * When the page loads
 */
jQuery(document).ready(function ($) {

	/**
	 * Update the name of each day when the select option is cahnged
	 */
	$('.sap-opening-hours-day').change( function() {
		$( $(this).data('target') ).val( $(this).children('option:selected').data('name') );
	});

});
