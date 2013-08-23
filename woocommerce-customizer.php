<?php
/**
 * Plugin Name: WooCommerce Customizer
 * Plugin URI: http://www.skyverge.com/product/woocommerce-customizer/
 * Description: Customize WooCommerce without code! Easily change add to cart button text and more.
 * Author: SkyVerge
 * Author URI: http://www.skyverge.com
 * Version: 1.1
 * Text Domain: wc-customizer
 * Domain Path: /languages/
 *
 * Copyright: (c) 2013 SkyVerge, Inc. (info@skyverge.com)
 *
 * License: GNU General Public License v3.0
 * License URI: http://www.gnu.org/licenses/gpl-3.0.html
 *
 * @package   WC-Customizer
 * @author    SkyVerge
 * @category  Utility
 * @copyright Copyright (c) 2013, SkyVerge, Inc.
 * @license   http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License v3.0
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

// Check if WooCommerce is active
if ( ! in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) )
	return;


/**
 * The WC_Customizer global object
 * @name $wc_customizer
 * @global WC_Customizer $GLOBALS['wc_customizer']
 */
$GLOBALS['wc_customizer'] = new WC_Customizer();

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
	const VERSION = '1.1';

	/** var array the active filters */
	public $filters;


	/**
	 * Initializes the plugin
	 *
	 * @since 1.0
	 */
	public function __construct() {

		// load translation
		add_action( 'init', array( $this, 'load_translation' ) );

		// admin
		if ( is_admin() && ! defined( 'DOING_AJAX' ) ) {

			// include required files
			$this->admin_includes();

			// add a 'Configure' link to the plugin action links
			add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), array( $this, 'add_plugin_action_links' ) );

			// run every time
			$this->install();
		}

		// load filter names and values
		$this->filters = get_option( 'wc_customizer_active_customizations' );

		// only add filters if some exist
		if ( ! empty( $this->filters ) ) {

			foreach ( $this->filters as $filter_name => $filter_value ) {
				add_filter( $filter_name, array( $this, 'customize' ) );
		}

			// for use some day, in a galaxy far, far away, when PHP 5.3+ has greater WP adoption
			// add_filter( $filter_name, function() use ( $filter_value ) { return $filter_value; } );
		}
	}


	/**
	 * Include required admin files
	 *
	 * @since 1.1
	 */
	private function admin_includes() {

		// admin UI
		require( 'includes/class-wc-customizer-admin.php' );
		$this->admin = new WC_Customizer_Admin();
	}


	/**
	 * Handle localization, WPML compatible
	 *
	 * @since 1.1
	 */
	public function load_translation() {

		// localization in the init action for WPML support
		load_plugin_textdomain( 'wc-customizer', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
	}


	/** Frontend methods ******************************************************/


	/**
	 * Add hook to selected filters
	 *
	 * @since 1.0
	 * @return string $filter_value value to use for selected hook
	 */
	public function customize() {

		$current_filter = current_filter();

		if ( isset( $this->filters[ $current_filter ] ) )
			return $this->filters[ $current_filter ];

		// no need to return a value passed in, because if a filter is set, it's designed to only return that value
	}


	/** Admin methods ******************************************************/


	/**
	 * Return the plugin action links.  This will only be called if the plugin
	 * is active.
	 *
	 * @since 0.1
	 * @param array $actions associative array of action names to anchor tags
	 * @return array associative array of plugin action links
	 */
	public function add_plugin_action_links( $actions ) {

		$custom_actions = array(
			'configure' => sprintf( '<a href="%s">%s</a>', admin_url( 'admin.php?page=wc_customizer' ), __( 'Configure', 'wc-customizer' ) ),
			'faq'       => sprintf( '<a href="%s">%s</a>', 'http://wordpress.org/plugins/woocommerce-customizer/faq/', __( 'FAQ', 'wc-customizer' ) ),
			'support'   => sprintf( '<a href="%s">%s</a>', 'http://wordpress.org/support/plugin/woocommerce-customizer', __( 'Support', 'wc-customizer' ) ),
		);

		// add the links to the front of the actions list
		return array_merge( $custom_actions, $actions );
	}


	/** Lifecycle methods ******************************************************/


	/**
	 * Run every time.  Used since the activation hook is not executed when updating a plugin
	 *
	 * @since 1.1
	 */
	private function install() {

		// get current version to check for upgrade
		$installed_version = get_option( 'wc_customizer_version' );

		// install
		if ( ! $installed_version ) {

			// install default settings
		}

		// upgrade if installed version lower than plugin version
		if ( -1 === version_compare( $installed_version, self::VERSION ) )
			$this->upgrade( $installed_version );
	}


	/**
	 * Perform any version-related changes.
	 *
	 * @since 1.1
	 * @param int $installed_version the currently installed version of the plugin
	 */
	private function upgrade( $installed_version ) {

		// update the installed version option
		update_option( 'wc_customizer_version', self::VERSION );
	}


} // end \WC_Customizer
