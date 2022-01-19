<?php
/**
 * VHC WooCommerce Bestsellers Products Frontend
 *
 * @class VHC_WC_Bestsellers_Products_Frontend
 * @package VHC_WC_Bestsellers_Products
 * @subpackage VHC_WC_Bestsellers_Products/Frontend
 * @version 1.0.0
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

if ( class_exists( 'VHC_WC_Bestsellers_Products_Frontend' ) ) {
	return new VHC_WC_Bestsellers_Products_Frontend();
}

/**
 * VHC_WC_Bestsellers_Products_Frontend class.
 */
class VHC_WC_Bestsellers_Products_Frontend {
	/**
	 * Constructor.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		// Includes files.
		add_action( 'init', array( $this, 'includes' ) );

		// Enqueue scripts.
		add_action( 'wp_enqueue_scripts', array( $this, 'frontend_styles' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'frontend_scripts' ) );
	}

	/**
	 * Include any classes/functions we need within frontend.
	 *
	 * @since 1.0.0
	 */
	public function includes() {
		// Include your required frontend files here.
	}

	/**
	 * Enqueue styles.
	 */
	public function frontend_styles() {
		$suffix = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';

		// Register frontend styles.
		wp_register_style( 'vhc-wc-bestsellers-products-frontend-styles', vhc_wc_bestsellers_products()->plugin_url() . '/assets/css/frontend' . $suffix . '.css', array(), VHC_WC_BESTSELLERS_PRODUCTS_VERSION );

		// Enqueue frontend styles.
		wp_enqueue_style( 'vhc-wc-bestsellers-products-frontend-styles' );
	}

	/**
	 * Enqueue scripts.
	 *
	 * @since 1.0.0
	 */
	public function frontend_scripts() {
		$suffix = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';

		// Register scripts.
		wp_register_script( 'vhc-wc-bestsellers-products-frontend', vhc_wc_bestsellers_products()->plugin_url() . '/assets/js/frontend' . $suffix . '.js', array( 'jquery' ), VHC_WC_BESTSELLERS_PRODUCTS_VERSION, true );

		// Enqueue frontend scripts.
		wp_enqueue_script( 'vhc-wc-bestsellers-products-frontend' );
		$params = array(
			'ajax_url' => admin_url( 'admin-ajax.php' ),
		);
		wp_localize_script( 'vhc-wc-bestsellers-products-frontend', 'vhc_wc_bestsellers_products_params', $params );
	}
}

return new VHC_WC_Bestsellers_Products_Frontend();
