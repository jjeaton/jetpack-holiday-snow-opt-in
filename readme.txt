=== Jetpack Holiday Snow Opt-In ===

Contributors: jjeaton  
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=7DR8UF55NRFTS  
Tags: jetpack, accessibility  
Requires at least: 3.7  
Tested up to: 4.6.1  
Stable tag: 0.1.5  
License: GPLv2 or later  
License URI: http://www.gnu.org/licenses/gpl-2.0.html  

Make Jetpack's Holiday Snow feature accessible by only showing it if user has opted-in by clicking a snowflake displayed on the page.

== Description ==

Make Jetpack's Holiday Snow feature accessible by only showing it if user has opted-in by clicking a snowflake displayed on the page. Users can also disable the snow if they get tired of it by clicking the snowflake again. The snow is disabled by default, and the snow status is stored in a cookie so it will remain on or off until the user decides.

Requires Jetpack (obviously)

If you're a user and interested in being able to block the Jetpack Holiday Snow from loading on any site you visit, check out my Chrome Extension: [Block Holiday Snow](https://github.com/jjeaton/block-holiday-snow).

== Installation ==

Normal installation method through the Plugins menu.

== Frequently Asked Questions ==

Nothing has been frequently asked, yet.

== Screenshots ==

1. Example of the snowflake snow control shown on the front end.

== Changelog ==

= 0.1.5 - 2016-12-04 =

* Bump tested up to version to 4.6.1.
* Fix textdomain path

= 0.1.4 - 2016-12-04 =

* Fixed theme compatibility with increased z-index.
* Minor code cleanup.
* The snow icon no longer spins.
* Added POT file and removed plugin textdomain call.
* Tested up to WP 4.6.1.

= 0.1.3 - 2015-04-08 =

* Fixed Jetpack check to include check for `jetpack_is_holiday_snow_season` function.

= 0.1.2 =

* Fixed Jetpack check that only worked on latest Jetpack version.

= 0.1.1 =

* Modified the check for Jetpack.
* Snowflake now disappears automatically once the Jetpack holiday snow season is over.

= 0.1.0 =

* Initial release
