<?php
/**
 * VHC WooCommerce Bestsellers Setup Class.
 *
 * @package VHC_WC_Bestsellers
 */

defined( 'ABSPATH' ) || exit; // Exit if accessed directly.

/**
 * Main VHC_WC_Bestsellers Class.
 */
final class VHC_WC_Bestsellers {

	/**
	 * This class instance.
	 *
	 * @var VHC_WC_Bestsellers single instance of this class.
	 * @since 1.0.0
	 */
	private static $instance;

	/**
	 * Frontend class instance.
	 *
	 * @var VHC_WC_Bestsellers_Frontend single instance of frontend class.
	 * @since 1.0.0
	 */
	private $frontend;

	/**
	 * Archive class instance.
	 *
	 * @var VHC_WC_Bestsellers_Archive single instance of archive class.
	 * @since 1.0.0
	 */
	private $archive;

	/**
	 * Main VHC_WC_Bestsellers Instance.
	 * Ensures only one instance of VHC_WC_Bestsellers is loaded or can be loaded.
	 *
	 * @since 1.0.0
	 * @return VHC_WC_Bestsellers - Main instance.
	 */
	public static function instance() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * VHC_WC_Bestsellers Constructor.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		register_shutdown_function( array( $this, 'log_errors' ) );

		add_action( 'plugins_loaded', array( $this, 'load_plugin_textdomain' ) );
		add_action( 'plugins_loaded', array( $this, 'init_plugin' ), 9 );
	}

	/**
	 * Ensures fatal errors are logged so they can be picked up in the status report.
	 *
	 * @since 1.0.0
	 */
	public function log_errors() {
		$error = error_get_last();
		if ( $error && in_array( $error['type'], array( E_ERROR, E_PARSE, E_COMPILE_ERROR, E_USER_ERROR, E_RECOVERABLE_ERROR ), true ) ) {
			if ( defined( 'WP_DEBUG' ) && WP_DEBUG === true ) {
				/* translators: 1: Error Message 2: File Name and Path 3: Line Number */
				$error_message = sprintf( __( '%1$s in %2$s on line %3$s', 'vhc-wc-bestsellers' ), $error['message'], $error['file'], $error['line'] ) . PHP_EOL;
				// phpcs:disable WordPress.PHP.DevelopmentFunctions
				error_log( $error_message );
				// phpcs:enable WordPress.PHP.DevelopmentFunctions
			}
		}
	}

	/**
	 * Load Localisation files.
	 *
	 * Note: the first-loaded translation file overrides any following ones if the same translation is present.
	 *
	 * Locales found in:
	 *      - WP_LANG_DIR/vhc-wc-bestsellers/vhc-wc-bestsellers-LOCALE.mo
	 *      - WP_LANG_DIR/plugins/vhc-wc-bestsellers-LOCALE.mo
	 *
	 * @since 1.0.0
	 */
	public function load_plugin_textdomain() {
		if ( function_exists( 'determine_locale' ) ) {
			$locale = determine_locale();
		} else {
			$locale = is_admin() ? get_user_locale() : get_locale();
		}

		$locale = apply_filters( 'plugin_locale', $locale, 'vhc-wc-bestsellers' );

		unload_textdomain( 'vhc-wc-bestsellers' );
		load_textdomain( 'vhc-wc-bestsellers', WP_LANG_DIR . '/vhc-wc-bestsellers/vhc-wc-bestsellers-' . $locale . '.mo' );
		load_plugin_textdomain( 'vhc-wc-bestsellers', false, plugin_basename( dirname( VHC_WC_BESTSELLERS_PLUGIN_FILE ) ) . '/languages' );
	}

	/**
	 * Hook into actions and filters.
	 *
	 * @since 1.0.0
	 */
	public function init_plugin() {
		if ( $this->is_woocommerce_activated() === false ) {
			add_action( 'admin_notices', array( $this, 'required_woocommerce_notice' ) );
			return;
		}

		if ( version_compare( PHP_VERSION, '7.1', '<' ) ) {
			add_action( 'admin_notices', array( $this, 'required_php_version_notice' ) );
		}

		// all systems ready - GO!
		$this->includes();
	}

	/**
	 * Include required core files used in admin and on the frontend.
	 *
	 * @since 1.0.0
	 */
	public function includes() {
		// Core classes.
		include_once VHC_WC_BESTSELLERS_ABSPATH . 'includes/class-vhc-wc-widget-bestsellers.php';
		include_once VHC_WC_BESTSELLERS_ABSPATH . 'includes/class-vhc-wc-bestsellers-admin.php';

		$this->frontend = include_once VHC_WC_BESTSELLERS_ABSPATH . 'includes/class-vhc-wc-bestsellers-frontend.php';
		$this->archive  = include_once VHC_WC_BESTSELLERS_ABSPATH . 'includes/class-vhc-wc-bestsellers-archive.php';
	}

	/**
	 * Check if woocommerce is activated
	 */
	public function is_woocommerce_activated() {
		$blog_plugins = get_option( 'active_plugins', array() );
		$site_plugins = is_multisite() ? (array) maybe_unserialize( get_site_option( 'active_sitewide_plugins' ) ) : array();

		if ( in_array( 'woocommerce/woocommerce.php', $blog_plugins, true ) || isset( $site_plugins['woocommerce/woocommerce.php'] ) ) {
			return true;
		}

		return false;
	}

	/**
	 * WooCommerce not active notice.
	 *
	 * @return void
	 */
	public function required_woocommerce_notice() {
		/* translators: <a> tags */
		$error = sprintf( esc_html__( 'VHC WooCommerce Bestsellers requires %1$sWooCommerce%2$s to be installed & activated!', 'vhc-wc-bestsellers' ), '<a href="http://wordpress.org/extend/plugins/woocommerce/">', '</a>' );

		$message = '<div class="error"><p>' . $error . '</p></div>';

		echo wp_kses_post( $message );
	}

	/**
	 * PHP version requirement notice
	 */
	public function required_php_version_notice() {
		$error_message = __( 'VHC WooCommerce Bestsellers requires PHP 7.1 (7.4 or higher recommended). We strongly recommend to update your PHP version.', 'vhc-wc-bestsellers' );

		$message  = '<div class="error">';
		$message .= sprintf( '<p>%s</p>', $error_message );
		$message .= '</div>';

		echo wp_kses_post( $message );
	}

	/**
	 * Get the plugin url.
	 *
	 * @since 1.0.0
	 * @return string
	 */
	public function plugin_url() {
		return untrailingslashit( plugins_url( '/', VHC_WC_BESTSELLERS_PLUGIN_FILE ) );
	}

	/**
	 * Get the plugin path.
	 *
	 * @since 1.0.0
	 * @return string
	 */
	public function plugin_path() {
		return untrailingslashit( plugin_dir_path( VHC_WC_BESTSELLERS_PLUGIN_FILE ) );
	}

	/**
	 * Return bestsellers.
	 *
	 * @since 1.0.0
	 * @param array $args Arguments array.
	 * @return array
	 */
	public function get_bestsellers( $args = array() ) {
		return class_exists( 'VHC_WC_Bestsellers_Frontend' ) ? VHC_WC_Bestsellers_Frontend::get_bestsellers( $args ) : false;
	}

	/**
	 * Check if current page is bestsellers archive page.
	 *
	 * @since 1.0.0
	 * @return bool
	 */
	public function is_archive() {
		return class_exists( 'VHC_WC_Bestsellers_Archive' ) ? VHC_WC_Bestsellers_Archive::is_page() : false;
	}
}
