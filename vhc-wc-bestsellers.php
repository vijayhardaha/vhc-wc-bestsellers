<?php
/**
 * Plugin Name: VHC WooCommerce Bestsellers
 * Plugin URI: https://github.com/vijayhardaha/vhc-wc-bestsellers/
 * Description: Displays your store's bestselling products from a specific period of time. These can be displayed through a Best Sellers page or a shortcode/widget.
 * Version: 1.0.0
 * Author: Vijay Hardaha
 * Author URI: https://twitter.com/vijayhardaha/
 * License: GNU General Public License v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: vhc-wc-bestsellers
 * Domain Path: /languages
 * Requires at least: 5.8
 * Requires PHP: 7.0
 * Tested up to: 6.0
 *
 * @package VHC_WC_Bestsellers
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

if ( ! function_exists( 'get_plugin_data' ) ) {
	require_once ABSPATH . 'wp-admin/includes/plugin.php';
}

if ( ! defined( 'VHC_WC_BESTSELLERS_PLUGIN_FILE' ) ) {
	define( 'VHC_WC_BESTSELLERS_PLUGIN_FILE', __FILE__ );
}

// Include the main VHC_WC_Bestsellers class.
if ( ! class_exists( 'VHC_WC_BESTSELLERS', false ) ) {
	include_once dirname( __FILE__ ) . '/includes/class-vhc-wc-bestsellers.php';
}

/**
 * Returns the main instance of VHC_WC_Bestsellers.
 *
 * @since 1.0.0
 * @return VHC_WC_Bestsellers
 */
function vhc_wc_bestsellers() {
	return VHC_WC_Bestsellers::instance();
}

// Global for backwards compatibility.
$GLOBALS['vhc_wc_bestsellers'] = vhc_wc_bestsellers();
