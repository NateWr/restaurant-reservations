=== Restaurant Reservations ===
Contributors: NateWr
Author URI: https://github.com/NateWr
Plugin URL: http://themeofthecrop.com
Requires at Least: 3.8
Tested Up To: 4.1
Tags: restaurant, reservations, bookings, table bookings, restaurant reservation, table reservation
Stable tag: 1.4.3
License: GPLv2 or later
Donate link: http://themeofthecrop.com

Accept restaurant reservations and table bookings online. Quickly confirm or reject bookings, send email notifications, set booking times and more.

== Description ==

Accept restaurant reservations and table bookings online. Quickly confirm or reject bookings, send out custom email notifications, restrict booking times and more.

* Quickly confirm or reject a booking
* Receive an email notification when a booking request is made
* Notify a customer by email when their request is confirmed or rejected
* Automatically block bookings when you're closed, including holidays and one-off openings
* Custom user role to manage bookings
* Add your booking form to any page, post or widget area
* Customize all notification messages, and date and time formats
* Add and edit bookings from the admin panel
* Send customers an email about their booking from the admin panel

More features will be added to this plugin and addons will be created which extend the functionality or integrate with third-party services. Follow future developments at [Theme of the Crop](http://themeofthecrop.com/?utm_source=Plugin&utm_medium=Plugin%20Description&utm_campaign=Restaurant%20Reservations) or read the Upgrade Notices when you see updates for this plugin in your WordPress admin panel.

This plugin is part of a group of plugins for restaurants. Check out the [Food and Drink Menu](http://wordpress.org/plugins/food-and-drink-menu/), [Good Reviews for WordPress](http://wordpress.org/plugins/good-reviews-wp/) and [Business Profile](http://wordpress.org/plugins/business-profile/) plugins as well.

= How to use =

There is a short guide to using the plugin in the /docs/ folder. It can be accessed by following the Help link listed under the plugin on the Plugins page in your WordPress admin area. Not sure where that is? The last [screenshot](http://wordpress.org/plugins/restaurant-reservations/screenshots/) for this plugin will show you where to find it.

= Addons =
[MailChimp for Restaurant Reservations](http://themeofthecrop.com/plugin/mailchimp-restaurant-reservations/?utm_source=Plugin&utm_medium=Plugin%20Description&utm_campaign=Restaurant%20Reservations) - Subscribe emails from new restaurant reservations to your MailChimp mailing list.

= Developers =

This plugin is packed with hooks so you can extend it, customize it and rebrand it as needed. Development takes place on [GitHub](https://github.com/NateWr/restaurant-reservations), so fork it up.

== Installation ==

1. Unzip `restaurant-reservations.zip`
2. Upload the contents of `restaurant-reservations.zip` to the `/wp-content/plugins/` directory
3. Activate the plugin through the 'Plugins' menu in WordPress
4. Go to Bookings > Settings to set up the page to display your booking form.

== Frequently Asked Questions ==

= Is there a shortcode to print the booking form? =

Yes, use the `[booking-form]` shortcode.

= Can I change the format of the date or time? =

Yes, set the format for the datepicker in *Bookings > Settings*. The format used in the backend will depend on the date and time formats in your WordPress settings.

= The datepicker or timepicker is not working. =

If you load up the form and no date or time picker is popping up when you select those fields, this is likely caused by a Javascript error from another plugin or theme. You can find the problematic plugin by deactivating other plugins you're using one-by-one. Test after each deactivation to see if the date and time pickers work.

If you have deactivated all other plugins and still have a problem, try switching to a default theme (one of the TwentySomething themes).

= I'm not receiving notification emails for new bookings. =

This is almost always the result of issues with your server and can be caused by a number of things. Before posting a support request, please run through the following checklist:

1. Double-check that the notification email in *Bookings > Settings > Notifications* is correct.
2. Make sure that WordPress is able to send emails. The admin email address in the WordPress settings page should receive notifications of new users.
3. If you're not able to receive regular WordPress emails, contact your web host and ask them for help sorting it out.
4. If you're able to receive regular WordPress emails but not booking notifications, check your spam filters or junk mail folders.
5. If you still haven't found the emails, contact your web host and let them know the date, time and email address where you expected to receive a booking. They should be able to check their logs to see what is happening to the email.

= Can I make the phone number required? =

This is a common request so I have written a small addon to do this for you. [Learn more](http://themeofthecrop.com/2015/01/08/simple-phone-validation-restaurant-reservations/).

= Can I translate the booking form? =

Yes, everything in this plugin can be translated using the standard translation process and software like PoEdit. If you're not familiar with that process, I'd recommend you take a look at the [Loco Translate](https://wordpress.org/plugins/loco-translate/) plugin, which provides a simple interface in your WordPress admin area for translating themes and plugins.

If you make a translation, please help others out by adding it to the [GitHub repository](https://github.com/NateWr/restaurant-reservations) so that I can distribute it for others.

= I want to add a field to the form. Can I do that? =

I'm working on an addon that will allow you to customize the booking form and add fields through your WordPress admin panel. Until that's ready, the only way to accomplish this is by using the built-in hooks. Developers who are comfortable writing plugins for WordPress can find a rough guide in the answer to [this support request](https://wordpress.org/support/topic/edit-form-label-and-add-input-fields).

== Screenshots ==

1. Easily manage bookings. View today's bookings or upcoming bookings at-a-glance. Confirm or reject bookings quickly.
2. Easy-to-use booking form with a simple, clear style that is compatible with many themes. Minimal CSS is used to make it easier to style.
3. Great, mobile-friendly date and time pickers to make it easy for your customers.
4. Clear settings page to get it working how you need.
5. Easily adjust when customers can request a booking.
6. Customize the admin notification email when a new booking request is made. Add a quick link to confirm or reject a request straight from the email.
7. Customize the notification email sent to a user when they make a new booking request.
8. Customize the notification email sent to a user when their booking is confirmed. You can also customize the email sent when a booking is rejected.
9. Add and edit bookings from an admin panel.
10. Access a short guide from your Plugins list to help you get started quickly.

== Changelog ==

= 1.4.3 (2015-04-20) =
* Add: Datepickers for start/end date filters in admin bookings list
* Fix: Disabled weekdays get offset when editing bookings
* Fix: Start/end date filters in admin bookings list
* Fix: Booking form shouldn't appear on password-protected posts
* Fix: Dutch translation
* Updated: Dutch and German translations
* Updated: pickadate.js lib now at v3.5.6


= 1.4.2 (2015-03-31) =
* Fix: Speed issue if licensed addon active

= 1.4.1 (2015-03-31) =
* Add: rtbQuery class for fetching bookings
* Add: Centralized system for handling extension licenses
* Add: Several filters for the bookings admin list table
* Add: French translation h/t I-Visio
* Add: Italian translation h/t Pierfilippo Trevisan
* Updated: German translation h/t Roland Stumpp
* Fix: Button label in send email modal

= 1.4 (2015-02-24) =
* Add: Send a custom email from the bookings list
* Add: Hebrew translation. h/t Ahrale
* Add: Default template functions for checkbox, radio and confirmation fields
* Fix: Replace dialect with more common German in translation file. h/t Roland Stumpp

= 1.3 (2015-02-03) =
* Add and edit bookings from the admin area
* Fix: date and time pickers broken on iOS 8 devices
* Add complete German translation from scolast34
* Add partial Dutch and Chilean translations
* Change Party text field to a dropdown selection
* Bookings admin panel shows upcoming bookings by default
* Use new HTML5 input types for email and phone
* Change textdomain to comply with upcoming translation standards
* Improve WPML compatibility
* New support for assigning custom classes to fields, fieldsets and legends. h/t Primoz Cigler
* New filters for email notifications
* Fix: some bookings menu pages don't load when screen names are translated
* Fix: addons list won't load if allow_url_fopen is disabled


= 1.2.3 (2014-11-04) =
* Add a {user_email} notification template tag
* Add filter to notification template tag descriptions for extensions
* Add Reply-To mail headers and use a more reliable From header
* Add filter to the datepicker rules for disabled dates
* Fix: missing "Clear" button translation in time picker for many languages
* Fix: open time picker in body container to mitigate rare positioning bugs
* Fix: don't auto-select today's date if it's not a valid date or errors are attached to the date field

= 1.2.2 (2014-08-24) =
* Fix: custom date formats can break date validation for new bookings
* Add new booking form generation hooks for easier customization
* Add support for upcoming MailChimp addon
* Add new addons page
* Update Simple Admin Pages library to v2.0.a.7

= 1.2.1 (2014-08-01) =
* Fix: bulk actions below the bookings table don't work
* Fix: PHP Notice generated during validation

= 1.2 (2014-07-17) =
* Add notification template tags for phone number and message
* Add automatic selection of date when page is loaded (option to disable this feature)
* Add option to set time interval of time picker
* Fix auto-detection of pickadate language from WordPress site language
* Fix duplicate entry in .pot file that caused PoEdit error

= 1.1.4 (2014-07-03) =
* Add a .pot file for easier translations
* Fix notifications that showed MySQL date format instead of user-selected format
* Fix Arabic translation of pickadate component
* Add support for the correct start of the week depending on language

= 1.1.3 (2014-05-22) =
* Fix an error where the wrong date would be selected when a form was reloaded with validation errors

= 1.1.2 (2014-05-14) =
* Update Simple Admin Pages library to fix an uncommon error when saving Textarea components

= 1.1.1 (2014-05-14) =
* Update Simple Admin Pages library to fix broken Scheduler in Firefox

= 1.1 (2014-05-12) =
* Attempt to load the correct language for the datepicker from the WordPress settings
* Add support for choosing a language for the datepicker if different from WordPress settings
* Allow late bookings to be blocked 4 hours and 1 day in advance
* Fix: don't show settings under WordPress's core General settings page

= 1.0.2 (2014-05-08) =
* Remove development tool from codebase

= 1.0.1 (2014-05-08) =
* Replace dashicons caret with CSS-only caret in booking form

= 1.0 (2014-05-07) =
* Initial release

== Upgrade Notice ==

= 1.4.2 =
This update is a maintenance release that fixes a couple minor issues, adds French and Italian translations, and includes some under-the-hood changes to support upcoming extensions. 1.4.1-1.4.2 fixes a rare but vital performance issue in the admin.

= 1.4.1 =
This update is a maintenance release that fixes a couple minor issues, adds French and Italian translations, and includes some under-the-hood changes to support upcoming extensions.

= 1.4 =
Thanks to sponsorship from Gemini Design, the plugin now supports sending an email directly to customers from the list of bookings, so you can request more details or suggest an alternative booking time. This update also improves the German translation and adds a Hebrew translation. Read the full changelog for details.

= 1.3 =
This update adds support for adding and editing bookings from the admin panel. The bookings panel now shows upcoming bookings by default. The Party field in the booking form is now a dropdown selection. Plus a bunch of new features and fixes. Read the full changelog for details.

= 1.2.3 =
This update adds a {user_email} notification template tag and improves the mail headers on notifications to mitigate spam risk. It also adds the missing translation for the Clear button in the time picker for many languages. More minor bug fixes listed in the changelog.

= 1.2.2 =
This update adds support for a new MailChimp addon that will be released soon. An addons page is now available under the Bookings menu. A bug in which custom date/time formats could cause validation errors has been fixed. New hooks are now in place so that it's easier to customize the form output.

= 1.2.1 =
This is a minor maintenance update which fixes a couple of small bugs.

= 1.2 =
This update adds new template tags for notification emails, a new option to customize the time interval and more. A new .pot file has been generated, so update your translations. Consult the changelog for further details.

= 1.1.4 =
This updated fixes an error with the format of the date in notification emails. Now it will show you the date formatted however you have chosen for it to be formatted in your WordPress installation. It also now displays the correct start of the week depending on the language selected for the datepicker. A .pot file is now included for easier translations.

= 1.1.3 =
This update fixes an error when the form had validation errors (missing fields or wrong date/time selected). Instead of loading the selected date it would load today's date. This update ensures the selected date is reloaded properly.

= 1.1.2 =
This update fixes an error some people may experience when trying to save settings. This is the second update today, so if you missed the other one please read the changelog for the 1.1.1 update as well.

= 1.1.1 =
This update fixes problems some users reported when using the Firefox browser to modify the booking schedule. This required an update to a library that is shared with another plugin, Food and Drink Menu. If you are using that plugin, please update that one as well or you may get some odd behavior. (Thanks to sangwh and bforsoft for reporting the issue.)

= 1.1 =
This update improves internationalization (i8n) by attempting to determine the appropriate language for the booking form datepicker from your WordPress settings. It also adds a setting to pick a language manually from a list of supported languages. This update also adds options to block late bookings at least 4 hours or 1 day in advance. Thanks to Remco and Roland for their early feedback.

= 1.0.2 =
This update removes a bit of code that was used for development purposes. Please update as this code could be run by any user on the frontend.
