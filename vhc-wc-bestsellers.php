<?php
/**
 * Plugin Name: VHC WooCommerce Bestsellers
 * Plugin URI: https://github.com/vijayhardaha/vhc-wc-bestsellers
 * Description: Displays bestselling products products of your store from a specific time period. You can show them on a Best Sellers page, shortcode or through a widget. You can also customize many things with WordPress hooks.
 * Version: 1.0.1
 * Author: Vijay Hardaha
 * Author URI: https://twitter.com/vijayhardaha/
 * Text Domain: vhc-wc-bestsellers
 * Domain Path: /languages/
 * Requires at least: 5.4
 * Requires PHP: 5.6
 * License: GNU General Public License v2 or later
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 *
 * @package VHC_WC_BESTSELLERS
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

if ( ! function_exists( 'get_plugin_data' ) ) {
	require_once ABSPATH . 'wp-admin/includes/plugin.php';
}

if ( ! defined( 'VHC_WC_BESTSELLERS_PLUGIN_FILE' ) ) {
	define( 'VHC_WC_BESTSELLERS_PLUGIN_FILE', __FILE__ );
}

// Include the main VHC_WC_BESTSELLERS class.
if ( ! class_exists( 'VHC_WC_BESTSELLERS', false ) ) {
	include_once dirname( __FILE__ ) . '/includes/class-vhc-wc-bestsellers.php';
}

/**
 * Returns the main instance of VHC_WC_BESTSELLERS.
 *
 * @since  1.0.0
 * @return VHC_WC_Bestsellers
 */
function vhc_wc_bestsellers() {
	return VHC_WC_Bestsellers::instance();
}

// Global for backwards compatibility.
$GLOBALS['vhc_wc_bestsellers'] = vhc_wc_bestsellers();
