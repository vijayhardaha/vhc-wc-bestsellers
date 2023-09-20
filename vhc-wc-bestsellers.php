<?php
/**
 * Plugin Name: VHC WooCommerce Bestsellers
 * Plugin URI: https://github.com/vijayhardaha/vhc-wc-bestsellers/
 * Description: Displays your store's bestselling products from a specific period of time. These can be displayed through a Best Sellers page or a shortcode/widget.
 * Version: 1.1.1
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

defined( 'ABSPATH' ) || exit; // Exit if accessed directly.

if ( ! defined( 'VHC_WC_BESTSELLERS_PLUGIN_FILE' ) ) {
	define( 'VHC_WC_BESTSELLERS_PLUGIN_FILE', __FILE__ );
}

define( 'VHC_WC_BESTSELLERS_VERSION', '1.1.1' );
define( 'VHC_WC_BESTSELLERS_PLUGIN_NAME', 'VHC WooCommerce Bestsellers' );
define( 'VHC_WC_BESTSELLERS_ABSPATH', dirname( VHC_WC_BESTSELLERS_PLUGIN_FILE ) . '/' );
define( 'VHC_WC_BESTSELLERS_PLUGIN_BASENAME', plugin_basename( VHC_WC_BESTSELLERS_PLUGIN_FILE ) );

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
