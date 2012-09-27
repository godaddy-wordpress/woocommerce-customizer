<?php
/**
 * WooCommerce Customizer Admin Class
 *
 * Loads / Saves admin settings page
 *
 *
 * @package        WooCommerce Customizer
 * @subpackage     WC_Customizer_Admin
 * @category       Class
 * @author         Max Rice
 * @since          1.0
 */

class WC_Customizer_Admin {

	/** @var array tab IDs / titles admin page */
	public static $tabs;

	/** @var string submenu page hook suffix  */
	public static $page;


	/**
	 * Init Admin class
	 *
	 * @access public
	 * @since  1.0;
	 * @return void
	 */
	public static function init() {

		self::$tabs = array( 'shop_loop'        => __( 'Shop Loop', WC_Customizer::$text_domain ),
												 'product_page'     => __( 'Product Page', WC_Customizer::$text_domain ),
												 'checkout'         => __( 'Checkout', WC_Customizer::$text_domain ),
												 'misc'             => __( 'Misc', WC_Customizer::$text_domain )
		);

		// Load necessary admin styles / scripts
		add_action( 'admin_enqueue_scripts', __CLASS__ . '::load_styles_scripts' );

		// Add 'Customizer' link under WooCommerce menu
		add_action( 'admin_menu', __CLASS__ . '::add_menu_link' );

	}

	/**
	 * Load admin styles and scripts, only on our page
	 *
	 * @access public
	 * @since  1.0
	 *
	 * @param $hook_suffix;
	 *
	 * @return void
	 */
	public static function load_styles_scripts( $hook_suffix ) {
		global $woocommerce;

		// only load on our settings page
		if ( $hook_suffix != self::$page )
			return;

		//WooCommerce styles
		wp_enqueue_style( 'woocommerce_admin_styles', $woocommerce->plugin_url() . '/assets/css/admin.css' );

		// WooCommerce Admin JS for tool tips
		wp_enqueue_script( 'woocommerce_admin', $woocommerce->plugin_url() . '/assets/js/admin/woocommerce_admin.min.js', array( 'jquery', 'jquery-ui-widget', 'jquery-ui-core' ), $woocommerce->version );

	}

	/**
	 * Add 'Customizer' menu link under 'WooCommerce' top level menu
	 *
	 * @access public
	 * @since  1.0
	 * @return void
	 */
	public static function add_menu_link() {

		self::$page = add_submenu_page( 'woocommerce',
			__( 'WooCommerce Customizer', WC_Customizer::$text_domain ),
			__( 'Customizer', WC_Customizer::$text_domain ),
			'manage_woocommerce',
			'woocommerce_customizer',
				__CLASS__ . '::display_settings_page'
		);

	}

	/**
	 * Show Customizer settings page content for all tabs.
	 *
	 * @access public
	 * @since  1.0
	 * @return void
	 */
	public static function display_settings_page() {

		if ( false === ( $current_tab = self::get_current_tab() ) )
			return;
		?>
  <div class="wrap woocommerce">
      <form method="post" id="mainform" action="" enctype="multipart/form-data">
          <div class="icon32 icon32-woocommerce-settings" id="icon-woocommerce"><br /></div>
          <h2 class="nav-tab-wrapper woo-nav-tab-wrapper">
						<?php

						foreach ( self::$tabs as $tab_id => $tab_title ) :

							$class = ( $tab_id == $current_tab ) ? 'nav-tab nav-tab-active' : 'nav-tab';
							$url   = add_query_arg( 'tab', $tab_id, admin_url( 'admin.php?page=woocommerce_customizer&tab=' ) );

							printf( '<a href="%s" class="%s">%s</a>', $url, $class, $tab_title );

						endforeach;

						?> </h2> <?php

				wp_nonce_field( 'wc-customizer-settings', '_wpnonce', true, true );

				//check post & verify nonce
				if ( ! empty( $_POST ) ) {

					if ( ! wp_verify_nonce( $_REQUEST['_wpnonce'], 'wc-customizer-settings' ) )
						wp_die( __( 'Action failed. Please refresh the page and retry.', WC_Customizer::$text_domain ) );

					self::save_fields( self::load_fields(), $current_tab );

					// Redirect to settings
					wp_redirect( add_query_arg( 'saved', 'true' ) );
					exit;

				}

				// show error / success message
				if ( ! empty( $_GET['saved'] ) )
					echo '<div id="message" class="updated fade"><p><strong>' . __( 'Your customizations have been saved.', WC_Customizer::$text_domain ) . '</strong></p></div>';


				self::output_fields( self::load_fields(), $current_tab );

				?>
          <p class="submit">
              <input name="save" class="button-primary" type="submit" value="<?php _e( 'Save Customizations', WC_Customizer::$text_domain ); ?>" />
          </p>
      </form>
  </div>
	<?php
	}

	/**
	 * Output fields to page
	 *
	 * @access public
	 * @since  1.0;
	 *
	 * @param array  $fields
	 * @param string $tab current tab slug
	 *
	 * @return void
	 */
	public static function output_fields( $fields, $tab ) {
		global $woocommerce;

		// only use fields for current tab
		$fields = $fields[$tab];

		$customizations = maybe_unserialize( get_option( WC_Customizer::$option_name ) );

		foreach ( $fields as $field ) :

			if ( ! isset( $field['type'] ) ) continue;
			if ( ! isset( $field['filter_id'] ) ) $field['filter_id'] = '';
			if ( ! isset( $field['name'] ) ) $field['name'] = '';
			if ( ! isset( $field['class'] ) ) $field['class'] = '';
			if ( ! isset( $field['css'] ) ) $field['css'] = '';
			if ( ! isset( $field['std'] ) ) $field['std'] = '';
			if ( ! isset( $field['desc'] ) ) $field['desc'] = '';
			if ( ! isset( $field['desc_tip'] ) ) $field['desc_tip'] = false;

			if ( $field['desc_tip'] === true ) {
				$description = '<img class="help_tip" data-tip="' . esc_attr( $field['desc'] ) . '" src="' . $woocommerce->plugin_url() . '/assets/images/help.png" />';
			} elseif ( $field['desc_tip'] ) {
				$description = '<img class="help_tip" data-tip="' . esc_attr( $field['desc_tip'] ) . '" src="' . $woocommerce->plugin_url() . '/assets/images/help.png" />';
			} else {
				$description = '<span class="description">' . $field['desc'] . '</span>';
			}

			switch ( $field['type'] ) :

				case 'title':
					if ( isset( $field['name'] ) && $field['name'] ) echo '<h3>' . $field['name'] . '</h3>';
					if ( isset( $field['desc'] ) && $field['desc'] ) echo wpautop( wptexturize( $field['desc'] ) );
					echo '<table class="form-table">' . "\n\n";
					break;

				case 'sectionend':
					echo '</table>';
					break;

				case 'text':
					?>
          <tr valign="top">
              <th scope="row" class="titledesc">
                  <label for="<?php echo esc_attr( $field['filter_id'] ); ?>"><?php echo $field['name']; ?></label>
              </th>
              <td class="forminp">
                  <input name="<?php echo esc_attr( $field['filter_id'] ); ?>" id="<?php echo esc_attr( $field['filter_id'] ); ?>" type="<?php echo esc_attr( $field['type'] ); ?>" style="<?php echo esc_attr( $field['css'] ); ?>" value="<?php if ( isset( $customizations[$field['filter_id']] ) && ! empty( $customizations[$field['filter_id']] ) ) {
										echo esc_attr( stripslashes( $customizations[$field['filter_id']] ) );
									} else {
										echo esc_attr( $field['std'] );
									} ?>" /> <?php echo $description; ?></td>
          </tr><?php
					break;

				default:
					break;

			endswitch;

		endforeach;
	}

	/**
	 * Save admin fields into serialized option
	 *
	 * @access public
	 * @since  1.0;
	 *
	 * @param array  $fields
	 * @param string $tab current tab slug
	 *
	 * @return bool
	 */
	public static function save_fields( $fields, $tab ) {

		// only use fields for current tab
		$fields = $fields[$tab];

		$customizations = maybe_unserialize( get_option( WC_Customizer::$option_name ) );

		foreach ( $fields as $field ) :

			if ( ! isset( $field['filter_id'] ) )
				continue;

			if ( isset( $field['filter_id'] ) && ! empty( $_POST[$field['filter_id']] ) ) {

				$customizations[$field['filter_id']] = woocommerce_clean( $_POST[$field['filter_id']] );

			} elseif ( isset( $field['filter_id'] ) ) {

				unset( $customizations[$field['filter_id']] );

			}

		endforeach;

		update_option( WC_Customizer::$option_name, $customizations );

		return true;
	}

	/**
	 * Return admin fields in proper format for outputting / saving
	 *
	 * @access private
	 * @since  1.0
	 * @return array
	 */
	private static function load_fields() {

		return array(

			'shop_loop'    =>

			array(

				array(
					'name'    => __( 'Add to Cart Button Text', WC_Customizer::$text_domain ),
					'type'    => 'title'
				),

				array(
					'filter_id'    => 'add_to_cart_text',
					'name'         => __( 'Simple Product', WC_Customizer::$text_domain ),
					'desc_tip'     => __( 'Changes the add to cart button text for simple products on all loop pages', WC_Customizer::$text_domain ),
					'type'         => 'text'
				),

				array(
					'filter_id'    => 'variable_add_to_cart_text',
					'name'         => __( 'Variable Product', WC_Customizer::$text_domain ),
					'desc_tip'     => __( 'Changes the add to cart button text for variable products on all loop pages', WC_Customizer::$text_domain ),
					'type'         => 'text'
				),

				array(
					'filter_id'    => 'grouped_add_to_cart_text',
					'name'         => __( 'Grouped Product', WC_Customizer::$text_domain ),
					'desc_tip'     => __( 'Changes the add to cart button text for grouped products on all loop pages', WC_Customizer::$text_domain ),
					'type'         => 'text'
				),

				array(
					'filter_id'    => 'external_add_to_cart_text',
					'name'         => __( 'External Product', WC_Customizer::$text_domain ),
					'desc_tip'     => __( 'Changes the add to cart button text for external products on all loop pages', WC_Customizer::$text_domain ),
					'type'         => 'text'
				),

				array(
					'filter_id'    => 'out_of_stock_add_to_cart_text',
					'name'         => __( 'Out of Stock Product', WC_Customizer::$text_domain ),
					'desc_tip'     => __( 'Changes the add to cart button text for out of stock products on all loop pages', WC_Customizer::$text_domain ),
					'type'         => 'text'
				),

				array( 'type' => 'sectionend' ),

				array(
					'name'    => __( 'Layout', WC_Customizer::$text_domain ),
					'type'    => 'title'
				),

				array(
					'filter_id'    => 'loop_shop_per_page',
					'name'         => __( 'Products displayed per page', WC_Customizer::$text_domain ),
					'desc_tip'     => __( 'Changes the number of products displayed per page', WC_Customizer::$text_domain ),
					'type'         => 'text'
				),

				array(
					'filter_id'    => 'loop_shop_columns',
					'name'         => __( 'Product columns displayed per page', WC_Customizer::$text_domain ),
					'desc_tip'     => __( 'Changes the number of columns displayed per page', WC_Customizer::$text_domain ),
					'type'         => 'text'
				),

				array(
					'filter_id'    => 'woocommerce_product_thumbnails_columns',
					'name'         => __( 'Product thumbnail columns displayed', WC_Customizer::$text_domain ),
					'desc_tip'     => __( 'Changes the number of product thumbnail columns displayed', WC_Customizer::$text_domain ),
					'type'         => 'text'
				),

				array( 'type' => 'sectionend' )

			),

			'product_page' =>

			array(
				// tab title customization coming in wc 1.7
				/*array(
														'name'		=> __( 'Tab Titles', WC_Customizer::$text_domain ),
														'type'		=> 'title'
													),

													array(
														'filter_id'		=> 'woocommerce_product_description_tab_title',
														'name'				=> __( 'Product Description', WC_Customizer::$text_domain ),
														'desc_tip'		=> __( 'Changes the Production Description tab title', WC_Customizer::$text_domain ),
														'type'				=> 'text'
													),

													array(
														'filter_id'		=> 'woocommerce_product_additional_information_tab_title',
														'name'				=> __( 'Additional Information', WC_Customizer::$text_domain ),
														'desc_tip'		=> __( 'Changes the Additional Information tab title', WC_Customizer::$text_domain ),
														'type'				=> 'text'
													),

													array(
														'filter_id'		=> 'woocommerce_reviews_tab_title',
														'name'				=> __( 'Reviews', WC_Customizer::$text_domain ),
														'desc_tip'		=> __( 'Changes the Reviews tab title', WC_Customizer::$text_domain ),
														'type'				=> 'text'
													),

													array( 'type' => 'sectionend' ),*/

				array(
					'name'    => __( 'Tab Headings', WC_Customizer::$text_domain ),
					'type'    => 'title'
				),

				array(
					'filter_id'    => 'woocommerce_product_description_heading',
					'name'         => __( 'Product Description', WC_Customizer::$text_domain ),
					'desc_tip'     => __( 'Changes the Product Description tab heading', WC_Customizer::$text_domain ),
					'type'         => 'text'
				),

				array(
					'filter_id'    => 'woocommerce_product_additional_information_heading',
					'name'         => __( 'Additional Information', WC_Customizer::$text_domain ),
					'desc_tip'     => __( 'Changes the Additional Information tab heading', WC_Customizer::$text_domain ),
					'type'         => 'text'
				),

				array( 'type' => 'sectionend' ),

				array(
					'name'    => __( 'Add to Cart Button Text', WC_Customizer::$text_domain ),
					'type'    => 'title'
				),

				array(
					'filter_id'    => 'single_add_to_cart_text',
					'name'         => __( 'All Product Types', WC_Customizer::$text_domain ),
					'desc_tip'     => __( 'Changes the Add to Cart button text on the single product page for all product type', WC_Customizer::$text_domain ),
					'type'         => 'text'
				),

				array( 'type' => 'sectionend' )
			),

			'checkout'     =>

			array(

				array(
					'name'    => __( 'Messages', WC_Customizer::$text_domain ),
					'type'    => 'title'
				),

				array(
					'filter_id'    => 'woocommerce_checkout_must_be_logged_in_message',
					'name'         => __( 'Must be logged in text', WC_Customizer::$text_domain ),
					'desc_tip'     => __( 'Changes the message displayed when a customer must be logged in to checkout', WC_Customizer::$text_domain ),
					'type'         => 'text'
				),

				array(
					'filter_id'    => 'woocommerce_checkout_coupon_message',
					'name'         => __( 'Coupon text', WC_Customizer::$text_domain ),
					'desc_tip'     => __( 'Changes the message displayed if the coupon form is enabled on checkout', WC_Customizer::$text_domain ),
					'type'         => 'text'
				),

				array(
					'filter_id'    => 'woocommerce_checkout_login_message',
					'name'         => __( 'Login text', WC_Customizer::$text_domain ),
					'desc_tip'     => __( 'Changes the message displayed if customers can login at checkout', WC_Customizer::$text_domain ),
					'type'         => 'text'
				),

				array( 'type' => 'sectionend' ),

				array(
					'name'    => __( 'Button Text', WC_Customizer::$text_domain ),
					'type'    => 'title'
				),

				array(
					'filter_id'    => 'woocommerce_order_button_text',
					'name'         => __( 'Submit Order button', WC_Customizer::$text_domain ),
					'desc_tip'     => __( 'Changes the Place Order button text on checkout', WC_Customizer::$text_domain ),
					'type'         => 'text'
				),

				array( 'type' => 'sectionend' )

			),

			'misc'         =>

			array(

				array(
					'name'    => __( 'Tax', WC_Customizer::$text_domain ),
					'type'    => 'title'
				),

				array(
					'filter_id'    => 'woocommerce_countries_tax_or_vat',
					'name'         => __( 'Tax Label', WC_Customizer::$text_domain ),
					'desc_tip'     => __( 'Changes the Taxes label. Defaults to Tax for USA, VAT for European countries', WC_Customizer::$text_domain ),
					'type'         => 'text'
				),

				array(
					'filter_id'    => 'woocommerce_countries_inc_tax_or_vat',
					'name'         => __( 'Including Tax Label', WC_Customizer::$text_domain ),
					'desc_tip'     => __( 'Changes the Including Taxes label. Defaults to Inc. tax for USA, Inc. VAT for European countries', WC_Customizer::$text_domain ),
					'type'         => 'text'
				),

				array(
					'filter_id'    => 'woocommerce_countries_ex_tax_or_vat',
					'name'         => __( 'Excluding Tax Label', WC_Customizer::$text_domain ),
					'desc_tip'     => __( 'Changes the Excluding Taxes label. Defaults to Exc. tax for USA, Exc. VAT for European countries', WC_Customizer::$text_domain ),
					'type'         => 'text'
				),

				array( 'type' => 'sectionend' )

			)

		);
	}

	/**
	 * Get current tab slug
	 *
	 * @access private
	 * @since  1.0;
	 * @return string slug of current tab
	 */
	private static function get_current_tab() {

		if ( empty( $_GET['tab'] ) ) {

			reset( self::$tabs );
			return key( self::$tabs );

		} elseif ( array_key_exists( $_GET['tab'], self::$tabs ) ) {

			return urldecode( $_GET['tab'] );

		} else {

			return false;
		}
	}

} // end class

WC_Customizer_Admin::init();

// end file