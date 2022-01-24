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
		// Register Widgets.
		add_action( 'widgets_init', array( $this, 'register_widgets' ) );

		if ( is_admin() ) {
			add_filter( 'plugin_action_links_' . VHC_WC_BESTSELLERS_PLUGIN_BASENAME, array( $this, 'plugin_manage_link' ), 10, 4 );
			add_filter( 'woocommerce_get_sections_products', array( $this, 'add_setting_setion' ) );
			add_filter( 'woocommerce_get_settings_products', array( $this, 'add_settings' ), 10, 2 );
		}
	}

	/**
	 * Return the plugin action links.
	 *
	 * @param array $actions Associative array of action names to anchor tags.
	 * @since 1.0.0
	 * @return array
	 */
	public function plugin_manage_link( $actions ) {
		$url = add_query_arg(
			array(
				'page'    => 'wc-settings',
				'tab'     => 'products',
				'section' => 'vhc-bestsellers',
			),
			admin_url( 'admin.php' )
		);

		$actions['settings'] = '<a href="' . esc_url( $url ) . '">' . esc_html__( 'Settings', 'vhc-wc-sales-report' ) . '</a>';

		return $actions;
	}

	/**
	 * Returns bestsellers sales period options.
	 *
	 * @since 1.0.0
	 * @return array
	 */
	public static function sales_periods() {
		$options = array(
			'today'          => __( 'Today', 'vhc-wc-sales-report' ),
			'yesterday'      => __( 'Yesterday', 'vhc-wc-sales-report' ),
			'last-2-days'    => __( 'Last 2 days', 'vhc-wc-sales-report' ),
			'last-3-days'    => __( 'Last 3 days', 'vhc-wc-sales-report' ),
			'last-7-days'    => __( 'Last 7 days', 'vhc-wc-sales-report' ),
			'last-14-days'   => __( 'Last 14 days', 'vhc-wc-sales-report' ),
			'last-30-days'   => __( 'Last 30 days', 'vhc-wc-sales-report' ),
			'this-month'     => __( 'This month', 'vhc-wc-sales-report' ),
			'last-month'     => __( 'Last month', 'vhc-wc-sales-report' ),
			'last-2-months'  => __( 'Last 2 months', 'vhc-wc-sales-report' ),
			'last-3-months'  => __( 'Last 3 months', 'vhc-wc-sales-report' ),
			'last-6-months'  => __( 'Last 6 months', 'vhc-wc-sales-report' ),
			'last-12-months' => __( 'Last 12 months', 'vhc-wc-sales-report' ),
			'this-year'      => __( 'This year', 'vhc-wc-sales-report' ),
			'last-year'      => __( 'Last year', 'vhc-wc-sales-report' ),
			'all'            => __( 'All time', 'vhc-wc-sales-report' ),
		);

		return apply_filters( 'vhc_wc_bestsellers_sales_periods_options', $options );
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
	 * @since 1.0.0
	 * @return array
	 */
	public function add_settings( $settings, $section_id ) {
		if ( 'vhc-bestsellers' === $section_id ) {
			$settings = array(
				array(
					'title' => __( 'VHC Bestsellers', 'vhc-wc-bestsellers' ),
					'type'  => 'title',
				),
				array(
					'title'   => __( 'Bestsellers page', 'vhc-wc-bestsellers' ),
					'desc'    => __( 'The base page for bestsellers archive.', 'vhc-wc-bestsellers' ),
					'id'      => 'woocommerce_vhc_bestsellers_page_id',
					'type'    => 'single_select_page',
					'default' => '',
					'class'   => 'wc-enhanced-select-nostd',
				),
				array(
					'title'   => __( 'Sales period', 'vhc-wc-bestsellers' ),
					'desc'    => __( 'Default sales period for bestsellers archive.', 'vhc-wc-bestsellers' ),
					'id'      => 'woocommerce_vhc_bestsellers_sales_period',
					'type'    => 'select',
					'default' => 'all',
					'class'   => 'wc-enhanced-select',
					'options' => self::sales_periods(),
				),
				array(
					'title'             => __( 'Limit', 'vhc-wc-bestsellers' ),
					'desc'              => __( 'Number of products to show as bestsellers archive.', 'vhc-wc-bestsellers' ),
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
				array(
					'title'   => __( 'Hide free products', 'vhc-wc-bestsellers' ),
					'desc'    => __( 'Hide free products from bestsellers.', 'vhc-wc-bestsellers' ),
					'id'      => 'woocommerce_vhc_bestsellers_hide_free',
					'default' => 'no',
					'type'    => 'checkbox',
				),
				array( 'type' => 'sectionend' ),
			);

		}

		return $settings;
	}
}

return new VHC_WC_Bestsellers_Admin();
