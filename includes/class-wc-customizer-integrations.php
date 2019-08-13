<?php
/**
 * WooCommerce Customizer
 *
 * This source file is subject to the GNU General Public License v3.0
 * that is bundled with this package in the file license.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.gnu.org/licenses/gpl-3.0.html
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@skyverge.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade WooCommerce Customizer to newer
 * versions in the future. If you wish to customize WooCommerce Customizer for your
 * needs please refer to http://www.skyverge.com/product/woocommerce-customizer/ for more information.
 *
 * @author      SkyVerge
 * @copyright   Copyright (c) 2013-2019, SkyVerge, Inc.
 * @license     http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License v3.0
 */

defined( 'ABSPATH' ) or exit;

/**
 * Class WC_Customizer_Integrations.
 *
 * Adds integration code for other WooCommerce extensions.
 *
 * @since 2.6.0
 */
class WC_Customizer_Integrations {


	/**
	 * WC_Customizer_Integrations constructor.
	 *
	 * @since 2.6.0
	 */
	public function __construct() {

		if ( WC_Customizer::is_plugin_active( 'woocommerce-product-bundles.php' ) ) {

			add_filter( 'wc_customizer_settings', array( $this, 'add_bundles_settings' ) );
			add_filter( 'woocommerce_product_add_to_cart_text', array( $this, 'customize_bundle_add_to_cart_text' ), 150, 2 );
		}
	}


	/**
	 * Adds settings when Product Bundles is active.
	 *
	 * @since 2.6.0
	 *
	 * @param array $settings the settings array
	 * @return array updated settings
	 */
	public function add_bundles_settings( $settings ) {

		$new_settings = array();

		foreach ( $settings as $section => $settings_group ) {

			$new_settings[ $section ] = array();

			foreach ( $settings_group as $setting ) {

				$new_settings[ $section ][] = $setting;

				if ( 'shop_loop' === $section && isset( $setting['id'] ) && 'grouped_add_to_cart_text' === $setting['id'] ) {

					// insert bundle settings after the grouped product text
					$new_settings[ $section ][] = array(
						'id'       => 'bundle_add_to_cart_text',
						'title'    => __( 'Bundle Product', 'woocommerce-customizer' ),
						'desc_tip' => __( 'Changes the add to cart button text for bundle products on all loop pages', 'woocommerce-customizer' ),
						'type'     => 'text'
					);
				}
			}
		}

		return $new_settings;
	}


	/**
	 * Customizes the add to cart button for bundle products.
	 *
	 * @since  2.6.0
	 *
	 * @param string $text add to cart text
	 * @param WC_Product $product product object
	 * @return string modified add to cart text
	 */
	public function customize_bundle_add_to_cart_text( $text, $product ) {

		if ( isset( wc_customizer()->filters['bundle_add_to_cart_text'] ) && $product->is_type( 'bundle' ) ) {

			// bundle add to cart text
			$text = wc_customizer()->filters['bundle_add_to_cart_text'];
		}

		return $text;
	}


}
