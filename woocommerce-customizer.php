<?php
/**
 * Plugin Name: WooCommerce Customizer
 * Plugin URI: http://www.skyverge.com/product/woocommerce-customizer/
 * Description: Customize WooCommerce without code! Easily change add to cart button text and more.
 * Author: SkyVerge
 * Author URI: http://www.skyverge.com
 * Version: 2.5.0
 * Text Domain: woocommerce-customizer
 * Domain Path: /i18n/languages/
 *
 * Copyright: (c) 2013-2017, SkyVerge, Inc. (info@skyverge.com)
 *
 * License: GNU General Public License v3.0
 * License URI: http://www.gnu.org/licenses/gpl-3.0.html
 *
 * @package   WC-Customizer
 * @author    SkyVerge
 * @category  Utility
 * @copyright Copyright (c) 2013-2017, SkyVerge, Inc.
 * @license   http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License v3.0
 */

defined( 'ABSPATH' ) or exit;

// Check if WooCommerce is active
if ( ! WC_Customizer::is_woocommerce_active() ) {
	add_action( 'admin_notices', 'wc_customizer_render_wc_inactive_notice' );
	return;
}

// WC version check
if ( version_compare( get_option( 'woocommerce_db_version' ), '2.5.5', '<' ) ) {
	add_action( 'admin_notices', 'wc_customizer_render_outdated_wc_version_notice' );
	return;
}


/**
 * Renders a notice when WooCommerce version is outdated
 *
 * @since 2.3.1
 */
function wc_customizer_render_outdated_wc_version_notice() {

	$message = sprintf(
		/* translators: %1$s and %2$s are <strong> tags. %3$s and %4$s are <a> tags */
		__( '%1$sWooCommerce Customizer is inactive.%2$s This version requires WooCommerce 2.5.5 or newer. Please %3$supdate WooCommerce to version 2.5.5 or newer%4$s', 'woocommerce-customizer' ),
		'<strong>',
		'</strong>',
		'<a href="' . admin_url( 'plugins.php' ) . '">',
		'&nbsp;&raquo;</a>'
	);

	printf( '<div class="error"><p>%s</p></div>', $message );
}


/**
 * Renders a notice when WooCommerce version is outdated
 *
 * @since 2.3.1
 */
function wc_customizer_render_wc_inactive_notice() {

	$message = sprintf(
		/* translators: %1$s and %2$s are <strong> tags. %3$s and %4$s are <a> tags */
		__( '%1$sWooCommerce Customizer is inactive%2$s as it requires WooCommerce. Please %3$sactivate WooCommerce version 2.5.5 or newer%4$s', 'woocommerce-customizer' ),
		'<strong>',
		'</strong>',
		'<a href="' . admin_url( 'plugins.php' ) . '">',
		'&nbsp;&raquo;</a>'
	);

	printf( '<div class="error"><p>%s</p></div>', $message );
}


/**
 * # WooCommerce Customizer Main Plugin Class
 *
 * ## Plugin Overview
 *
 * Adds a few settings pages which make uses of some of the simpler filters inside WooCommerce, so if you want to quickly
 * change button text or the number of products per page, you can use this instead of having to write code for the filter.
 * Note this isn't designed as a rapid development/prototyping tool -- for a production site you should use the actual filter
 * instead of relying on this plugin.
 *
 * ## Admin Considerations
 *
 * A 'Customizer' sub-menu page is added to the top-level WooCommerce page, which contains 4 tabs with the settings
 * for each section - Shop Loop, Product Page, Checkout, Misc
 *
 * ## Frontend Considerations
 *
 * The filters that the plugin exposes as settings as used exclusively on the frontend.
 *
 * ## Database
 *
 * ### Global Settings
 *
 * + `wc_customizer_active_customizations` - a serialized array of active customizations in the format
 * filter name => filter value
 *
 * ### Options table
 *
 * + `wc_customizer_version` - the current plugin version, set on install/upgrade
 *
 */
class WC_Customizer {


	/** plugin version number */
	const VERSION = '2.5.0';

	/** @var \WC_Customizer single instance of this plugin */
	protected static $instance;

	/** @var \WC_Customizer_Settings instance */
	public $settings;

	/** var array the active filters */
	public $filters;


	/**
	 * Initializes the plugin
	 *
	 * @since 1.0.0
	 */
	public function __construct() {

		// load translation
		add_action( 'init', array( $this, 'load_translation' ) );

		// admin
		if ( is_admin() && ! defined( 'DOING_AJAX' ) ) {

			// load settings page
			add_filter( 'woocommerce_get_settings_pages', array( $this, 'add_settings_page' ) );

			// add a 'Configure' link to the plugin action links
			add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), array( $this, 'add_plugin_action_links' ) );

			// run every time
			$this->install();
		}

		add_action( 'woocommerce_init', array( $this, 'load_customizations' ) );
	}


	/**
	 * Cloning instances is forbidden due to singleton pattern.
	 *
	 * @since 2.3.0
	 */
	public function __clone() {

		/* translators: Placeholders: %s - plugin name */
		_doing_it_wrong( __FUNCTION__, sprintf( esc_html__( 'You cannot clone instances of %s.', 'woocommerce-customizer' ), 'WooCommerce Customizer' ), '2.3.0' );
	}


	/**
	 * Unserializing instances is forbidden due to singleton pattern.
	 *
	 * @since 2.3.0
	 */
	public function __wakeup() {

		/* translators: Placeholders: %s - plugin name */
		_doing_it_wrong( __FUNCTION__, sprintf( esc_html__( 'You cannot unserialize instances of %s.', 'woocommerce-customizer' ), 'WooCommerce Customizer' ), '2.3.0' );
	}


	/**
	 * Add settings page
	 *
	 * @since 2.0.0
	 * @param array $settings
	 * @return array
	 */
	public function add_settings_page( $settings ) {

		$settings[] = require_once( 'includes/class-wc-customizer-settings.php' );
		return $settings;
	}


	/**
	 * Load customizations after WC is loaded so the version can be checked
	 *
	 * @since 1.2.0
	 */
	public function load_customizations() {

		// load filter names and values
		$this->filters = get_option( 'wc_customizer_active_customizations' );

		// only add filters if some exist
		if ( ! empty( $this->filters ) ) {

			foreach ( $this->filters as $filter_name => $filter_value ) {

				// WC 2.1 changed the add to cart text filter signatures so conditionally add the new filters
				if ( false !== strpos( $filter_name, 'add_to_cart_text' ) ) {

					if ( $filter_name == 'single_add_to_cart_text' ) {

						add_filter( 'woocommerce_product_single_add_to_cart_text', array( $this, 'customize_single_add_to_cart_text' ) );

					} else {

						add_filter( 'woocommerce_product_add_to_cart_text', array( $this, 'customize_add_to_cart_text' ), 10, 2 );
					}

				} elseif ( 'woocommerce_placeholder_img_src' === $filter_name ) {

					// only filter placeholder images on the frontend
					if ( ! is_admin() ) {
						add_filter( $filter_name, array( $this, 'customize' ) );
					}

				} elseif ( 'loop_sale_flash_text' === $filter_name || 'single_sale_flash_text' === $filter_name ) {

					add_filter( 'woocommerce_sale_flash', array( $this, 'customize_woocommerce_sale_flash' ), 50, 3 );

				} else {

					add_filter( $filter_name, array( $this, 'customize' ) );
				}
			}
		}
	}


	/**
	 * Handle localization, WPML compatible
	 *
	 * @since 1.1.0
	 */
	public function load_translation() {

		// localization in the init action for WPML support
		load_plugin_textdomain( 'woocommerce-customizer', false, dirname( plugin_basename( __FILE__ ) ) . '/i18n/languages' );
	}


	/**
	 * Checks if WooCommerce is active
	 *
	 * @since 2.3.0
	 * @return bool true if WooCommerce is active, false otherwise
	 */
	public static function is_woocommerce_active() {

		$active_plugins = (array) get_option( 'active_plugins', array() );

		if ( is_multisite() ) {
			$active_plugins = array_merge( $active_plugins, get_site_option( 'active_sitewide_plugins', array() ) );
		}

		return in_array( 'woocommerce/woocommerce.php', $active_plugins ) || array_key_exists( 'woocommerce/woocommerce.php', $active_plugins );
	}


	/** Frontend methods ******************************************************/


	/**
	 * Add hook to selected filters
	 *
	 * @since 1.0.0
	 * @return string $filter_value value to use for selected hook
	 */
	public function customize() {

		$current_filter = current_filter();

		if ( isset( $this->filters[ $current_filter ] ) ) {

			if ( 'customizer_true' === $this->filters[ $current_filter] || 'customizer_true' === $this->filters[ $current_filter] ) {

				// helper to return a pure boolean value
				return 'customizer_true' === $this->filters[ $current_filter ];

			} else {

				return $this->filters[ $current_filter ];
			}
		}

		// no need to return a value passed in, because if a filter is set, it's designed to only return that value
	}


	/**
	 * Apply the single add to cart button text customization
	 *
	 * @since 1.2.0
	 */
	public function customize_single_add_to_cart_text() {

		return $this->filters['single_add_to_cart_text'];
	}


	/**
	 * Apply the shop loop add to cart button text customization
	 *
	 * @since 1.2.0
	 * @param string $text add to cart text
	 * @param WC_Product $product product object
	 * @return string modified add to cart text
	 */
	public function customize_add_to_cart_text( $text, $product ) {

		// out of stock add to cart text
		if ( isset( $this->filters['out_of_stock_add_to_cart_text'] ) && ! $product->is_in_stock() ) {

			return $this->filters['out_of_stock_add_to_cart_text'];
		}

		if ( isset( $this->filters['add_to_cart_text'] ) && $product->is_type( 'simple' ) ) {

			// simple add to cart text
			return $this->filters['add_to_cart_text'];

		} elseif ( isset( $this->filters['variable_add_to_cart_text'] ) && $product->is_type( 'variable') )  {

			// variable add to cart text
			return $this->filters['variable_add_to_cart_text'];

		} elseif ( isset( $this->filters['grouped_add_to_cart_text'] ) && $product->is_type( 'grouped' ) ) {

			// grouped add to cart text
			return $this->filters['grouped_add_to_cart_text'];

		} elseif( isset( $this->filters['external_add_to_cart_text'] ) && $product->is_type( 'external' ) ) {

			// external add to cart text
			return $this->filters['external_add_to_cart_text'];
		}

		return $text;
	}


	/**
	 * Apply the shop loop sale flash text customization.
	 *
	 * @since 2.5.0
	 *
	 * @param string $html add to cart flash HTML
	 * @param \WP_Post $_ post object, unused
	 * @param \WC_Product $product the prdouct object
	 * @return string updated HTML
	 */
	public function customize_woocommerce_sale_flash( $html, $_, $product ) {

		if ( is_product() && isset( $this->filters['single_sale_flash_text'] ) ) {

			$text = $this->filters['single_sale_flash_text'];

			// only get sales percentages when we should be replacing text
			// check "false" specifically since the position could be 0
			if ( false !== strpos( $text, '{percent}' ) ) {

				$percent = $this->get_sale_percentage( $product );
				$text    = str_replace( '{percent}', "{$percent}%", $text );
			}

			$html = "<span class='onsale'>{$text}</span>";

		} elseif ( ! is_product() && isset( $this->filters['loop_sale_flash_text'] ) ) {

			$text = $this->filters['loop_sale_flash_text'];

			// only check for sales percentages when we should be replacing text
			// check "false" specifically since the position could be 0
			if ( false !== strpos( $text, '{percent}' ) ) {

				$percent = $this->get_sale_percentage( $product );
				$text    = str_replace( '{percent}', "{$percent}%", $text );
			}

			$html = "<span class='onsale'>{$text}</span>";
		}

		return $html;
	}


	/** Admin methods ******************************************************/


	/**
	 * Return the plugin action links.  This will only be called if the plugin
	 * is active.
	 *
	 * @since 1.0.0
	 * @param array $actions associative array of action names to anchor tags
	 * @return array associative array of plugin action links
	 */
	public function add_plugin_action_links( $actions ) {

		$custom_actions = array(
			'configure' => sprintf( '<a href="%s">%s</a>', admin_url( 'admin.php?page=wc-settings&tab=customizer&section=shop_loop' ), __( 'Configure', 'woocommerce-customizer' ) ),
			'faq'       => sprintf( '<a href="%s">%s</a>', 'http://wordpress.org/plugins/woocommerce-customizer/#faq', __( 'FAQ', 'woocommerce-customizer' ) ),
			'support'   => sprintf( '<a href="%s">%s</a>', 'http://wordpress.org/support/plugin/woocommerce-customizer', __( 'Support', 'woocommerce-customizer' ) ),
		);

		// add the links to the front of the actions list
		return array_merge( $custom_actions, $actions );
	}


	/** Helper methods ******************************************************/


	/**
	 * Helper to get the percent discount for a product on sale.
	 *
	 * @since 2.5.0
	 *
	 * @param \WC_Product $product product instance
	 * @return string percentage discount
	 */
	private function get_sale_percentage( $product ) {

		$child_sale_percents = array();
		$percentage          = '0';

		if ( $product->is_type( 'grouped' ) || $product->is_type( 'variable' ) ) {

			foreach ( $product->get_children() as $child_id ) {

				$child = wc_get_product( $child_id );

				if ( $child->is_on_sale() ) {

					$regular_price         = $child->get_regular_price();
					$sale_price            = $child->get_sale_price();
					$child_sale_percents[] = $this->calculate_sale_percentage( $regular_price, $sale_price );
				}
			}

			// filter out duplicate values
			$child_sale_percents = array_unique( $child_sale_percents );

			// only add "up to" if there's > 1 percentage possible
			if ( ! empty ( $child_sale_percents ) ) {

				/* translators: Placeholder: %s - sale percentage */
				$percentage = count( $child_sale_percents ) > 1 ? sprintf( esc_html__( 'up to %s', 'woocommerce-customizer' ), max( $child_sale_percents ) ) : current( $child_sale_percents );
			}

		} else {

			$percentage = $this->calculate_sale_percentage( $product->get_regular_price(), $product->get_sale_price() );
		}

		return $percentage;
	}


	/**
	 * Calculates a sales percentage difference given regular and sale prices for a product.
	 *
	 * @since 2.5.0
	 *
	 * @param string $regular_price product regular price
	 * @param string $sale_price product sale price
	 * @return float percentage difference
	 */
	private function calculate_sale_percentage( $regular_price, $sale_price ) {

		$percent = 0;
		$regular = (float) $regular_price;
		$sale    = (float) $sale_price;

		// in case of free products so we don't divide by 0
		if ( $regular ) {
			$percent = round( ( ( $regular - $sale ) / $regular ) * 100 );
		}

		return $percent;
	}


	/**
	 * Main Customizer Instance, ensures only one instance is/can be loaded
	 *
	 * @since 2.3.0
	 * @see wc_customizer()
	 * @return \WC_Customizer
	 */
	public static function instance() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}
		return self::$instance;
	}


	/** Lifecycle methods ******************************************************/


	/**
	 * Run every time.  Used since the activation hook is not executed when updating a plugin
	 *
	 * @since 1.1.0
	 */
	private function install() {

		// get current version to check for upgrade
		$installed_version = get_option( 'wc_customizer_version' );

		// install
		if ( ! $installed_version ) {

			// install default settings
		}

		// upgrade if installed version lower than plugin version
		if ( -1 === version_compare( $installed_version, self::VERSION ) ) {
			$this->upgrade( $installed_version );
		}
	}


	/**
	 * Perform any version-related changes.
	 *
	 * @since 1.1.0
	 * @param int $installed_version the currently installed version of the plugin
	 */
	private function upgrade( $installed_version ) {

		// update the installed version option
		update_option( 'wc_customizer_version', self::VERSION );
	}


}


/**
 * Returns the One True Instance of Customizer
 *
 * @since 2.3.0
 * @return \WC_Customizer
 */
function wc_customizer() {
	return WC_Customizer::instance();
}


/**
 * The WC_Customizer global object
 * TODO: Remove with WC 3.1 compat {BR 2017-03-09}
 *
 * @deprecated 2.3.0
 *
 * @name $wc_customizer
 * @global WC_Customizer $GLOBALS['wc_customizer']
 */
$GLOBALS['wc_customizer'] = wc_customizer();
