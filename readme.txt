=== WooCommerce Customizer ===
Contributors: SkyVerge, maxrice, tamarazuk, chasewiseman, nekojira, beka.rice
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_xclick&business=paypal@skyverge.com&item_name=Donation+for+WooCommerce+Customizer
Tags: woocommerce, woocommerce shop, woocommerce filters, woocommerce text
Requires at least: 4.1
Tested up to: 4.7.4
WC requires at least: 2.5.5
WC tested up to: 3.0.7
Stable tag: 2.5.0
License: GPLv3 or later
License URI: http://www.gnu.org/licenses/gpl-3.0.html

Helps you customize WooCommerce without writing any code!

== Description ==

WooCommerce includes tons of filters to let you customize button text, labels, and more, but you have to write PHP code to use them. This plugin provides a settings page where you can add your customizations and save them without needing to write any code or modify any templates, which is helpful for quick change testing.

> **Requires** WooCommerce 2.5.5+

Here are some customizations you can make:

* Add to Cart button text for different product types (within the shop loop and on a single product page)
* Sales badge text for the shop or product pages
* The number of products displayed per page
* Heading text for the "Product Description" and "Additional Information" tab.
* Checkout page coupon / login text
* Checkout page "Create Account" checkbox default
* Checkout page "Submit Order" button text
* Tax Label text
* Placeholder image source

To make these changes, go to **WooCommerce &gt; Settings** and view the "Customizer" tab.

> **Note**: This plugin does not support being network activated on multisite. To use this on a multisite network, you must activate the plugin individually on each site.

= Support Details =
We do support our free plugins and extensions, but please understand that support for premium products takes priority. We typically check the forums every few days (with a maximum delay of one week).

= More Details =
 - See the [product page](http://www.skyverge.com/product/woocommerce-customizer/) for full details.
 - View more of SkyVerge's [free WooCommerce extensions](http://profiles.wordpress.org/skyverge/)
 - View all [SkyVerge WooCommerce extensions](http://www.skyverge.com/shop/)

== Installation ==

1. You can (a) Search Plugins &gt; Add New for "WooCommerce Customizer", (b) Upload `woocommerce-customizer` folder to the `/wp-content/plugins/` directory, or (c) upload the zip file via the "Plugins &gt; Add New" menu
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Go to the 'Customizer' tab under WooCommerce &gt; Settings
4. Start customizing!

== Frequently Asked Questions ==

= Why can't I customize xyz? =

Most likely because a filter does not yet exist within WooCommerce, or a filter does exist but is too complicated to be of use with this plugin.

= Why don't the settings show up under the WooCommerce Settings? =

Do you have this network activated for a multisite installation? If so, you'll need to remove the plugin, then activate it on each child site as needed, as the plugin does not support being network activated.

= I found a bug! What do I do? =

Please submit an issue on [GitHub](https://github.com/skyverge/woocommerce-customizer/) along with a description of the problem so we can fix it :)

= Can I contribute to the plugin? =

Of course! Please fork the [GitHub](https://github.com/skyverge/woocommerce-customizer/) repository and send a pull request.

== Screenshots ==

1. Settings Page to start customizing!

== Changelog ==

= 2.5.0 =
* Feature - Added sales badge text customization

= 2.4.0 =
* Fix - Image placeholder replacements are only shown in the shop, not in the WP admin
* Localization - Includes Persian translation, props [Saakhtani team](http://saakhtani.ir/)!
* Misc - Added support for WooCommerce 3.0
* Misc - Removed support for WooCommerce 2.4

= 2.3.1 =
* Fix - Fixes "headers already sent" error if WooCommerce is out of date

= 2.3.0 =
* Misc - Added support for WooCommerce 2.6
* Misc - Removed support for WooCommerce 2.3

= 2.2.0 =
* Misc - Added support for WooCommerce 2.5
* Misc - Removed support for WooCommerce 2.2

= 2.1.1 =
* Misc - Standardize the translation string

= 2.1.0 =
* Misc - WooCommerce 2.3 Compatibility

= 2.0.1 =
* Fix error when upgrading to WooCommerce 2.2

= 2.0.0 =
* Added Checkout "Create Account" checkbox default customization
* Added Placeholder image source customization
* Moved settings to WooCommerce > Settings > Customizer
* WooCommerce 2.2 Compatibility
* Localization - Text domain changed from `wc-customizer` to `woocommerce-customizer`

= 1.2.1 =
* Fix missing compatibility class error

= 1.2 =
* Fix issues with add to cart button text customizations in WooCommerce 2.1

= 1.1.1 =
* WooCommerce 2.1 Compatibility

= 1.1 =
* Refactor to support the upcoming WooCommerce 2.1 beta
* Localization - Text domain changed from `wc_customizer` to `wc-customizer` and loaded properly on `init` hook

= 1.0.1 =
* Add two new filters for customizing the Product Description and Additional Information tab titles
* Fix TipTips on Customizer page

= 1.0 =
* Initial release
