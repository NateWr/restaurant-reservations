=== Restaurant Reservations ===
Contributors: NateWr
Author URI: https://github.com/NateWr
Plugin URL: https://themeofthecrop.com
Requires at Least: 4.4
Tested Up To: 4.6
Tags: restaurant, reservations, bookings, table bookings, restaurant reservation, table reservation
Stable tag: 1.6.2
License: GPLv2 or later
Donate link: https://themeofthecrop.com

Accept restaurant reservations and table bookings online. Quickly confirm or reject bookings, send email notifications, set booking times and more.

== Description ==

Accept restaurant reservations and table bookings online. Quickly confirm or reject bookings, send out custom email notifications, restrict booking times and more.

* Quickly [confirm or reject](http://doc.themeofthecrop.com/plugins/restaurant-reservations/user/manage/confirm-reject-bookings) a booking
* Receive an [email notification](http://doc.themeofthecrop.com/plugins/restaurant-reservations/user/config/email-notifications) when a booking request is made
* Notify a customer by email when their request is confirmed or rejected
* Automatically [block bookings](http://doc.themeofthecrop.com/plugins/restaurant-reservations/user/config/schedule#scheduling-exceptions) when you're closed, including holidays and one-off openings
* Custom user role to manage bookings
* Add your booking form to any page, post or widget area
* Customize all [notification messages](http://doc.themeofthecrop.com/plugins/restaurant-reservations/user/config/email-notifications#understanding-the-template-tags), and date and time formats
* Add and edit bookings from the admin panel
* Take bookings for [multiple locations](http://doc.themeofthecrop.com/plugins/restaurant-reservations/user/manage/locations)
* Send customers an email about their booking from the admin panel

Follow future developments at [Theme of the Crop](https://themeofthecrop.com/?utm_source=Plugin&utm_medium=Plugin%20Description&utm_campaign=Restaurant%20Reservations) or read the Upgrade Notices when you see updates for this plugin in your WordPress admin panel.

This plugin is part of a group of plugins for restaurants. Check out the [Food and Drink Menu](http://wordpress.org/plugins/food-and-drink-menu/), [Good Reviews for WordPress](http://wordpress.org/plugins/good-reviews-wp/) and [Business Profile](http://wordpress.org/plugins/business-profile/) plugins as well.

= How to use =

Read the [User Guide](http://doc.themeofthecrop.com/plugins/restaurant-reservations/?utm_source=Plugin&utm_medium=Plugin%20Description&utm_campaign=Restaurant%20Reservations) for quicks tips on how to get started taking reservations.

= Addons =
[Custom Fields for Restaurant Reservations](https://themeofthecrop.com/plugin/custom-fields-restaurant-reservations/?utm_source=Plugin&utm_medium=Plugin%20Description&utm_campaign=Restaurant%20Reservations) - Add custom fields and edit your booking form with ease.

[Export Bookings for Restaurant Reservations](https://themeofthecrop.com/plugin/export-bookings-for-restaurant-reservations/?utm_source=Plugin&utm_medium=Plugin%20Description&utm_campaign=Restaurant%20Reservations) - Export your restaurant reservations to PDF and Excel/CSV files.

[MailChimp for Restaurant Reservations](https://themeofthecrop.com/plugin/mailchimp-restaurant-reservations/?utm_source=Plugin&utm_medium=Plugin%20Description&utm_campaign=Restaurant%20Reservations) - Subscribe emails from new restaurant reservations to your MailChimp mailing list.

= Developers =

This plugin is packed with hooks so you can extend it, customize it and rebrand it as needed. Take a look at the [Developer Documentation](http://doc.themeofthecrop.com/plugins/restaurant-reservations/developer/?utm_source=Plugin&utm_medium=Plugin%20Description&utm_campaign=Restaurant%20Reservations).

Development takes place on [GitHub](https://github.com/NateWr/restaurant-reservations), so fork it up.

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

This is a common request so I have written a small addon to do this for you. [Learn more](https://themeofthecrop.com/2015/01/08/simple-phone-validation-restaurant-reservations/).

= Can I translate the booking form? =

Yes, everything in this plugin can be translated using the standard translation process and software like PoEdit. If you're not familiar with that process, I'd recommend you take a look at the [Loco Translate](https://wordpress.org/plugins/loco-translate/) plugin, which provides a simple interface in your WordPress admin area for translating themes and plugins.

If you make a translation, please help others out by adding it to the [GitHub repository](https://github.com/NateWr/restaurant-reservations) so that I can distribute it for others.

= I set Early or Late Bookings restrictions, but I scan still book during that time =
Users with the Administrator and Booking Manager roles are exempt from these restrictions. This is so that they can make last-minute changes to bookings as needed. If you want to test the Early or Late Bookings restrictions, try logging out and testing.

= I want to add a field to the form. Can I do that? =

The addon, [Custom Fields for Restaurant Reservations](https://themeofthecrop.com/plugin/custom-fields-restaurant-reservations/?utm_source=Plugin&utm_medium=Plugin%20Description&utm_campaign=Restaurant%20Reservations), will allow you to add a field or modify some of the existing fields of the booking form. Developers who are comfortable coding up plugins for WordPress can add their own fields using the hooks provided. Developers can find a rough guide to coding a custom field in the answer to [this support request](https://wordpress.org/support/topic/edit-form-label-and-add-input-fields).

= More questions and answers =

Find answers to even more questions in the [FAQ](http://doc.themeofthecrop.com/plugins/restaurant-reservations/user/faq?utm_source=Plugin&utm_medium=Plugin%20Description&utm_campaign=Restaurant%20Reservations).

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

= 1.6.2 (2016-08-20) =
* Fix: Broken time picker introduced in 1.6.2

= 1.6.1 (2016-08-19) =
* Fix: Support location post ids in booking form shortcode
* Fix: JavaScript error if the time field is hidden
* Fix: Fix booking detail popup issue when used with custom fields addon
* Add: Notification template tag for location: {location}
* Add: Russian language translation. h/t Alexandra Kuksa
* Update: Spanish language translation. h/t Matias Rodriguez

= 1.6 (2016-06-20) =
* Fix: Currently visible notice in bookings list on mobile devices
* Fix: Conflict with WooCommerce that prevented booking managers from viewing bookings
* Add: Support multi-location bookings
* Add: Add reservation schema.org markup when Business Profile used
* Add: Allow custom first day of the week for date picker

= 1.5.3 (2016-03-25) =
* Fix: no bookings found when searching by start and end dates that are the same
* Add: clarify that early/late bookings restrictions don't apply to admins
* Add: Brazilian and Norwegian translations
* Update: Dutch translation
* Update: link to new online documentation
* Other: Tested for compatibility with WP 4.5

= 1.5.2 (2016-02-29) =
* Fix: booking managers can not confirm/reject bookings

= 1.5.1 (2016-02-19) =
* Fix: increase security of the quicklink feature for confirming/rejecting bookings
* Fix: Improve wp-cli compatibility

= 1.5 (2015-12-17) =
* Fix: pickadate iOS bug
* Fix: Bookings table's Today view didn't respect WordPress timezone setting
* Add: Allow bookings table columns to be toggled on/off
* Update: Convert message column/row drop-down to a details modal for all hidden columns
* Update: Put focus into message field when expanded in booking form

= 1.4.10 (2015-10-29) =
* Fix: Allow settings page required capability to be filtered later
* Fix: Compatibility issue with old versions of jQuery
* Add: Spanish translation from Rafa dMC

= 1.4.9 (2015-10-06) =
* Fix: iOS 8 bug with date and time pickers
* Add: newsletter signup prompt to addons page

= 1.4.8 (2015-08-20) =
* Add: WPML config file for improved multi-lingual compatibility
* Add: Danish translation by Yusef Mubeen
* Fix: Allow bookings managers to bypass early/late bookings restrictions
* Fix: No times available when latest time falls between last interval and midnight
* Updated: Improve bookings message view on small screens (adapt to 4.3 style)
* Updated: Simple Admin Pages lib to v2.0.a.10
* Updated: Dutch translation h/t Roy van den Houten and Clements Tolboom

= 1.4.7 (2015-07-02) =
* Add: Spanish translation from Joaqin Sanz Boixader
* Fix: Sorting of bookings by date and name in list table broken
* Fix: Custom late bookings values more than one day aren't reflected in date picker
* Fix: Norwegian doesn't include time picker translation for some strings
* Updated: German translation from Roland Stumpp
* Updated: pickadate.js language translations

= 1.4.6 (2015-06-20) =
* Add: Remove old schedule exceptions and sort exceptions by date
* Add: CSS class indicating type of booking form field
* Fix: Extended Latin can cause Reply-To email headers to fail in some clients
* Fix: PHP Warning when performing bulk or quick action in bookings panel
* Fix: Message row lingers after booking trashed in admin panel
* Updated .pot file

= 1.4.5 (2015-04-23) =
* Fix: Loading spinner not visible due to 4.2 changes
* Add: new addon Export Bookings released

= 1.4.4 (2015-04-20) =
* Fix: low-risk XSS security vulnerability with escaped URLs on admin bookings page

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

= 1.6.2 =
This update fixes a critical error introduced in v1.6.1 which broke the time picker.

= 1.6.1 =
This maintenance update adds a {location} tag for notifications, improves the location argument in the booking form shortcode and fixes a few minor bugs.

= 1.6 =
This is a major update that adds support for accepting bookings at multiple locations. View the online documentation for further details.

= 1.5.3 =
This update fixes a minor bug when searching for bookings by date, updates compatibilty for WP v4.5, and adds links to the new online documentation.

= 1.5.2 =
This update fixes a bug introduced in the last version which prevented Booking Managers from approving/rejecting reservations.

= 1.5.1 =
This update increases security for the quick link feature to confirm/reject bookings from the admin notification email.

= 1.5 =
This update adds the ability to configure which columns are visible in the bookings table. It works with the Custom Fields addon. If you have added fields using custom code, please read the release notification at themeofthecrop.com before updating.

= 1.4.10 =
This update includes a new Spanish translation and a few minor fixes. Updating isn't necessary for most people.

= 1.4.9 =
This update fixes a bug that made it difficult for iOS 8 users to select a date and time in their bookings. I strongly recommend you update.

= 1.4.8 =
This update fixes a bug that prevented bookings managers from editing bookings within the early/late schedule restrictions. It also fixed a bug with late opening times, added a WPML config file for better multi-lingual compatibility, updated translations, and improved the mobile view of the bookings list.

= 1.4.7 =
This update fixes a bug that prevented bookings from being sorted by date or name in the admin panel. It also updates some translations and improves support for custom late bookings values.

= 1.4.6 =
This update improves compatibility with an upcoming Custom Fields addon. It also fixes some minor bugs with extended Latin characters in emails and the admin list table, and removes expired schedule exceptions.

= 1.4.5 =
This update fixes a non-critical issue with the display of the loading spinner in the upcoming 4.2 version of WordPress.

= 1.4.4 =
This update fixes a low-risk XSS security vulnerability. It is low-risk because in order to exploit this vulnerability a user would need to have access to the bookings management panel in the admin area, which only trusted users should have.

= 1.4.3 =
This update adds datepickers to the start/end date filters in the admin bookings list and fixes a small error with the filters. It also fixes an issue with disabled weekdays when editing bookings. Dutch and German translation updates.

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
