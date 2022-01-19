<?php
/**
 * Plugin Name: VHC WooCommerce Bestsellers Products
 * Plugin URI: https://github.com/vijayhardaha/
 * Description: This is a short description of what the plugin does. It's displayed in the WordPress admin area.
 * Version: 1.0.0
 * Author: Vijay Hardaha
 * Author URI: https://twitter.com/vijayhardaha/
 * Text Domain: vhc-wc-bestsellers-products
 * Domain Path: /languages/
 * Requires at least: 5.4
 * Requires PHP: 5.6
 * License: GNU General Public License v2 or later
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 *
 * @package VHC_WC_Bestsellers_Products
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

if ( ! function_exists( 'get_plugin_data' ) ) {
	require_once ABSPATH . 'wp-admin/includes/plugin.php';
}

if ( ! defined( 'VHC_WC_BESTSELLERS_PRODUCTS_PLUGIN_FILE' ) ) {
	define( 'VHC_WC_BESTSELLERS_PRODUCTS_PLUGIN_FILE', __FILE__ );
}

// Include the main VHC_WC_Bestsellers_Products class.
if ( ! class_exists( 'VHC_WC_Bestsellers_Products', false ) ) {
	include_once dirname( __FILE__ ) . '/includes/class-vhc-wc-bestsellers-products.php';
}

/**
 * Returns the main instance of VHC_WC_Bestsellers_Products.
 *
 * @since  1.0.0
 * @return VHC_WC_Bestsellers_Products
 */
function vhc_wc_bestsellers_products() {
	return VHC_WC_Bestsellers_Products::instance();
}

// Global for backwards compatibility.
$GLOBALS['vhc_wc_bestsellers_products'] = vhc_wc_bestsellers_products();
