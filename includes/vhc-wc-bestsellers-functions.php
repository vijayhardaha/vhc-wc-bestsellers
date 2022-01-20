<?php
/**
 * VHC WooCommerce Bestsellers Products core functions & definations
 *
 * Core functions available on both the front-end and admin.
 *
 * @package VHC_WC_BESTSELLERS
 * @subpackage VHC_WC_BESTSELLERS/Functions
 * @version 1.0.0
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Returns bestsellers sales period options.
 *
 * @return array
 */
function vhc_wc_bestsellers_sales_period_options() {
	return array(
		'today'          => __( 'Today orders', 'vhc-wc-sales-report' ),
		'yesterday'      => __( 'Yesterday orders', 'vhc-wc-sales-report' ),
		'last-2-days'    => __( 'Last 3 days orders (excluding today)', 'vhc-wc-sales-report' ),
		'last-3-days'    => __( 'Last 3 days orders (excluding today)', 'vhc-wc-sales-report' ),
		'last-7-days'    => __( 'Last 7 days orders (excluding today)', 'vhc-wc-sales-report' ),
		'last-14-days'   => __( 'Last 14 days orders (excluding today)', 'vhc-wc-sales-report' ),
		'last-30-days'   => __( 'Last 30 days orders (excluding today)', 'vhc-wc-sales-report' ),
		'this-month'     => __( 'This month orders (including today)', 'vhc-wc-sales-report' ),
		'last-month'     => __( 'Last month orders', 'vhc-wc-sales-report' ),
		'last-3-months'  => __( 'Last 3 months orders (excluding current month)', 'vhc-wc-sales-report' ),
		'last-6-months'  => __( 'Last 6 months orders (excluding current month)', 'vhc-wc-sales-report' ),
		'last-12-months' => __( 'Last 12 months orders (excluding current month)', 'vhc-wc-sales-report' ),
		'this-year'      => __( 'This year orders', 'vhc-wc-sales-report' ),
		'last-year'      => __( 'Last year orders', 'vhc-wc-sales-report' ),
		'all'            => __( 'All time', 'vhc-wc-sales-report' ),
	);
}

/**
 * Returns bestsellers products.
 *
 * @param string $range Sales period range.
 * @param array  $args Query args.
 * @since 1.0.0
 * @return array
 */
function vhc_wc_bestsellers_get_products( $range = 'all', $args = array() ) {
	$bs_product_ids = vhc_wc_bestsellers_get_product_ids( $range, $args );

	if ( empty( $bs_product_ids ) ) {
		return false;
	}

	return array_filter( array_map( 'wc_get_product', $bs_product_ids ), 'wc_products_array_filter_visible' );
}

/**
 * Returns bestsellers products.
 *
 * @param string $range Sales period range.
 * @param array  $args Query args.
 * @since 1.0.0
 * @return array
 */
function vhc_wc_bestsellers_get_product_ids( $range = 'all', $args = array() ) {
	$bs_query       = new VHC_WC_Bestsellers_Query();
	$bs_product_ids = $bs_query->get_product_ids( $range, $args );

	if ( empty( $bs_product_ids ) ) {
		return false;
	}

	return $bs_product_ids;
}

/**
 * Callback to bestsellers shortcode.
 *
 * @param array $atts Shortcode attributes.
 * @return string Shortcode output.
 */
function vhc_wc_bestsellers_products_shortcode( $atts ) {
	$atts = shortcode_atts(
		array(
			'columns' => 5,
			'range'   => 'all',
			'limit'   => 100,
		),
		$atts,
		'vhc_wc_bestsellers'
	);

	$bs_products = vhc_wc_bestsellers_get_products( $atts['range'] );

	ob_start();

	if ( $bs_products ) {
		echo '<div class="woocommerce vhc-bestsellers">';

		wc_set_loop_prop( 'name', 'bestsellers' );
		wc_set_loop_prop( 'columns', apply_filters( 'vhc_woocommerce_bestsellers_columns', isset( $atts['columns'] ) ? $atts['columns'] : 5 ) );

		woocommerce_product_loop_start();

		foreach ( $bs_products as $bs_product ) {

			$post_object = get_post( $bs_product->get_id() );

			setup_postdata( $GLOBALS['post'] =& $post_object ); // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited, Squiz.PHP.DisallowMultipleAssignments.Found

			wc_get_template_part( 'content', 'product' );

		}

		woocommerce_product_loop_end();

		echo '</div>';
	}

	wp_reset_postdata();

	return ob_get_clean();
}
add_shortcode( 'vhc_wc_bestsellers', 'vhc_wc_bestsellers_products_shortcode' );
