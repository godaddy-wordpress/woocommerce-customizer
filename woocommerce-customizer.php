<?php
/**
 * Plugin Name: WooCommerce Customizer
 * Plugin URI: http://www.maxrice.com/wordpress/woocommerce-customizer/
 * Description: Helps you customize WooCommerce without writing any code
 * Version: 1.0.0
 * Author: Max Rice
 * Author URI: http://www.maxrice.com
 *
 *
 * Copyright: © 2012 Max Rice (max@maxrice.com)
 *
 * License: GNU General Public License v3.0
 * License URI: http://www.gnu.org/licenses/gpl-3.0.html
 *
 * @package    WooCommerce Customizer
 * @author     Max Rice
 * @since      1.0
 */

/**
 * Plugin Setup
 *
 * @since 1.0
 */

// Check if WooCommerce is active
if ( ! in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) )
	return;


/**
 * Main Class
 *
 * @since 1.0
 */
class WC_Customizer {

	/** @var string option db prefix */
	public static $option_name = 'wc_customizer_active_customizations';

	/** @var string text domain */
	public static $text_domain = 'wc_customizer';

	/** @var plugin path */
	public static $plugin_path;

	/** @var plugin url */
	public static $plugin_url;

	/** @var active filters */
	public static $filters;

	/**
	 * Init plugin
	 *
	 * @access public
	 * @since  1.0
	 * @return void
	 */
	public static function init() {

		self::$plugin_path = untrailingslashit( plugin_dir_path( __FILE__ ) );
		self::$plugin_url  = plugins_url( basename( plugin_dir_path( __FILE__ ) ), basename( __FILE__ ) );

		if ( is_admin() ) {

			self::admin_includes();

			// add a 'Start Customizing' link to the plugin action links
			// remember, __FILE__ derefs symlinks :(
			add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), __CLASS__ . '::plugin_manage_link' );

		}

		// load filter 'tags' and values
		self::$filters = maybe_unserialize( get_option( self::$option_name ) );

		// only add filters if some exist
		if ( self::$filters ) {

			foreach ( self::$filters as $filter_name => $filter_value ) {
				add_filter( $filter_name, __CLASS__ . '::customize' );
			}

			//for use some day, in a galaxy far, far away, when PHP 5.3+ has greater WP adoption
			//add_filter( $filter_name, function() use ( $filter_value ) { return $filter_value; } );
		}

	}


	/**
	 * Add hook to selected filters
	 *
	 * @access public
	 * @since  1.0
	 * @return string $filter_value value to use for selected hook
	 */
	public static function customize() {
		$current_filter = current_filter();

		foreach ( self::$filters as $filter_name => $filter_value ) {
			if ( $filter_name == $current_filter )
				return $filter_value;
		}

	}

	/**
	 * Return the plugin action links.  This will only be called if the plugin
	 * is active.
	 *
	 * @access public
	 * @since  1.0
	 * @param array $actions associative array of action names to anchor tags
	 * @return array associative array of plugin action links
	 */
	public static function plugin_manage_link( $actions ) {
		// add the link to the front of the actions list
		return ( array_merge( array( 'start_customizing' => sprintf( '<a href="%s">%s</a>', admin_url( 'admin.php?page=woocommerce_customizer' ), __( 'Start Customizing', self::$text_domain ) ) ),
			$actions )
		);
	}

	/**
	 * Include admin class
	 *
	 * @access private
	 * @since  1.0
	 * @return void
	 */
	private static function admin_includes() {

		require_once( 'admin/class-wc-customizer-admin.php' );
	}


} // end class

WC_Customizer::init();

//end file