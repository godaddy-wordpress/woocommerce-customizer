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
 * @package     WC-Customizer/Classes
 * @author      SkyVerge
 * @copyright   Copyright (c) 2013-2017, SkyVerge, Inc.
 * @license     http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License v3.0
 */

defined( 'ABSPATH' ) or exit;

/**
 * Settings
 *
 * Adds UX for adding/modifying customizations
 *
 * @since 2.0.0
 */
class WC_Customizer_Settings extends WC_Settings_Page {


	/**
	 * Add various admin hooks/filters
	 *
	 * @since 2.0.0
	 */
	public function __construct() {

		$this->id    = 'customizer';
		$this->label = __( 'Customizer', 'woocommerce-customizer' );

		parent::__construct();

		$this->customizations = get_option( 'wc_customizer_active_customizations', array() );
	}


	/**
	 * Get sections
	 *
	 * @since 2.0.0
	 * @return array
	 */
	public function get_sections() {

		return array(
			'shop_loop'    => __( 'Shop Loop', 'woocommerce-customizer' ),
			'product_page' => __( 'Product Page', 'woocommerce-customizer' ),
			'checkout'     => __( 'Checkout', 'woocommerce-customizer' ),
			'misc'         => __( 'Misc', 'woocommerce-customizer' )
		);
	}


	/**
	 * Render the settings for the current section
	 *
	 * @since 2.0.0
	 */
	public function output() {

		$settings = $this->get_settings();

		// inject the actual setting value before outputting the fields
		// ::output_fields() uses get_option() but customizations are stored
		// in a single option so this dynamically returns the correct value
		foreach ( $this->customizations as $filter => $value ) {

			add_filter( "pre_option_{$filter}", array( $this, 'get_customization' ) );
		}

		WC_Admin_Settings::output_fields( $settings );
	}


	/**
	 * Return the customization value for the given filter
	 *
	 * @since 2.0.0
	 * @return string
	 */
	public function get_customization() {

		$filter = str_replace( 'pre_option_', '', current_filter() );

		return isset( $this->customizations[ $filter ] ) ? $this->customizations[ $filter ] : '';
	}


	/**
	 * Save the customizations
	 *
	 * @since 2.0.0
	 */
	public function save() {

		foreach ( $this->get_settings() as $field ) {

			// skip titles, etc
			if ( ! isset( $field['id'] ) ) {
				continue;
			}

			if ( ! empty( $_POST[ $field['id'] ] ) ) {

				$this->customizations[ $field['id'] ] = wp_kses_post( stripslashes( $_POST[ $field['id'] ] ) );

			} elseif ( isset( $this->customizations[ $field['id'] ] ) ) {

				unset( $this->customizations[ $field['id'] ] );
			}
		}

		update_option( 'wc_customizer_active_customizations', $this->customizations );
	}


	/**
	 * Return admin fields in proper format for outputting / saving
	 *
	 * @since 1.1
	 * @return array
	 */
	public function get_settings() {

		$settings = array(

			'shop_loop' =>

				array(

					array(
						'title' => __( 'Add to Cart Button Text', 'woocommerce-customizer' ),
						'type'  => 'title'
					),

					array(
						'id'       => 'add_to_cart_text',
						'title'    => __( 'Simple Product', 'woocommerce-customizer' ),
						'desc_tip' => __( 'Changes the add to cart button text for simple products on all loop pages', 'woocommerce-customizer' ),
						'type'     => 'text'
					),

					array(
						'id'       => 'variable_add_to_cart_text',
						'title'    => __( 'Variable Product', 'woocommerce-customizer' ),
						'desc_tip' => __( 'Changes the add to cart button text for variable products on all loop pages', 'woocommerce-customizer' ),
						'type'     => 'text'
					),

					array(
						'id'       => 'grouped_add_to_cart_text',
						'title'    => __( 'Grouped Product', 'woocommerce-customizer' ),
						'desc_tip' => __( 'Changes the add to cart button text for grouped products on all loop pages', 'woocommerce-customizer' ),
						'type'     => 'text'
					),

					array(
						'id'       => 'out_of_stock_add_to_cart_text',
						'title'    => __( 'Out of Stock Product', 'woocommerce-customizer' ),
						'desc_tip' => __( 'Changes the add to cart button text for out of stock products on all loop pages', 'woocommerce-customizer' ),
						'type'     => 'text'
					),

					array( 'type' => 'sectionend' ),

					array(
						'title' => __( 'Sale Flash', 'woocommerce-customizer' ),
						'type'  => 'title'
					),

					array(
						'id'       => 'loop_sale_flash_text',
						'title'    => __( 'Sale badge text', 'woocommerce-customizer' ),
						'desc_tip' => __( 'Changes text for the sale flash on all loop pages. Default: "Sale!"', 'woocommerce-customizer' ),
						'type'     => 'text',
						/* translators: Placeholders: %1$s - <code>, %2$s - </code> */
						'desc'     => sprintf( __( 'Use %1$s{percent}%2$s to insert percent off, e.g., "{percent} off!"', 'woocommerce-customizer' ), '<code>', '</code>' ) . '<br />' . __( 'Shows "up to n%" for grouped or variable products if multiple percentages are possible.', 'woocommerce-customizer' ),
					),

					array( 'type' => 'sectionend' ),

					array(
						'title' => __( 'Layout', 'woocommerce-customizer' ),
						'type'  => 'title'
					),

					array(
						'id'       => 'loop_shop_per_page',
						'title'    => __( 'Products displayed per page', 'woocommerce-customizer' ),
						'desc_tip' => __( 'Changes the number of products displayed per page', 'woocommerce-customizer' ),
						'type'     => 'text'
					),

					array(
						'id'       => 'loop_shop_columns',
						'title'    => __( 'Product columns displayed per page', 'woocommerce-customizer' ),
						'desc_tip' => __( 'Changes the number of columns displayed per page', 'woocommerce-customizer' ),
						'type'     => 'text'
					),

					array(
						'id'       => 'woocommerce_product_thumbnails_columns',
						'title'    => __( 'Product thumbnail columns displayed', 'woocommerce-customizer' ),
						'desc_tip' => __( 'Changes the number of product thumbnail columns displayed', 'woocommerce-customizer' ),
						'type'     => 'text'
					),

					array( 'type' => 'sectionend' )

				),

			'product_page' =>

				array(

					array(
						'title' => __( 'Tab Titles', 'woocommerce-customizer' ),
						'type'  => 'title'
					),

					array(
						'id'       => 'woocommerce_product_description_tab_title',
						'title'    => __( 'Product Description', 'woocommerce-customizer' ),
						'desc_tip' => __( 'Changes the Production Description tab title', 'woocommerce-customizer' ),
						'type'     => 'text'
					),

					array(
						'id'       => 'woocommerce_product_additional_information_tab_title',
						'title'    => __( 'Additional Information', 'woocommerce-customizer' ),
						'desc_tip' => __( 'Changes the Additional Information tab title', 'woocommerce-customizer' ),
						'type'     => 'text'
					),

					array( 'type' => 'sectionend' ),

					array(
						'title' => __( 'Tab Content Headings', 'woocommerce-customizer' ),
						'type'  => 'title'
					),

					array(
						'id'       => 'woocommerce_product_description_heading',
						'title'    => __( 'Product Description', 'woocommerce-customizer' ),
						'desc_tip' => __( 'Changes the Product Description tab heading', 'woocommerce-customizer' ),
						'type'     => 'text'
					),

					array(
						'id'       => 'woocommerce_product_additional_information_heading',
						'title'    => __( 'Additional Information', 'woocommerce-customizer' ),
						'desc_tip' => __( 'Changes the Additional Information tab heading', 'woocommerce-customizer' ),
						'type'     => 'text'
					),

					array( 'type' => 'sectionend' ),

					array(
						'title' => __( 'Add to Cart Button Text', 'woocommerce-customizer' ),
						'type'  => 'title'
					),

					array(
						'id'       => 'single_add_to_cart_text',
						'title'    => __( 'All Product Types', 'woocommerce-customizer' ),
						'desc_tip' => __( 'Changes the Add to Cart button text on the single product page for all product type', 'woocommerce-customizer' ),
						'type'     => 'text'
					),

					array( 'type' => 'sectionend' ),

					array(
						'title' => __( 'Sale Flash', 'woocommerce-customizer' ),
						'type'  => 'title'
					),

					array(
						'id'       => 'single_sale_flash_text',
						'title'    => __( 'Sale badge text', 'woocommerce-customizer' ),
						'desc_tip' => __( 'Changes text for the sale flash on product pages. Default: "Sale!"', 'woocommerce-customizer' ),
						'type'     => 'text',
						/* translators: Placeholders: %1$s - <code>, %2$s - </code> */
						'desc'     => sprintf( __( 'Use %1$s{percent}%2$s to insert percent off, e.g., "{percent} off!"', 'woocommerce-customizer' ), '<code>', '</code>' ) . '<br />' . __( 'Shows "up to n%" for grouped or variable products if multiple percentages are possible.', 'woocommerce-customizer' ),
					),

					array( 'type' => 'sectionend' ),
				),

			'checkout' =>

				array(

					array(
						'title' => __( 'Messages', 'woocommerce-customizer' ),
						'type'  => 'title'
					),

					array(
						'id'       => 'woocommerce_checkout_must_be_logged_in_message',
						'title'    => __( 'Must be logged in text', 'woocommerce-customizer' ),
						'desc_tip' => __( 'Changes the message displayed when a customer must be logged in to checkout', 'woocommerce-customizer' ),
						'type'     => 'text'
					),

					array(
						'id'       => 'woocommerce_checkout_coupon_message',
						'title'    => __( 'Coupon text', 'woocommerce-customizer' ),
						'desc_tip' => __( 'Changes the message displayed if the coupon form is enabled on checkout', 'woocommerce-customizer' ),
						'type'     => 'text',
						'desc'     => sprintf( '<code>%s ' . esc_attr( '<a href="#" class="showcoupon">%s</a>' ) . '</code>', 'Have a coupon?', 'Click here to enter your code' ),
					),

					array(
						'id'       => 'woocommerce_checkout_login_message',
						'title'    => __( 'Login text', 'woocommerce-customizer' ),
						'desc_tip' => __( 'Changes the message displayed if customers can login at checkout', 'woocommerce-customizer' ),
						'type'     => 'text'
					),

					array( 'type' => 'sectionend' ),

					array(
						'title' => __( 'Misc', 'woocommerce-customizer' ),
						'type'  => 'title'
					),

					array(
						'id'       => 'woocommerce_create_account_default_checked',
						'title'    => __( 'Create Account checkbox default' ),
						'desc_tip' => __( 'Control the default state for the Create Account checkbox', 'woocommerce-customizer' ),
						'type'     => 'select',
						'options'  => array(
							'customizer_true'  => __( 'Checked', 'woocommerce-customizer' ),
							'customizer_false' => __( 'Unchecked', 'woocommerce-customizer' ),
						),
						'default'  => 'customizer_false',
					),

					array(
						'id'       => 'woocommerce_order_button_text',
						'title'    => __( 'Submit Order button', 'woocommerce-customizer' ),
						'desc_tip' => __( 'Changes the Place Order button text on checkout', 'woocommerce-customizer' ),
						'type'     => 'text'
					),

					array( 'type' => 'sectionend' )

				),

			'misc' =>

				array(

					array(
						'title' => __( 'Tax', 'woocommerce-customizer' ),
						'type'  => 'title'
					),

					array(
						'id'       => 'woocommerce_countries_tax_or_vat',
						'title'    => __( 'Tax Label', 'woocommerce-customizer' ),
						'desc_tip' => __( 'Changes the Taxes label. Defaults to Tax for USA, VAT for European countries', 'woocommerce-customizer' ),
						'type'     => 'text'
					),

					array(
						'id'       => 'woocommerce_countries_inc_tax_or_vat',
						'title'    => __( 'Including Tax Label', 'woocommerce-customizer' ),
						'desc_tip' => __( 'Changes the Including Taxes label. Defaults to Inc. tax for USA, Inc. VAT for European countries', 'woocommerce-customizer' ),
						'type'     => 'text'
					),

					array(
						'id'       => 'woocommerce_countries_ex_tax_or_vat',
						'title'    => __( 'Excluding Tax Label', 'woocommerce-customizer' ),
						'desc_tip' => __( 'Changes the Excluding Taxes label. Defaults to Exc. tax for USA, Exc. VAT for European countries', 'woocommerce-customizer' ),
						'type'     => 'text'
					),

					array( 'type' => 'sectionend' ),

					array(
						'title' => __( 'Images', 'woocommerce-customizer' ),
						'type'  => 'title'
					),

					array(
						'id'       => 'woocommerce_placeholder_img_src',
						'title'    => __( 'Placeholder Image source', 'woocommerce-customizer' ),
						'desc_tip' => __( 'Change the default placeholder image by setting this to a valid image URL', 'woocommerce-customizer' ),
						'type'     => 'text'
					),

					array( 'type' => 'sectionend' ),

				),
		);

		$current_section = isset( $GLOBALS['current_section'] ) ? $GLOBALS['current_section'] : 'shop_loop';

		return isset( $settings[ $current_section ] ) ?  $settings[ $current_section ] : $settings['shop_loop'];
	}


}

// setup settings
return wc_customizer()->settings = new WC_Customizer_Settings();
