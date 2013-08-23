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
 * @copyright   Copyright (c) 2013, SkyVerge, Inc.
 * @license     http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License v3.0
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * Admin class
 *
 * Adds UX for adding/modifying customizations
 *
 * @since 1.0
 */
class WC_Customizer_Admin {


	/** @var array tab IDs / titles admin page */
	public $tabs;

	/** @var string sub-menu page hook suffix  */
	public $page;


	/**
	 * Setup admin class
	 *
	 * @since 1.0
	 * @return \WC_Customizer_Admin
	 */
	public function __construct() {

		$this->tabs = array(
			'shop_loop'    => __( 'Shop Loop', 'wc-customizer' ),
			'product_page' => __( 'Product Page', 'wc-customizer' ),
			'checkout'     => __( 'Checkout', 'wc-customizer' ),
			'misc'         => __( 'Misc', 'wc-customizer' )
		);

		// Add settings page screen ID to list of pages for WC CSS/JS to load on
		add_filter( 'woocommerce_screen_ids', array( $this, 'load_woocommerce_styles_scripts' ) );

		// Add 'Customizer' link under WooCommerce menu
		add_action( 'admin_menu', array( $this, 'add_menu_link' ) );
	}


	/**
	 * Add Customizer settings screen ID to the list of pages for WC to load CSS/JS on
	 *
	 * @since 1.0
	 * @param array $screen_ids
	 * @return array
	 */
	public function load_woocommerce_styles_scripts( $screen_ids ) {

		$screen_ids[] = 'woocommerce_page_wc_customizer';

		return $screen_ids;
	}


	/**
	 * Add 'Customizer' menu link under 'WooCommerce' top level menu
	 *
	 * @since 1.0
	 */
	public function add_menu_link() {

		$this->page = add_submenu_page(
			'woocommerce',
			__( 'WooCommerce Customizer', 'wc-customizer' ),
			__( 'Customizer', 'wc-customizer' ),
			'manage_woocommerce',
			'wc_customizer',
			array( $this, 'render_settings_page' )
		);
	}


	/**
	 * Show Customizer settings page content for all tabs.
	 *
	 * @since 1.1
	 */
	public function render_settings_page() {

		$current_tab = ( empty( $_GET['tab'] ) ) ? 'shop_loop' : urldecode( $_GET['tab'] );

		?>
		<div class="wrap woocommerce">
			<form method="post" id="mainform" action="" enctype="multipart/form-data">
			<div id="icon-woocommerce" class="icon32-woocommerce-settings icon32"><br /></div>
			<h2 class="nav-tab-wrapper woo-nav-tab-wrapper">

				<?php

				// display tabs
				foreach ( $this->tabs as $tab_id => $tab_title ) {

					$class = ( $tab_id === $current_tab ) ? 'nav-tab nav-tab-active' : 'nav-tab';
					$url   = add_query_arg( 'tab', $tab_id, admin_url( 'admin.php?page=wc_customizer' ) );

					printf( '<a href="%s" class="%s">%s</a>', $url, $class, $tab_title );
				}

				?> </h2> <?php

				// save settings
				if ( ! empty( $_POST ) ) {

					if ( ! wp_verify_nonce( $_REQUEST['_wpnonce'], 'wc-customizer-settings' ) )
						wp_die( __( 'Action failed. Please refresh the page and retry.', 'wc-customizer' ) );


					$this->save_settings( $this->get_settings( $current_tab ) );

					wp_redirect( add_query_arg( array( 'saved' => 'true' ) ) );

					exit;
				}

				// display success message
				if ( ! empty( $_GET['saved'] ) )
					echo '<div id="message" class="updated fade"><p><strong>' . __( 'Your customizations have been saved.', 'wc-customizer' ) . '</strong></p></div>';

				// display filters
				$this->render_settings( $this->get_settings( $current_tab ) );

				submit_button( __( 'Save Customizations', 'wc-customizer' ) );

				wp_nonce_field( 'wc-customizer-settings', '_wpnonce', true, true );

		?></form></div> <?php
	}


	/**
	 * Show customization fields for a given tab
	 *
	 * @see adapted from woocommerce_admin_fields()
	 *
	 * @since 1.1
	 * @param array $fields the customization fields to show
	 */
	private function render_settings( $fields ) {

		$customizations = get_option( 'wc_customizer_active_customizations' );

		foreach ( $fields as $field ) {

			switch ( $field['type'] ) {

				case 'title':
					echo '<h3>' . esc_html( $field['title'] ) . '</h3>';
					echo '<table class="form-table">';
					break;

				case 'sectionend':
					echo '</table>';
					break;

				case 'text':;
					$value = ( isset( $customizations[ $field['id'] ] ) ) ? $customizations[ $field['id'] ] : '';
					?>
						<tr valign="top">
						<th scope="row" class="titledesc">
							<label for="<?php echo esc_attr( $field['id'] ); ?>"><?php echo esc_html( $field['title'] ); ?></label>
							<img class="help_tip" data-tip="<?php echo esc_attr( $field['desc_tip'] ); ?>" src="<?php echo esc_url( $GLOBALS['woocommerce']->plugin_url() . '/assets/images/help.png' ); ?>" height="16" width="16" />
						</th>
						<td class="forminp forminp-text">
							<input name="<?php echo esc_attr( $field['id'] ); ?>" id="<?php echo esc_attr( $field['id'] ); ?>" type="text" value="<?php echo esc_attr( $value ); ?>" />
						</td>
						</tr>
					<?php
					break;
			}
		}
	}


	/**
	 * Save customizations for a given tab
	 *
	 * @since 1.1
	 */
	public function save_settings( $fields ) {

		$customizations = get_option( 'wc_customizer_active_customizations' );

		foreach ( $fields as $field ) {

			if ( ! isset( $field['id'] ) )
				continue;

			if ( ! empty( $_POST[ $field['id'] ] ) )
				$customizations[ $field['id'] ] = wp_kses_post( $_POST[ $field['id'] ] );

			elseif ( isset( $customizations[ $field['id'] ] ) )
				unset( $customizations[ $field['id'] ] );
		}

		update_option( 'wc_customizer_active_customizations', $customizations );
	}


	/**
	 * Return admin fields in proper format for outputting / saving
	 *
	 * @since 1.1
	 * @param string $tab_id the tab to get settings for
	 * @return array
	 */
	private function get_settings( $tab_id ) {

		$settings = array(

			'shop_loop'    =>

				array(

					array(
						'title' => __( 'Add to Cart Button Text', 'wc-customizer' ),
						'type'  => 'title'
					),

					array(
						'id'       => 'add_to_cart_text',
						'title'    => __( 'Simple Product', 'wc-customizer' ),
						'desc_tip' => __( 'Changes the add to cart button text for simple products on all loop pages', 'wc-customizer' ),
						'type'     => 'text'
					),

					array(
						'id'       => 'variable_add_to_cart_text',
						'title'    => __( 'Variable Product', 'wc-customizer' ),
						'desc_tip' => __( 'Changes the add to cart button text for variable products on all loop pages', 'wc-customizer' ),
						'type'     => 'text'
					),

					array(
						'id'       => 'grouped_add_to_cart_text',
						'title'    => __( 'Grouped Product', 'wc-customizer' ),
						'desc_tip' => __( 'Changes the add to cart button text for grouped products on all loop pages', 'wc-customizer' ),
						'type'     => 'text'
					),

					array(
						'id'       => 'external_add_to_cart_text',
						'title'    => __( 'External Product', 'wc-customizer' ),
						'desc_tip' => __( 'Changes the add to cart button text for external products on all loop pages', 'wc-customizer' ),
						'type'     => 'text'
					),

					array(
						'id'       => 'out_of_stock_add_to_cart_text',
						'title'    => __( 'Out of Stock Product', 'wc-customizer' ),
						'desc_tip' => __( 'Changes the add to cart button text for out of stock products on all loop pages', 'wc-customizer' ),
						'type'     => 'text'
					),

					array( 'type' => 'sectionend' ),

					array(
						'title' => __( 'Layout', 'wc-customizer' ),
						'type'  => 'title'
					),

					array(
						'id'       => 'loop_shop_per_page',
						'title'    => __( 'Products displayed per page', 'wc-customizer' ),
						'desc_tip' => __( 'Changes the number of products displayed per page', 'wc-customizer' ),
						'type'     => 'text'
					),

					array(
						'id'       => 'loop_shop_columns',
						'title'    => __( 'Product columns displayed per page', 'wc-customizer' ),
						'desc_tip' => __( 'Changes the number of columns displayed per page', 'wc-customizer' ),
						'type'     => 'text'
					),

					array(
						'id'       => 'woocommerce_product_thumbnails_columns',
						'title'    => __( 'Product thumbnail columns displayed', 'wc-customizer' ),
						'desc_tip' => __( 'Changes the number of product thumbnail columns displayed', 'wc-customizer' ),
						'type'     => 'text'
					),

					array( 'type' => 'sectionend' )

				),

			'product_page' =>

				array(

					array(
						'title' => __( 'Tab Titles', 'wc-customizer' ),
						'type'  => 'title'
					),

					array(
						'id'       => 'woocommerce_product_description_tab_title',
						'title'    => __( 'Product Description', 'wc-customizer' ),
						'desc_tip' => __( 'Changes the Production Description tab title', 'wc-customizer' ),
						'type'     => 'text'
					),

					array(
						'id'       => 'woocommerce_product_additional_information_tab_title',
						'title'    => __( 'Additional Information', 'wc-customizer' ),
						'desc_tip' => __( 'Changes the Additional Information tab title', 'wc-customizer' ),
						'type'     => 'text'
					),

					array( 'type' => 'sectionend' ),

					array(
						'title' => __( 'Tab Content Headings', 'wc-customizer' ),
						'type'  => 'title'
					),

					array(
						'id'       => 'woocommerce_product_description_heading',
						'title'    => __( 'Product Description', 'wc-customizer' ),
						'desc_tip' => __( 'Changes the Product Description tab heading', 'wc-customizer' ),
						'type'     => 'text'
					),

					array(
						'id'       => 'woocommerce_product_additional_information_heading',
						'title'    => __( 'Additional Information', 'wc-customizer' ),
						'desc_tip' => __( 'Changes the Additional Information tab heading', 'wc-customizer' ),
						'type'     => 'text'
					),

					array( 'type' => 'sectionend' ),

					array(
						'title' => __( 'Add to Cart Button Text', 'wc-customizer' ),
						'type'  => 'title'
					),

					array(
						'id'       => 'single_add_to_cart_text',
						'title'    => __( 'All Product Types', 'wc-customizer' ),
						'desc_tip' => __( 'Changes the Add to Cart button text on the single product page for all product type', 'wc-customizer' ),
						'type'     => 'text'
					),

					array( 'type' => 'sectionend' )
				),

			'checkout'     =>

				array(

					array(
						'title' => __( 'Messages', 'wc-customizer' ),
						'type'  => 'title'
					),

					array(
						'id'       => 'woocommerce_checkout_must_be_logged_in_message',
						'title'    => __( 'Must be logged in text', 'wc-customizer' ),
						'desc_tip' => __( 'Changes the message displayed when a customer must be logged in to checkout', 'wc-customizer' ),
						'type'     => 'text'
					),

					array(
						'id'       => 'woocommerce_checkout_coupon_message',
						'title'    => __( 'Coupon text', 'wc-customizer' ),
						'desc_tip' => __( 'Changes the message displayed if the coupon form is enabled on checkout', 'wc-customizer' ),
						'type'     => 'text'
					),

					array(
						'id'       => 'woocommerce_checkout_login_message',
						'title'    => __( 'Login text', 'wc-customizer' ),
						'desc_tip' => __( 'Changes the message displayed if customers can login at checkout', 'wc-customizer' ),
						'type'     => 'text'
					),

					array( 'type' => 'sectionend' ),

					array(
						'title' => __( 'Button Text', 'wc-customizer' ),
						'type'  => 'title'
					),

					array(
						'id'       => 'woocommerce_order_button_text',
						'title'    => __( 'Submit Order button', 'wc-customizer' ),
						'desc_tip' => __( 'Changes the Place Order button text on checkout', 'wc-customizer' ),
						'type'     => 'text'
					),

					array( 'type' => 'sectionend' )

				),

			'misc'         =>

				array(

					array(
						'title' => __( 'Tax', 'wc-customizer' ),
						'type'  => 'title'
					),

					array(
						'id'       => 'woocommerce_countries_tax_or_vat',
						'title'    => __( 'Tax Label', 'wc-customizer' ),
						'desc_tip' => __( 'Changes the Taxes label. Defaults to Tax for USA, VAT for European countries', 'wc-customizer' ),
						'type'     => 'text'
					),

					array(
						'id'       => 'woocommerce_countries_inc_tax_or_vat',
						'title'    => __( 'Including Tax Label', 'wc-customizer' ),
						'desc_tip' => __( 'Changes the Including Taxes label. Defaults to Inc. tax for USA, Inc. VAT for European countries', 'wc-customizer' ),
						'type'     => 'text'
					),

					array(
						'id'       => 'woocommerce_countries_ex_tax_or_vat',
						'title'    => __( 'Excluding Tax Label', 'wc-customizer' ),
						'desc_tip' => __( 'Changes the Excluding Taxes label. Defaults to Exc. tax for USA, Exc. VAT for European countries', 'wc-customizer' ),
						'type'     => 'text'
					),

					array( 'type' => 'sectionend' )

				),

		);

		return ( isset( $settings[ $tab_id ] ) ) ? $settings[ $tab_id ] : array();
	}


} // end \WC_Customizer_Admin class
