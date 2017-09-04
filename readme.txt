=== Really Simple SSL social ===
Contributors:RogierLankhorst
Requires at least: 4.2
License: GPL2
Tested up to: 4.8
Stable tag: 2.0.5

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
= 2.0.5 =
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
