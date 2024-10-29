=== ASAP 507 Panama Shipping ===
Contributors: t0gokj88ziy2
Tags: panama, envios, asap, 507
Requires at least: 6.0.0
Tested up to: 6.6.1
Requires PHP: 7.4
Stable tag: 3.6.2
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

This plugin allows ASAP corporate customers to automate creating shipping orders into ASAP platform.

== Description ==

This plugin allows ASAP corporate customers to integrate and automate creting shipping orders into ASAP's platform.

In order to work shop manager should:
1. Create a fixed priced shipping method
2. Name that method 'ASAP'.
3. Request credentials for API connection for ASAP

Then, on each paid order it should update the order status to 'Complete' in order to create the shipping request.

The API integration relies on Google Maps Locations API. User should provide this key along with ASAP's shared secret and user's tokens.

If selected the plugin will also declares Panama's provinces to WooCommerce enabling a dropdown in the 'states' (pronvicias) field on both billing and shipping forms at checkout.

WooCommerce is required for this plugin to work.

https://asap507.com

https://asap-plugin-woocomerce.document360.io/docs


== Installation ==

This software should be treated as a WP plugin.


== Changelog ==
= Version 3.6.2 =
- fix compatibility to parameter order id to woocomerce


= Version 3.6.1 =
- Minor fixes in order management


= Version 3.6.0 =
- New service to shipping dynamic
- Improvements in connection messages with APIs


= Version 3.3.0 =
- New service to shipping dynamic


= Version 3.2.0 =
- Fix bugs to install plugins


= Version 3.1.0 =
- Add Phone to config plugin
- Refactor code.

= 3.0.0 =
- Change assets to plugins page
- Clean code and structure php class
- Refactor code.

= 2.0.0 =
ASAP Shipping create Shipping box always present in orders. Now you can create a shipping with us no matter if the user selected or not ASAP as a shipping method.

= 0.0.1 =
* Initial Release.
