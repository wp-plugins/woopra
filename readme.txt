=== Woopra Analytics Plugin ===
Contributors: eliekhoury, markjaquith, ShaneF, marioac
Web site: http://www.woopra.com
Tags: statistics, analytics, stats, real-time
Requires at least: 2.7.0
Tested up to: 3.0
Stable tag: 1.4.6

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
2. Wait for WordPress to acknowledge that it is on your system and ready to activate.

After step 2 (of either method):

3. Activate the plugin through the 'Plugins' menu in WordPress
4. Configure your API key and event settings in the Woopra Settings menu
(Your API key can be found in the Members area on Woopra.com)

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
A. You must be invited (for free account) or sign up for a paid account to use Woopra.

Q. I can not see anything under 'Woopra Analytics' even though I have an API Key.
A. Make sure your site is registered exactly like it should be in the Woopra Members area that matches your WordPress SiteUrl. (A fix will be along shortly.)

== Changelog ==

= 1.4.6 (06-27-10) =
* [BUG] Fixed another issue with setIdleTimeout
* [BUG] Fixed a division by 0 issue
* [NEW] Added a few comments on some errors and how to resolve them

= 1.4.5 (06-25-10) =
* [NEW] Added support for Curl in case fopen is disabled on server.
* [BUG] Fixed setIdleTimeout javascript code
* [BUG] Fixed API Url, all requests for analytics should now be working
* [CHANGE] Added event name to javascript tracking code
* [CHANGE] Added extra error checks and handling code

= 1.4.3.2 (12-21-09) =

* [SECRUITY UPDATE] Removed 'ofc_upload_image.php' from the Open Flash Directory. Remove this file if you do upgrading manually.
* [SVN CHANGE] Made a 1.4 branch and moved 'trunk' to the new development version of '1.5.x'

= 1.4.3.1 (10-15-09) =

* [ADDED] New Visitor Percentage
* [REMOVED] Removed check 'Site not part of...'.

= 1.4.3 (10-12-09) =

* [NEW] Adding Error Handling Code
* [ACTIVATED] Uncommented code for events to work now! (Posting Comment, Searching)
* [BUG] Javascript for the Frontend again is updated to work with the new Woopra.js master file.
* [BUG] Fixed 'Site not part of...'. In most cases the XML data returned was not being interpreted correctly.
* [CHANGE] Events code re-written from scratch.
* [CHANGE] No more dropdown for the 'Timeout' feild. This value must be a whole number. Numbers will round down to the lower number if it's set a fraction. Setting it to '0' (Zero) will remove the override timeout.

= 1.4.2 (08-24-09) =

* [NEW] New SuperTab - Tagged Visitors - You can aggregate by name. More options to come in the future.
* [NEW] New Option - Auto Timeout - Up to 600 seconds (Default) for manually setting the timeout.
* [CHANGE] Changed to the new javascript convention. No change it what is outputed.

= 1.4.1.1 (07-23-09) =

* [BUG] "Parse error: parse error in \woopra\inc\chart.php on line 130": Reverted back to 4.3.0 format.
* [BUG] "Parse error: parse error, expecting T_OLD_FUNCTION' orT_FUNCTION' or T_VAR' or'}'' in \plugins\woopra\inc\chart.php on line 194": Moved array values into a global var in the class.
* [BUG] Forgot hook_action name for toplevel support.
* [BUG] Removed index.html files from both the 1.4.1 tag and trunk. Was causing errors during automatic upgrade.
* [BUG] Ignoring admin visits fixed.
* [BUG] Detection in admin section is now being set correctly.
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
