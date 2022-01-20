<?php
/**
 * VHC WooCommerce Bestsellers Products Admin
 *
 * @class VHC_WC_Bestsellers_Admin
 * @package VHC_WC_Bestsellers
 * @subpackage VHC_WC_Bestsellers/Admin
 * @version 1.0.0
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

if ( class_exists( 'VHC_WC_Bestsellers_Admin' ) ) {
	return new VHC_WC_Bestsellers_Admin();
}

/**
 * VHC_WC_Bestsellers_Admin class.
 */
class VHC_WC_Bestsellers_Admin {
	/**
	 * Constructor.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		// Include widget files.
		$this->includes();

		// Register Widgets.
		add_action( 'widgets_init', array( $this, 'register_widgets' ) );

		add_filter( 'woocommerce_get_sections_products', array( $this, 'add_setting_setion' ) );
		add_filter( 'woocommerce_get_settings_products', array( $this, 'add_settings' ), 10, 2 );
	}

	/**
	 * Include any classes/functions we need within admin.
	 *
	 * @since 1.0.0
	 */
	public function includes() {
		require_once VHC_WC_BESTSELLERS_ABSPATH . 'includes/widgets/class-vhc-wc-widget-bestsellers.php';
	}

	/**
	 * Register Widgets.
	 *
	 * @since 1.0.0
	 */
	public function register_widgets() {
		register_widget( 'VHC_WC_Widget_Bestsellers' );
	}

	/**
	 * Add setting section in product settings
	 *
	 * @param array $sections Sections array.
	 *
	 * @since 1.0.0
	 * @return array
	 */
	public function add_setting_setion( $sections ) {
		$sections['vhc-bestsellers'] = __( 'VHC Bestsellers', 'vhc-wc-bestsellers' );
		return $sections;
	}

	/**
	 * Display admin settings.
	 *
	 * @param array  $settings Settings array.
	 * @param string $section_id Current section id.
	 *
	 * @since 1.0.0
	 * @return array
	 */
	public function add_settings( $settings, $section_id ) {
		if ( 'vhc-bestsellers' === $section_id ) {
			$settings = array(
				array(
					'title' => __( 'VHC Bestsellers options', 'vhc-wc-bestsellers' ),
					'type'  => 'title',
					'desc'  => __( 'Manage your bestsellers products default settings here. Shortcode to use: [vhc_wc_bestsellers limit="100" columns="5" range="all"]', 'vhc-wc-bestsellers' ),
				),
				array(
					'title'   => __( 'Bestsellers page', 'vhc-wc-bestsellers' ),
					'desc'    => __( 'Set your bestsellers page here.', 'vhc-wc-bestsellers' ),
					'id'      => 'woocommerce_vhc_bestsellers_page_id',
					'type'    => 'single_select_page',
					'default' => '',
					'class'   => 'wc-enhanced-select-nostd',
				),
				array(
					'title'   => __( 'Sales period', 'vhc-wc-bestsellers' ),
					'desc'    => __( 'Set your bestsellers page here.', 'vhc-wc-bestsellers' ),
					'id'      => 'woocommerce_vhc_bestsellers_sales_period',
					'type'    => 'select',
					'default' => 'all',
					'class'   => 'wc-enhanced-select',
					'options' => vhc_wc_bestsellers_sales_period_options(),
				),
				array(
					'title'             => __( 'Limit', 'vhc-wc-bestsellers' ),
					'desc'              => __( 'Select the number of products to show as best sellers.', 'vhc-wc-bestsellers' ),
					'id'                => 'woocommerce_vhc_bestsellers_limit',
					'type'              => 'number',
					'custom_attributes' => array(
						'min'  => 0,
						'max'  => 200,
						'step' => 1,
					),
					'css'               => 'width: 80px;',
					'default'           => 100,
					'autoload'          => false,
				),
				array( 'type' => 'sectionend' ),
			);

		}

		return $settings;
	}
}

return new VHC_WC_Bestsellers_Admin();
