<?php
/**
 * VHC WooCommerce Bestsellers Products Admin
 *
 * @class VHC_WC_Bestsellers_Query
 * @package VHC_WC_Bestsellers
 * @subpackage VHC_WC_Bestsellers/Query
 * @version 1.0.0
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * VHC_WC_Bestsellers_Query class.
 */
class VHC_WC_Bestsellers_Query {
	/**
	 * Constructor.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
	}

	/**
	 * Calculate sales period range.
	 *
	 * @param string $range Period range.
	 *
	 * @since 1.0.0
	 * @return array.
	 */
	public function calculate_range( $range = '' ) {
		$midnight  = strtotime( 'today midnight' );
		$postnight = $midnight + DAY_IN_SECONDS - 1;

		switch ( $range ) {
			case 'today':
				$start_date = $midnight;
				$end_date   = $postnight;
				break;
			case 'yesterday':
				$start_date = $midnight - DAY_IN_SECONDS;
				$end_date   = $start_date + DAY_IN_SECONDS - 1;
				break;
			case 'last-3-days':
				$start_date = $midnight - ( 3 * DAY_IN_SECONDS );
				$end_date   = $midnight - 1;
				break;
			case 'last-7-days':
				$start_date = $midnight - ( 7 * DAY_IN_SECONDS );
				$end_date   = $midnight - 1;
				break;
			case 'last-14-days':
				$start_date = $midnight - ( 14 * DAY_IN_SECONDS );
				$end_date   = $midnight - 1;
				break;
			case 'last-30-days':
				$start_date = $midnight - ( 30 * DAY_IN_SECONDS );
				$end_date   = $midnight - 1;
				break;
			case 'this-month':
				$start_date = strtotime( 'midnight', strtotime( 'first day of this month' ) );
				$end_date   = strtotime( 'midnight', strtotime( 'last day of this month' ) ) + DAY_IN_SECONDS - 1;
				break;
			case 'last-month':
				$start_date = strtotime( 'midnight', strtotime( 'first day of last month' ) );
				$end_date   = strtotime( 'midnight', strtotime( 'last day of last month' ) ) + DAY_IN_SECONDS - 1;
				break;
			case 'last-3-months':
				$start_date = strtotime( 'midnight', strtotime( 'first day of -3 month' ) );
				$end_date   = strtotime( 'midnight', strtotime( 'last day of last month' ) ) + DAY_IN_SECONDS - 1;
				break;
			case 'last-6-months':
				$start_date = strtotime( 'midnight', strtotime( 'first day of -6 month' ) );
				$end_date   = strtotime( 'midnight', strtotime( 'last day of last month' ) ) + DAY_IN_SECONDS - 1;
				break;
			case 'last-12-months':
				$start_date = strtotime( 'midnight', strtotime( 'first day of -12 month' ) );
				$end_date   = strtotime( 'midnight', strtotime( 'last day of last month' ) ) + DAY_IN_SECONDS - 1;
				break;
			case 'this-year':
				$start_date = strtotime( 'midnight', strtotime( gmdate( 'Y-01-01' ) ) );
				$end_date   = strtotime( 'midnight', strtotime( gmdate( 'Y-12-31' ) ) ) + DAY_IN_SECONDS - 1;
				break;
			case 'last-year':
				$last_year  = gmdate( 'Y' ) - 1;
				$start_date = strtotime( 'midnight', strtotime( gmdate( $last_year . '-01-01' ) ) );
				$end_date   = strtotime( 'midnight', strtotime( gmdate( $last_year . '-12-31' ) ) ) + DAY_IN_SECONDS - 1;
				break;
			default:
				$start_date = '';
				$end_date   = '';
		}

		return array(
			'start' => $start_date,
			'end'   => $end_date,
		);
	}

	/**
	 * Return product ids.
	 *
	 * @param string $range Sales period range.
	 * @param array  $args Query args.
	 * @return array
	 */
	public function get_product_ids( $range = '', $args = array() ) {
		global $wpdb;

		$products = array();

		$range = empty( $range ) ? get_option( 'woocommerce_vhc_bestsellers_sales_period', 'all' ) : $range;
		$range = wc_clean( $range );

		$default_limit = get_option( 'woocommerce_vhc_bestsellers_limit', 100 );
		$default_args  = array(
			'limit' => $default_limit,
			'ids'   => array(),
		);

		// Set range filter to false by default.
		$filter_range = false;

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

		$args = wp_parse_args( $args, $default_args );

		// Validate limit. We do not accept -1 for show all and more than 100.
		$limit = isset( $args['limit'] ) && intval( $args['limit'] ) > 0 && intval( $args['limit'] ) <= 100 ? absint( $args['limit'] ) : $default_limit;

		// Create a new WC_Admin_Report object.
		include_once WC()->plugin_path() . '/includes/admin/reports/class-wc-admin-report.php';

		$wc_report = new WC_Admin_Report();
		if ( 'all' !== $range ) {
			$wc_report->start_date = $range_args['start'];
			$wc_report->end_date   = $range_args['end'];
		}

		$where_meta = array();
		if ( ! empty( $args['ids'] ) && is_array( $args['ids'] ) ) {
			$where_meta[] = array(
				'meta_key'   => '_product_id', // phpcs:ignore WordPress.DB.SlowDBQuery
				'meta_value' => array_unique( (array) $args['ids'] ), // phpcs:ignore WordPress.DB.SlowDBQuery
				'operator'   => 'IN',
				'type'       => 'order_item_meta',
			);
		}

		// Avoid max join size error.
		$wpdb->query( 'SET SQL_BIG_SELECTS=1' ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery

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
		$sold_products = $wc_report->get_order_report_data( $report_args );

		// Filter Existing products.
		foreach ( $sold_products as $p ) {
			if ( get_post( absint( $p->product_id ) ) ) {
				$products[] = absint( $p->product_id );
			}
		}

		return $products;
	}
}
