<?php
/**
 * VHC WooCommerce Bestsellers Frontend Class.
 *
 * @package VHC_WC_Bestsellers
 */

defined( 'ABSPATH' ) || die( 'Don\'t run this file directly!' );

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
		add_shortcode( 'vhc_wc_bestsellers', array( $this, 'bestsellers_shortcode' ) );
	}

	/**
	 * Parse args for report data.
	 *
	 * @since 1.0.0
	 * @param array $args Report args.
	 * @return array
	 */
	private function parse_args( $args ) {
		$default_args = array(
			'period'            => get_option( 'woocommerce_vhc_bestsellers_sales_period', 'all' ),
			'limit'             => get_option( 'woocommerce_vhc_bestsellers_limit', 100 ),
			'fallback'          => true,
			'show_hidden'       => 'yes' === get_option( 'woocommerce_vhc_bestsellers_show_hidden', 'no' ),
			'hide_out_of_stock' => 'yes' === get_option( 'woocommerce_vhc_bestsellers_hide_out_of_stock', 'no' ),
			'hide_free'         => 'yes' === get_option( 'woocommerce_vhc_bestsellers_hide_free', 'no' ),
			'include'           => array(),
			'exclude'           => array(),
			'meta_query'        => array(), // phpcs:ignore WordPress.DB.SlowDBQuery
			'tax_query'         => array(), // phpcs:ignore WordPress.DB.SlowDBQuery
			'terms'             => array(),
			'show'              => '',
			'return'            => '',
		);

		$args = wp_parse_args( $args, $default_args );

		return $args;
	}

	/**
	 * Calculate sales period range.
	 *
	 * @since 1.0.0
	 * @param string $period Period range.
	 * @return array.
	 */
	private function calculate_range( $period = '' ) {
		$one_day  = DAY_IN_SECONDS;
		$midnight = strtotime( 'today midnight' );
		$end_date = $midnight + $one_day - 1;

		switch ( $period ) {
			case 'today':
				$start_date = $midnight;
				break;
			case 'yesterday':
				$start_date = $midnight - DAY_IN_SECONDS;
				break;
			case 'last-2-days':
				$start_date = $midnight - ( 2 * $one_day );
				break;
			case 'last-3-days':
				$start_date = $midnight - ( 3 * $one_day );
				break;
			case 'last-7-days':
				$start_date = $midnight - ( 7 * $one_day );
				break;
			case 'last-14-days':
				$start_date = $midnight - ( 14 * $one_day );
				break;
			case 'last-30-days':
				$start_date = $midnight - ( 30 * $one_day );
				break;
			case 'this-month':
				$start_date = strtotime( 'midnight', strtotime( 'first day of this month' ) );
				break;
			case 'last-month':
				$start_date = strtotime( 'midnight', strtotime( 'first day of last month' ) );
				break;
			case 'last-2-months':
				$start_date = strtotime( 'midnight', strtotime( 'first day of -2 month' ) );
				break;
			case 'last-3-months':
				$start_date = strtotime( 'midnight', strtotime( 'first day of -3 month' ) );
				break;
			case 'last-6-months':
				$start_date = strtotime( 'midnight', strtotime( 'first day of -6 month' ) );
				break;
			case 'last-12-months':
				$start_date = strtotime( 'midnight', strtotime( 'first day of -12 month' ) );
				break;
			case 'this-year':
				$start_date = strtotime( 'midnight', strtotime( gmdate( 'Y-01-01' ) ) );
				break;
			case 'last-year':
				$last_year  = gmdate( 'Y' ) - 1;
				$start_date = strtotime( 'midnight', strtotime( gmdate( $last_year . '-01-01' ) ) );
				break;
			default:
				$start_date = '';
		}

		$range_args = array(
			'start' => $start_date,
			'end'   => $end_date,
		);

		return apply_filters( 'vhc_wc_bestsellers_range_args', $range_args, $period, $midnight );
	}

	/**
	 * Return queried product ids from query args.
	 *
	 * @since 1.0.0
	 * @param array $args Arguments for product query.
	 * @return array
	 */
	private function query_products( $args = array() ) {
		$query_args = array(
			'fields'      => 'ids',
			'nopaging'    => true,
			'post_status' => 'publish',
			'post_type'   => 'product',
			'meta_query'  => array(), // phpcs:ignore WordPress.DB.SlowDBQuery
			'tax_query'   => array( // phpcs:ignore WordPress.DB.SlowDBQuery
				'relation' => 'AND',
			),
		);

		$product_visibility_terms  = wc_get_product_visibility_term_ids();
		$product_visibility_not_in = array();

		if ( empty( $args['show_hidden'] ) ) {
			$product_visibility_not_in[] = $product_visibility_terms['exclude-from-catalog'];
			$query_args['post_parent']   = 0;
		}

		if ( ! empty( $args['hide_out_of_stock'] ) ) {
			$product_visibility_not_in[] = $product_visibility_terms['outofstock'];
		}

		if ( ! empty( $product_visibility_not_in ) ) {
			$query_args['tax_query'][] = array(
				'taxonomy' => 'product_visibility',
				'field'    => 'term_taxonomy_id',
				'terms'    => $product_visibility_not_in,
				'operator' => 'NOT IN',
			);
		}

		if ( ! empty( $args['hide_free'] ) ) {
			$query_args['meta_query'][] = array(
				'key'     => '_price',
				'value'   => 0,
				'compare' => '>',
				'type'    => 'DECIMAL',
			);
		}

		switch ( $args['show'] ) {
			case 'featured':
				$query_args['tax_query'][] = array(
					'taxonomy' => 'product_visibility',
					'field'    => 'term_taxonomy_id',
					'terms'    => $product_visibility_terms['featured'],
				);
				break;
			case 'onsale':
				$product_ids_on_sale    = wc_get_product_ids_on_sale();
				$product_ids_on_sale[]  = 0;
				$query_args['post__in'] = $product_ids_on_sale;
				break;
		}

		if ( ! empty( $args['include'] ) && is_array( $args['include'] ) ) {
			$include_ids = wp_parse_id_list( $args['include'] );
			if ( ! empty( $include_ids ) ) {
				$query_args['post__in'] = isset( $query_args['post__in'] ) ? array_merge( $query_args['post__in'], $include_ids ) : $include_ids;
			}
		}

		if ( ! empty( $args['exclude'] ) && is_array( $args['exclude'] ) ) {
			$exclude_ids = wp_parse_id_list( $args['exclude'] );
			if ( ! empty( $exclude_ids ) ) {
				$query_args['post__not_in'] = isset( $query_args['post__not_in'] ) ? array_merge( $query_args['post__not_in'], $exclude_ids ) : $exclude_ids;
			}
		}

		if ( ! empty( $args['tax_query'] ) && is_array( $args['tax_query'] ) ) {
			$query_args['tax_query'] = array_merge( $query_args['tax_query'], $args['tax_query'] ); // phpcs:ignore WordPress.DB.SlowDBQuery
		}

		if ( ! empty( $args['meta_query'] ) && is_array( $args['meta_query'] ) ) {
			$query_args['meta_query'] = array_merge( $query_args['meta_query'], $args['meta_query'] ); // phpcs:ignore WordPress.DB.SlowDBQuery
		}

		if ( ! empty( $args['terms'] ) && is_array( $args['terms'] ) ) {
			foreach ( $args['terms'] as $term_name => $term_data ) {
				if ( is_string( $term_name ) ) {
					$query_args['tax_query'][] = array(
						'taxonomy' => $term_name,
						'field'    => 'slug',
						'terms'    => (array) $term_data,
						'operator' => 'IN',
					);
				}
			}
		}

		$transient_name = 'vhcbs_query_products';
		$cache_args     = http_build_query( $query_args );

		$transient   = get_transient( $transient_name );
		$product_ids = $transient && isset( $transient[ $cache_args ] ) ? $transient[ $cache_args ] : false;

		if ( false === $product_ids ) {
			$product_ids = wp_parse_id_list( get_posts( $query_args ) );

			if ( $transient ) {
				$transient[ $cache_args ] = $product_ids;
			} else {
				$transient = array( $cache_args => $product_ids );
			}

			set_transient( $transient_name, $transient, DAY_IN_SECONDS );
		}

		return $product_ids;
	}

	/**
	 * Return bestsellers.
	 *
	 * @since 1.0.0
	 * @param array $args Arguments array.
	 * @return array
	 */
	public function get_bestsellers( $args = array() ) {
		global $wpdb;

		$filter_range = false;
		$args         = $this->parse_args( $args );
		$range        = empty( $args['period'] ) ? 'all' : sanitize_text_field( $args['period'] );

		// Check if range is not all time.
		if ( 'all' !== $range ) {
			// Calulate start and end date from range type.
			$range_args = $this->calculate_range( $range );

			// If start or end date is empty then return false and do nothing.
			if ( empty( $range_args['start'] ) || empty( $range_args['end'] ) ) {
				return false;
			}

			// If start and end date exists then set filter range true.
			$filter_range = true;
		}

		// Get queried product ids.
		$product_ids = $this->query_products( $args );

		// If queried products ids are empty and fallback is not true then do nothing.
		if ( empty( $product_ids ) && empty( $args['fallback'] ) ) {
			return false;
		}

		// Validate limit. We do not accept -1 for show all and more than 100.
		$limit = intval( $args['limit'] ) > 0 && intval( $args['limit'] ) <= 100 ? absint( $args['limit'] ) : 100;

		// Create a new WC_Admin_Report object.
		include_once WC()->plugin_path() . '/includes/admin/reports/class-wc-admin-report.php';

		$wc_report = new WC_Admin_Report();
		if ( 'all' !== $range ) {
			$wc_report->start_date = $range_args['start'];
			$wc_report->end_date   = $range_args['end'];
		}

		$where_meta = array();
		if ( ! empty( $product_ids ) ) {
			$where_meta[] = array(
				'meta_key'   => '_product_id', // phpcs:ignore WordPress.DB.SlowDBQuery
				'meta_value' => $product_ids, // phpcs:ignore WordPress.DB.SlowDBQuery
				'operator'   => 'IN',
				'type'       => 'order_item_meta',
			);
		}

		$report_args = array(
			'data'         => array(
				'_product_id' => array(
					'type'            => 'order_item_meta',
					'order_item_type' => 'line_item',
					'function'        => '',
					'name'            => 'product_id',
				),
				'_qty'        => array(
					'type'            => 'order_item_meta',
					'order_item_type' => 'line_item',
					'function'        => 'SUM',
					'name'            => 'quantity',
				),
			),
			'query_type'   => 'get_results',
			'group_by'     => 'product_id',
			'where_meta'   => $where_meta,
			'order_by'     => 'quantity DESC',
			'limit'        => $limit,
			'filter_range' => $filter_range,
			'order_types'  => wc_get_order_types( 'order-count' ),
		);

		// Based on woocoommerce/includes/admin/reports/class-wc-report-sales-by-product.php.
		$bs_products = $wc_report->get_order_report_data( $report_args );

		if ( empty( $bs_products ) ) {
			return false;
		}

		$bs_product_ids = wp_list_pluck( $bs_products, 'product_id' );

		$output_query_args = array(
			'fields'        => 'ids',
			'nopaging'      => true,
			'no_found_rows' => true,
			'post_status'   => 'publish',
			'post_type'     => 'product',
			'orderby'       => 'post__in',
			'post__in'      => $bs_product_ids,
		);

		switch ( $args['return'] ) {
			case 'query_objects':
				unset( $output_query_args['fields'] );
				$output = new WP_Query( $output_query_args );
				break;
			case 'products':
				$ids    = get_posts( $output_query_args );
				$output = ! empty( $ids ) ? array_map( 'wc_get_product', $ids ) : false;
				break;
			case 'ids':
			default:
				$output = get_posts( $output_query_args );
				break;
		}

		return $output;
	}

	/**
	 * Callback to bestsellers shortcode.
	 *
	 * @since 1.0.0
	 * @param array $atts Shortcode attributes.
	 * @return string Shortcode output.
	 */
	public function bestsellers_shortcode( $atts ) {
		$atts = shortcode_atts(
			apply_filters(
				'vhc_wc_bestsellers_shortcode_atts',
				array(
					'period'  => 'all',
					'columns' => 5,
					'limit'   => 5,
				)
			),
			$atts,
			'vhc_wc_bestsellers'
		);

		$query_args = apply_filters(
			'vhc_wc_bestsellers_shortcode_query_args',
			array(
				'period' => $atts['period'],
				'limit'  => $atts['limit'],
			)
		);

		$query_args['return'] = 'query_objects';

		$products = $this->get_bestsellers( $query_args );

		ob_start();

		if ( $products && $products->have_posts() ) {
			echo '<div class="woocommerce vhc-bestsellers">';

			wc_set_loop_prop( 'name', 'bestsellers' );
			wc_set_loop_prop( 'columns', apply_filters( 'vhc_woocommerce_bestsellers_columns', isset( $atts['columns'] ) ? $atts['columns'] : 5 ) );

			woocommerce_product_loop_start();

			while ( $products->have_posts() ) {
				$products->the_post();

				wc_get_template_part( 'content', 'product' );
			}

			woocommerce_product_loop_end();

			echo '</div>';
		}

		wp_reset_postdata();

		return ob_get_clean();
	}
}

return new VHC_WC_Bestsellers_Frontend();
