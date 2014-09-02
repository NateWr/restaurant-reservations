=== Restaurant Reservations ===
Contributors: NateWr
Author URI: https://github.com/NateWr
Plugin URL: http://themeofthecrop.com
Requires at Least: 3.8
Tested Up To: 3.9.1
Tags: restaurant, reservations, bookings, table bookings, restaurant reservation, table reservation
Stable tag: 1.2.2
License: GPLv2 or later
Donate link: http://themeofthecrop.com

Accept restaurant reservations and table bookings online. Quickly confirm or reject bookings, send email notifications, set booking times and more.

== Description ==

Accept restaurant reservations and table bookings online. Quickly confirm or reject bookings, send out custom email notifications, restrict booking times and more.

* Quickly confirm or reject a booking
* Admin email notification when a booking request is made
* Customer email notifications when their request is confirmed or rejected
* Limit available booking times
* Custom user role to manage bookings
* Add your booking form to any page, post or widget area
* Customize all notification messages, and date and time formats

More features will be added to this plugin and addons will be created which extend the functionality or integrate with third-party services. Follow future developments at [Theme of the Crop](http://themeofthecrop.com/?utm_source=Plugin&utm_medium=Plugin%20Description&utm_campaign=Restaurant%20Reservations) or read the Upgrade Notices when you see updates for this plugin in your WordPress admin panel.

This plugin is part of a group of plugins for restaurants. Check out the [Food and Drink Menu](http://wordpress.org/plugins/food-and-drink-menu/), [Good Reviews for WordPress](http://wordpress.org/plugins/good-reviews-wp/) and [Business Profile](http://wordpress.org/plugins/business-profile/) plugins as well.

= How to use =

There is a short guide to using the plugin in the /docs/ folder. It can be accessed by following the Help link listed under the plugin on the Plugins page in your WordPress admin area. Not sure where that is? One of the [screenshots](http://wordpress.org/plugins/restaurant-reservations/screenshots/) for this plugin will show you where to find it.

= Addons =
[MailChimp for Restaurant Reservations](http://themeofthecrop.com/2014/08/18/mailchimp-restaurant-reservations-ready-beta/?utm_source=Plugin&utm_medium=Plugin%20Description&utm_campaign=Restaurant%20Reservations)

Subscribe emails from new restaurant reservations to your MailChimp mailing list.

= Developers =

This plugin is packed with hooks so you can extend it, customize it and rebrand it as needed. Development takes place on [GitHub](https://github.com/NateWr/restaurant-reservations), so fork it up.

== Installation ==

1. Unzip `restaurant-reservations.zip`
2. Upload the contents of `restaurant-reservations.zip` to the `/wp-content/plugins/` directory
3. Activate the plugin through the 'Plugins' menu in WordPress
4. Go to Bookings > Settings to set up the page to display your booking form.

== Screenshots ==

1. Easily manage bookings. View today's bookings or upcoming bookings at-a-glance. Confirm or reject bookings quickly.
2. Easy-to-use booking form with a simple, clear style that is compatible with many themes. Minimal CSS is used to make it easier to style.
3. Great, mobile-friendly date and time pickers to make it easy for your customers.
4. Clear settings page to get it working how you need.
5. Easily adjust when customers can request a booking.
6. Customize the admin notification email when a new booking request is made. Add a quick link to confirm or reject a request straight from the email.
7. Customize the notification email sent to a user when they make a new booking request.
8. Customize the notification email sent to a user when their booking is confirmed. You can also customize the email sent when a booking is rejected.
9. Access a short guide from your Plugins list to help you get started quickly.

== Changelog ==

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
