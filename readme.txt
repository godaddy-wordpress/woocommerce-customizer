=== WooCommerce Customizer ===
Contributors: maxrice, justinstern, tamarazuk, skyverge
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_xclick&business=paypal@skyverge.com&item_name=Donation+for+WooCommerce+Customizer
Tags: woocommerce
Requires at least: 3.8
Tested up to: 4.0
Stable tag: 1.3.0
License: GPLv3 or later
License URI: http://www.gnu.org/licenses/gpl-3.0.html

Helps you customize WooCommerce without writing any code!

== Description ==

WooCommerce includes a lot of filters so you can customize button text, labels, and more -- but you have to write PHP
code to use them. This plugin provides a settings page where you can add your customizations and save them without
needing to write any code or modify any templates, which is helpful for quick change testing.

Here's the list of customizations you can make:

*   Add to Cart button text for all product types (within the shop loop and on a single product page)
*   The number of products displayed per page
*   Heading text for the 'Product Description' and 'Additional Information' tab.
*   Checkout page coupon / login text
*   Checkout page 'Submit Order' button text
*   Tax Label text

== Installation ==

1. Upload `woocommerce-customizer` folder to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Go to 'Customizer' under the WooCommerce menu
4. Start customizing!

== Frequently Asked Questions ==

= Why can't I customize xyz? =

Most likely because a filter does not yet exist within WooCommerce, or a filter does exist but is too complicated to be of use with this plugin.

= I found a bug! What do I do? =

Please submit an issue on [Github](https://github.com/skyverge/woocommerce-customizer/) along with a description of the problem so we can fix it :)

= Can I contribute to the plugin? =

Yes! Fork the plugin on [Github](https://github.com/skyverge/woocommerce-customizer/) and send a pull request.

== Screenshots ==

1. Settings Page
2. Customizations galore!

== Changelog ==

= 1.2.1-1 =
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
