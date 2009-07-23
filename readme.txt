=== Woopra Analytics Plugin ===
Contributors: eliekhoury, markjaquith, ShaneF
Web site: http://www.woopra.com
Tags: statistics, analytics, stats, real-time
Requires at least: 2.7.0
Tested up to: 2.8.2
Stable tag: 1.4.1.1

This plugin adds Woopra's real-time analytics to any WordPress installation.

== Description ==

Woopra is the world's most comprehensive, information rich, easy to use, real-time Web tracking and analysis application.

Features include:

*   Live Tracking and Web Statistics
*   A rich user interface and client monitoring application
*   Real-time Analytics
*   Manage Multiple Blogs and Websites
*   Deep analytic and search capabilities
*   Click-to-chat
*   Visitor and member tagging
*   Real-time notifications
*   Easy Installation and Update Notification

== Installation ==

These installation instructions assume you have an active account established on Woopra.com.  If not, please visit the site and sign up for service.

Using the manual method:

1. Extract the Woopra.zip file to a location on your local machine
2. Upload the `woopra` folder and all contents into the `/plugins/` directory

Using the automatic method:

1. Click on 'Download' or 'Upgrade'
2. Wait for WordPress to acknowledge that it is on your system amd ready to activate.

After step 2 (of either method):

3. Activate the plugin through the 'Plugins' menu in WordPress
4. Configure your API key and event settings in the Woopra Settings menu
(Your API key can be found in the Members area on Woopra.com)

For Version 1.4.1.x, Event Processing in the Javascript has been disabled until further testing has completed.

IMPORTANT NOTE: In order for the WordPress Plugin to work, your WordPress theme must have the following code immediately before the </BODY> element in the footer.php file:

    `<?php wp_footer(); ?>`

Without this function, the Woopra application will fail to track users who visit your site.

For more detailed installation instructions refer to: http://www.woopra.com/installation-guide/

For more support refer to: http://www.woopra.com/forums/

== Frequently Asked Questions == 

Q. I do not see 'Woopra Analytics' link.
A. Make sure the plugin is active.

Q. I can not see any of the flash charts.
A. Make sure you have flash installed and be sure you are not stopping or blocking flash from loading.

Q. How do I get an API Key?
A. If you have downloaded this and have never signed up, please visit http://www.woopra.com to sign up. Once your site is approved you can get your API key from your members section.

== Changelog ==

= 1.4.1.1 (07-23-09) =

* [BUG] "Parse error: parse error in \woopra\inc\chart.php on line 130": Reverted back to 4.3.0 format.
* [BUG] "Parse error: parse error, expecting T_OLD_FUNCTION' orT_FUNCTION' or T_VAR' or'}'' in \plugins\woopra\inc\chart.php on line 194": Moved array values into a global var in the class.
* [BUG] Forgot hook_action name for toplevel support.
* [BUG] Removed index.html files from both the 1.4.1 tag and truck. Was causing errors during automatic upgrade.
* [BUG] Ignoreing admin visits fixed.
* [BUG] Detection is admin section is now being set correctly.
* [BUG] Removed all PHP 5 stuff. :(
* [BUG] Fixed Referrers Subsections: Regular Referrers, Search Engines, Emails, Social Bookmarks, and Social Networks when trying to expand to view the charts now works.
* [BUG] API Key now transfers correctly.

= 1.4.1 (07-20-09) =

* [NEW] Woopra moved into a 'php class' format
* [NEW] Woopra event tracking made more universal. Moved to the 'wp' hook for tracking
* [NEW] Woopra settings moved into a single varabile
* [NEW] XML-API version 2.1 being used.
* [NEW] jQuery used for Datepicker: ui.datepicker.js
* [UPGRADE] Woopra now requires at least WordPress 2.7.x
* [UPGRADE] All functions are now documented
* [UPGRADE] Open-Flash-Charts Version 2
* [BUG] Woopra can now operate out of any directory. Doesn't matter the location.
