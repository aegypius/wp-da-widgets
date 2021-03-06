=== deviantART Widgets ===
Contributors: aegypius
Tags: widget, gallery, image
Donate link: http://github.com/aegypius/wp-da-widgets
Requires at least: 2.8.0
Tested up to: 2.9.2
Stable tag: 0.1.7

Displays deviantART feeds as wordpress widgets

== Description ==

This plugins allows display of deviantArt's feeds such as Journals, Favourites and Galleries into widgets. Cache and thumbnail generation is supported.

This plugin is actually in 'Beta', many changes can occure during the next releases.

= Testers =
* [Honigkuchenwolf](http://hokuwo.de/ "Wolfsgeheul")
* [Flex](http://www.flex-it.fr/ "Work It Make It Flex It")

== Screenshots ==

Nothing yet.

== Installation ==

Installing da-widgets should take fewer than 3 minutes

1. Upload da-widgets directory to `/wp-content/plugins/`
2. Activate the plugin through the 'Plugins' menu in Wordpress
3. Go to 'Widgets' menu in Wordpress and place in a widget placeholder

== Upgrade Notice ==

* Rembember to clean cache after an upgrade

== Frequently Asked Questions ==

Nothing yet.

== Changelog ==

= 0.1.7 =
* Adding a Changelog pannel to settings tabs

= 0.1.6 =
* Issue #3: Adding basic support for category filtering on galleries and favourites

= 0.1.5 =
* Issue #1: Fixed
* Added an option to enable debugging in global options
* Added the possibility to customize CSS styles for the widget in global options
* Fixing locale calls to allow string translation in differents languages (help granted !)

= 0.1.4 =
* Issue #1 : Adding an "Empty cache" button to clean the plugin cache
* Issue #1 : Adding more tests to check if the plugin is usable
* Issue #1 : Updating thumbnail generation for Gallery mode

= 0.1.3 =
* Issue #1 : Safe Mode problem
* Prevents "litteratures" to show in Favourites and Galleries

= 0.1.2 =
* Changing readme.txt tags
* Fixing cache issues when wp-content/cache is not writeable.

= 0.1.1 =
* Removes useless settings
* Fix url to thumbnails to uses Wordpress url instead of absolute one

= 0.1 =
* Initial release
* Cache support (default: wp-content/cache)
* Thumbnails generation support (default: 80x80/png)
* Supports deviantArt Journals, Galleries and Favourites
