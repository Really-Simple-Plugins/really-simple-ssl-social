=== Really Simple SSL social ===
Contributors: RogierLankhorst
Requires at least: 5.0
License: GPL2
Tested up to: 5.7
Requires PHP: 5.6
Stable tag: 4.1.1

Recovers Facebook likes for your website after migration to https.

== Description ==
Recovers the lost Facebook likes that can be lost when you change your domain to https.

= Installation =
* Install the Really Simple SSL plugin, which is needed with this one.
* Go to “plugins” in your Wordpress Dashboard, and click “add new”
* Click “upload”, and select the zip file you downloaded after the purchase.
* Activate
* Navigate to “settings”, “SSL”.
* Click “license”
* Enter your license key, and activate.

For more information: go to the [website](https://www.really-simple-ssl.com/), or
[contact](https://www.really-simple-ssl.com/contact/) me if you have any questions or suggestions.

== Frequently Asked Questions ==

== Changelog ==
= 4.1.1 =
* Warning on deactivating license

= 4.1.0 =
* PHP docs
* Compatibility with RSSSL 4.0.0

= 4.0.7 =
* Tested up to WordPress 5.5

= 4.0.6 =
* Fixed a bug where likes showed shares in built-in buttons

= 4.0.5 =
* Added option to insert og:url when recovering shares for existing buttons

= 4.0.4 =
* Updated Facebook API to v3.3
* Added option to recover shares on blog overview (archive) pages

= 4.0.3=
* Added WhatsApp and Yummly buttons to native theme
* Tweak: added share cache time option to native buttons
* Fix: sharecount now refreshes when native FB button is clicked
* Fix: built-in button CSS is no longer enqueued when using native buttons
* Fix: fixed a bug where custom CSS was not applied

= 4.0.2 =
* Fix: native like button now shows correctly when using the native buttons with like option
* Tweak: added constant RSSSL_SOC_NO_ACE to prevent ace CSS editor from loading

= 4.0.1 =
* Fix: fixed an issue where the licensing tab wouldn't appear when plugin is used as standalone plugin

= 4.0 =
* Added more share button themes: color rounded, color square, dark round, dark square, color sidebar and dark sidebar
* Added the ability to show native sharing buttons with API count
* Added Gutenberg support, buttons can be added as block
* Plugin is now able to run as stand-alone, without requiring Really Simple SSL free
* Tweak: no longer shows notices on Gutenberg edit screen

= 3.0.11 =
* Fix: preg_url should be replaced by normal

= 3.0.10 =
* Fix: settings link not implemented correctly

= 3.0.9 =
* Fix: exclude for Facebook crawlers not working correctly

= 3.0.8 =
* Fix: recovery mode for homepage was checking for blogs overview page as well.
* Tweak: Added shortcode for raw counts [rsssl_share_count type='facebook']

= 3.0.7 =
* Improved support for recovering Pinterest pins
* Fix: replaced a number of incorrect $preg_url_http uses with $http_url

= 3.0.6 =
* Fix: regex replace replaced FB data-url in incorrect format

= 3.0.5 =
* Added support for https://wordpress.org/plugins/shariff-sharing/

= 3.0.4 =
* Fix: linkedin shares were retrieved over http, which has stopped working
* Tweak: dropped linkedin aggragation, as linkedin returns all shares for both URL's
* Tweak: removed og:url option, now default enabled.

= 3.0.3 =
* Add this support

= 3.0.2 =
* Added support for Simple Share Buttons adder, assuming they add a filter to use
* Fixed an error in the JetPack sharing filters
* Added WhatsApp share button
* Added Yummly share button

= 3.0.1 =
* Added a dedicated tab for social settings
* Fix: enlarged and centered the count on buttons
* Tweak: button position on post type only shown when buttons are set to display inline
* Tweak: added https:// to http:// rewrite for Facebook user agent

= 3.0.0 =
* Changed the look of built-in buttons
* Added an option to either use the old styling or new styling
* Added an option to display the buttons on post or as a left sidebar
* Added default settings when enabling the built-in buttons

= 2.0.8 =
* Fix: buttons not showing up on page post type after fix for archive pages in 2.0.7

= 2.0.7 =
* Fix: shares from built in buttons also appearing in archive pages

= 2.0.6 =
* Fixed: not logged in user click would not result in clearing of the share cache

= 2.0.5 =
* Notice: please check the selected social services after upgrading!
* Tweak: Added pinterest to share buttons
* Tweak: changed default share cache expiration to one day
* Tweak: easy select the social services you want to use
* Fix: Google shares not recovering due to change in Gplus widget

= 2.0.4 =
* Tweak: share cache is now cleared on click of the share button

= 2.0.3 =
* Changed share retrieval so cached shares are shown instantly, after page load the most recent shares are retrieved if they are not cached.
* With the filter rsssl_social_cache_expiration you can change the expiration timeout (default one hour)
* With the constant rsssl_social_no_cache you can force to refresh the shares each request.

= 2.0.2 =
* Added linkedin to built in share retrieval.

= 2.0.1 =
* Fixed fb built in share count retrieval bug

= 2.0.0 =
* Tweak: when built in share button is clicked, counter is increased
* Tweak: counter for more than 1000 likes are shortened to 1k or 1m
* Created option to override with buttons with template

= 1.9.0 =
* Tweak: added the option to insert custom share buttons from Really Simple Social, which will retrieve likes from both http and https url.

= 1.0.1 =
Changed retrieval of post date to unix time.

= 1.0.0 =
Initial release

== Upgrade notice ==

== Screenshots ==
