<?php
/**
 * VHC WooCommerce Bestsellers Frontend
 *
 * @class VHC_WC_Bestsellers_Frontend
 * @package VHC_WC_Bestsellers
 * @subpackage VHC_WC_Bestsellers/Frontend
 * @version 1.0.0
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

if ( class_exists( 'VHC_WC_Bestsellers_Frontend' ) ) {
	return new VHC_WC_Bestsellers_Frontend();
}

/**
 * VHC_WC_Bestsellers_Frontend class.
 */
class VHC_WC_Bestsellers_Frontend {
	/**
	 * Constructor.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		// Enqueue scripts.
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
	}

	/**
	 * Enqueue scripts.
	 *
	 * @since 1.0.0
	 */
	public function enqueue_scripts() {
		$suffix = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';

		// Styles.
		wp_enqueue_style( 'vhc-wc-bestsellers-frontend-styles', vhc_wc_bestsellers()->plugin_url() . '/assets/css/frontend' . $suffix . '.css', array(), VHC_WC_BESTSELLERS_VERSION );

		// Scripts.
		wp_enqueue_script( 'vhc-wc-bestsellers-frontend', vhc_wc_bestsellers()->plugin_url() . '/assets/js/frontend' . $suffix . '.js', array( 'jquery' ), VHC_WC_BESTSELLERS_VERSION, true );

		$params = array(
			'ajax_url' => admin_url( 'admin-ajax.php' ),
		);
		wp_localize_script( 'vhc-wc-bestsellers-frontend', 'vhc_wc_bestsellers_params', $params );
	}
}

return new VHC_WC_Bestsellers_Frontend();
