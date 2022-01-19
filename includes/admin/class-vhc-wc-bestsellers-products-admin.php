<?php
/**
 * VHC WooCommerce Bestsellers Products Admin
 *
 * @class VHC_WC_Bestsellers_Products_Admin
 * @package VHC_WC_Bestsellers_Products
 * @subpackage VHC_WC_Bestsellers_Products/Admin
 * @version 1.0.0
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

if ( class_exists( 'VHC_WC_Bestsellers_Products_Admin' ) ) {
	return new VHC_WC_Bestsellers_Products_Admin();
}

/**
 * VHC_WC_Bestsellers_Products_Admin class.
 */
class VHC_WC_Bestsellers_Products_Admin {
	/**
	 * Constructor.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		// Includes files.
		add_action( 'init', array( $this, 'includes' ) );

		// Add menus.
		add_action( 'admin_menu', array( $this, 'admin_menu' ) );

		// Enqueue scripts.
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_styles' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_scripts' ) );
	}

	/**
	 * Include any classes/functions we need within admin.
	 *
	 * @since 1.0.0
	 */
	public function includes() {
		// Include your required backend files here.
	}

	/**
	 * Add menu items.
	 */
	public function admin_menu() {
		add_menu_page( __( 'VHC WooCommerce Bestsellers Products', 'vhc-wc-bestsellers-products' ), __( 'VHC WooCommerce Bestsellers Products', 'vhc-wc-bestsellers-products' ), 'manage_options', 'vhc-wc-bestsellers-products-page', array( $this, 'admin_menu_page' ), 'dashicons-wordpress', '60' );
		add_submenu_page( 'vhc-wc-bestsellers-products-page', __( 'Submenu Item', 'vhc-wc-bestsellers-products' ), __( 'Submenu Item', 'vhc-wc-bestsellers-products' ), 'manage_options', 'vhc-wc-bestsellers-products-page-submenu', array( $this, 'submenu_page' ) );
	}

	/**
	 * Valid screen ids for plugin scripts & styles
	 *
	 * @since 1.0.0
	 * @return  array
	 */
	public function is_valid_screen() {
		$screen    = get_current_screen();
		$screen_id = $screen ? $screen->id : '';

		$valid_screen_ids = apply_filters(
			'vhc_wc_bestsellers_products_valid_admin_screen_ids',
			array(
				'vhc-wc-bestsellers-products-page-submenu',
				'vhc-wc-bestsellers-products-page',
			)
		);

		if ( empty( $valid_screen_ids ) ) {
			return false;
		}

		foreach ( $valid_screen_ids as $admin_screen_id ) {
			$matcher = '/' . $admin_screen_id . '/';
			if ( preg_match( $matcher, $screen_id ) ) {
				return true;
			}
		}
		return false;
	}

	/**
	 * Enqueue styles.
	 *
	 * @since 1.0.0
	 */
	public function admin_styles() {
		$suffix = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';

		// Register admin styles.
		wp_register_style( 'vhc-wc-bestsellers-products-admin-styles', vhc_wc_bestsellers_products()->plugin_url() . '/assets/css/admin' . $suffix . '.css', array(), VHC_WC_BESTSELLERS_PRODUCTS_VERSION );

		// Admin styles for vhc_wc_bestsellers_products pages only.
		if ( $this->is_valid_screen() ) {
			wp_enqueue_style( 'vhc-wc-bestsellers-products-admin-styles' );
		}
	}

	/**
	 * Enqueue scripts.
	 *
	 * @since 1.0.0
	 */
	public function admin_scripts() {
		$suffix = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';

		// Register scripts.
		wp_register_script( 'vhc-wc-bestsellers-products-admin', vhc_wc_bestsellers_products()->plugin_url() . '/assets/js/admin' . $suffix . '.js', array( 'jquery' ), VHC_WC_BESTSELLERS_PRODUCTS_VERSION, true );

		// Admin scripts for vhc_wc_bestsellers_products pages only.
		if ( $this->is_valid_screen() ) {
			wp_enqueue_script( 'vhc-wc-bestsellers-products-admin' );
			$params = array(
				'ajax_url' => admin_url( 'admin-ajax.php' ),
			);
			wp_localize_script( 'vhc-wc-bestsellers-products-admin', 'vhc_wc_bestsellers_products_params', $params );
		}
	}

	/**
	 * Display admin page
	 *
	 * @since 1.0.0
	 */
	public function admin_menu_page() {
		?>
		<div class="wrap" id="vhc-wc-bestsellers-products">
			<h2><?php esc_html_e( 'Page title', 'vhc-wc-bestsellers-products' ); ?></h2>
		</div>
		<?php
	}

	/**
	 * Display submenu page
	 *
	 * @since 1.0.0
	 */
	public function submenu_page() {
		?>
		<div class="wrap" id="vhc-wc-bestsellers-products">
			<h2><?php esc_html_e( 'Submenu Item', 'vhc-wc-bestsellers-products' ); ?></h2>
		</div>
		<?php
	}
}

return new VHC_WC_Bestsellers_Products_Admin();
