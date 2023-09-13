=== Give - iATS Gateway ===
Contributors: givewp
Tags: donations, donation, ecommerce, e-commerce, fundraising, fundraiser, iats, gateway
Requires at least: 4.8
Tested up to: 6.3
Stable tag: 1.0.6
Requires Give: 2.4.0
License: GPLv3
License URI: https://opensource.org/licenses/GPL-3.0

iATS Gateway Add-on for Give.

== Description ==

This plugin requires the Give plugin activated to function properly. When activated, it adds a payment gateway for iatspayments.com.

== Installation ==

= Minimum Requirements =

* WordPress 4.8 or greater
* PHP version 5.3 or greater
* MySQL version 5.0 or greater
* Some payment gateways require fsockopen support (for IPN access)

= Automatic installation =

Automatic installation is the easiest option as WordPress handles the file transfers itself and you don't need to leave your web browser. To do an automatic install of Give, log in to your WordPress dashboard, navigate to the Plugins menu and click Add New.

In the search field type "Give" and click Search Plugins. Once you have found the plugin you can view details about it such as the the point release, rating and description. Most importantly of course, you can install it by simply clicking "Install Now".

= Manual installation =

The manual installation method involves downloading our donation plugin and uploading it to your server via your favorite FTP application. The WordPress codex contains [instructions on how to do this here](http://codex.wordpress.org/Managing_Plugins#Manual_Plugin_Installation).

= Updating =

Automatic updates should work like a charm; as always though, ensure you backup your site just in case.

== Changelog ==

= 1.0.6: September 13th, 2023 =
* Enhancement: Remove gulp to use WP CLI in buld process
* Enhancement: Bump ini from 1.3.4 to 1.3.7
* Fix: Resolve fatal error caused by a library that is not compatible with PHP 8.1

= 1.0.5: June 3rd, 2019 =
* Tweak: Adjusted the plugin's settings screens code logic to work with GiveWP Core 2.5.0+ which deprecates the old methods used to register settings in previous versions of this add-on.
* Tweak: Removed the outdated option to set a gateway label which is now part of GiveWP Core.

= 1.0.4: November 13th, 2018 =
* Tweak: Optimized how the plugin is activated to depend on Give Core and prevent potential activation issues when requirements not met.
* Fix: Link to documentation updated so it doesn't go to 404.

= 1.0.3: May 3rd, 2018 =
* Tweak: Updated deprecated hooks within Give 2.1+ - Please update to the latest Give core!

= 1.0.2 =
* Fix: Compatiblity with Fee Recovery so that the proper amount with fees is sent to the gateway.
* Fix: iATS requires two decimal places. When this gateway is active it forces two decimal places for donation amounts.

= 1.0.1 =
* Fix: Resolved issue with large amounts being sent with thousands separator which iATS' API doesn't expect and therefore would incorrectly process.
* Fix: PHP notice for using deprecated give_output_error() rather than Give_Notices().

= 1.0 =
* Initial plugin release. Yippee!