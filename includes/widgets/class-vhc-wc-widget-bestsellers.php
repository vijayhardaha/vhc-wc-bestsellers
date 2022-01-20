<?php
/**
 * Bestsellers products list.
 *
 * @class VHC_WC_Widget_Bestsellers
 * @package VHC_WC_Bestsellers
 * @subpackage VHC_WC_Bestsellers/Widget
 * @version 1.0.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * Widget bestsellers products.
 */
class VHC_WC_Widget_Bestsellers extends WC_Widget {

	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->widget_id          = 'vhc_wc_bestsellers';
		$this->widget_name        = __( 'VHC WooCommerce Bestsellers', 'vhc-wc-bestsellers' );
		$this->widget_description = __( 'List of your store\'s bestsellers products.', 'vhc-wc-bestsellers' );
		$this->widget_cssclass    = 'woocommerce widget_products vhc-wc-bs-products';
		$this->settings           = array(
			'title'   => array(
				'type'  => 'text',
				'std'   => __( 'Best Sellers', 'vhc-wc-bestsellers' ),
				'label' => __( 'Title', 'vhc-wc-bestsellers' ),
			),
			'number'  => array(
				'type'  => 'number',
				'step'  => 1,
				'min'   => 1,
				'max'   => 50,
				'std'   => 10,
				'label' => __( 'Number of products to show', 'vhc-wc-bestsellers' ),
			),
			'range'   => array(
				'type'    => 'select',
				'std'     => 'last-7-days',
				'label'   => __( 'Sales Period', 'vhc-wc-bestsellers' ),
				'options' => vhc_wc_bestsellers_sales_period_options(),
			),
			'show'    => array(
				'type'    => 'select',
				'std'     => '',
				'label'   => __( 'Show', 'vhc-wc-bestsellers' ),
				'options' => array(
					''       => __( 'All products', 'vhc-wc-bestsellers' ),
					'onsale' => __( 'On-sale products', 'vhc-wc-bestsellers' ),
				),
			),
			'dynamic' => array(
				'type'  => 'checkbox',
				'std'   => 1,
				'label' => __( 'Enable Dynamic Filter. (Filter bestsellers by current product category, product tag page)', 'vhc-wc-bestsellers' ),
			),
		);

		parent::__construct();
	}

	/**
	 * Query the products and return them.
	 *
	 * @param array $args     Arguments.
	 * @param array $instance Widget instance.
	 *
	 * @return WP_Query
	 */
	public function get_products( $args, $instance ) {
		global $wp_query, $post;

		$number = ! empty( $instance['number'] ) ? absint( $instance['number'] ) : $this->settings['number']['std'];
		$show   = ! empty( $instance['show'] ) ? sanitize_title( $instance['show'] ) : $this->settings['show']['std'];
		$range  = ! empty( $instance['range'] ) ? sanitize_title( $instance['range'] ) : $this->settings['range']['std'];

		$product_ids = array();

		$do_query   = false;
		$query_args = array(
			'fields'      => 'ids',
			'nopaging'    => true,
			'post_status' => 'publish',
			'post_type'   => 'product',
			'meta_query'  => array(), // phpcs:ignore WordPress.DB.SlowDBQuery
			'tax_query'   => array( // phpcs:ignore WordPress.DB.SlowDBQuery
				'relation' => 'AND',
				array(
					'taxonomy'         => 'product_visibility',
					'terms'            => 'exclude-from-catalog',
					'field'            => 'name',
					'operator'         => 'NOT IN',
					'include_children' => false,
				),
			),
		);

		if ( ! empty( $instance['dynamic'] ) ) {

			if ( is_tax( 'product_cat' ) ) {
				$do_query                  = true;
				$query_args['tax_query'][] = array(
					'taxonomy' => 'product_cat',
					'field'    => 'term_id',
					'terms'    => array( $wp_query->queried_object->term_id ),
					'operator' => 'IN',
				);

			} elseif ( is_singular( 'product' ) ) {
				$terms = wc_get_product_terms( $post->ID, 'product_cat', array( 'fields' => 'ids' ) );

				if ( $terms ) {
					$do_query                  = true;
					$current_cat               = $wp_query->queried_object;
					$query_args['tax_query'][] = array(
						'taxonomy' => 'product_cat',
						'field'    => 'term_id',
						'terms'    => $terms,
						'operator' => 'IN',
					);
				}
			}
		}

		if ( 'onsale' === $show ) {
			$product_ids_on_sale = wc_get_product_ids_on_sale();
			if ( ! empty( $product_ids_on_sale ) ) {
				$query_args['post__in'] = $product_ids_on_sale;
				$do_query               = true;
			}
		}

		if ( $do_query ) {
			$product_ids = get_posts( $query_args );
		}

		$bs_query       = new VHC_WC_Bestsellers_Query();
		$bs_product_ids = $bs_query->get_product_ids(
			$range,
			array(
				'limit' => $number,
				'ids'   => $product_ids,
			)
		);

		if ( empty( $bs_product_ids ) ) {
			return false;
		}

		return new WP_Query(
			array(
				'nopaging'      => true,
				'post_status'   => 'publish',
				'post_type'     => 'product',
				'no_found_rows' => 1,
				'orderby'       => 'post__in',
				'post__in'      => $bs_product_ids,
			)
		);
	}

	/**
	 * Output widget.
	 *
	 * @param array $args     Arguments.
	 * @param array $instance Widget instance.
	 *
	 * @see WP_Widget
	 */
	public function widget( $args, $instance ) {
		if ( $this->get_cached_widget( $args ) ) {
			return;
		}

		ob_start();

		wc_set_loop_prop( 'name', 'widget' );

		$products = $this->get_products( $args, $instance );
		if ( $products && $products->have_posts() ) {
			$this->widget_start( $args, $instance );

			echo wp_kses_post( apply_filters( 'woocommerce_before_widget_product_list', '<ul class="product_list_widget">' ) );

			$template_args = array(
				'widget_id'   => isset( $args['widget_id'] ) ? $args['widget_id'] : $this->widget_id,
				'show_rating' => true,
			);

			while ( $products->have_posts() ) {
				$products->the_post();
				wc_get_template( 'content-widget-product.php', $template_args );
			}

			echo wp_kses_post( apply_filters( 'woocommerce_after_widget_product_list', '</ul>' ) );

			$this->widget_end( $args );
		}

		wp_reset_postdata();

		echo $this->cache_widget( $args, ob_get_clean() ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}
}
