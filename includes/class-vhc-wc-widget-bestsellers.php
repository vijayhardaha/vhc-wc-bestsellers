<?php
/**
 * Bestsellers products list widget.
 *
 * @package VHC_WC_Bestsellers
 */

defined( 'ABSPATH' ) || exit; // Exit if accessed directly.

/**
 * Widget bestsellers products.
 */
class VHC_WC_Widget_Bestsellers extends WC_Widget {

	/**
	 * Constructor.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		$this->widget_id          = 'vhc_wc_bestsellers';
		$this->widget_cssclass    = 'woocommerce widget_products vhc-wc-bs-products';
		$this->widget_name        = __( 'VHC Bestsellers List', 'vhc-wc-bestsellers' );
		$this->widget_description = __( 'Displays bestsellers products list.', 'vhc-wc-bestsellers' );
		$this->settings           = array(
			'title'             => array(
				'type'  => 'text',
				'std'   => __( 'Best Sellers', 'vhc-wc-bestsellers' ),
				'label' => __( 'Title', 'vhc-wc-bestsellers' ),
			),
			'number'            => array(
				'type'  => 'number',
				'step'  => 1,
				'min'   => 1,
				'max'   => 50,
				'std'   => 10,
				'label' => __( 'Number of products to show', 'vhc-wc-bestsellers' ),
			),
			'period'            => array(
				'type'    => 'select',
				'std'     => 'last-7-days',
				'label'   => __( 'Sales Period', 'vhc-wc-bestsellers' ),
				'options' => VHC_WC_Bestsellers_Admin::sales_periods(),
			),
			'show'              => array(
				'type'    => 'select',
				'std'     => '',
				'label'   => __( 'Show', 'vhc-wc-bestsellers' ),
				'options' => array(
					''         => __( 'All products', 'vhc-wc-bestsellers' ),
					'featured' => __( 'Featured products', 'vhc-wc-bestsellers' ),
					'onsale'   => __( 'On-sale products', 'vhc-wc-bestsellers' ),
				),
			),
			'hide_free'         => array(
				'type'  => 'checkbox',
				'std'   => 0,
				'label' => __( 'Hide free products', 'vhc-wc-bestsellers' ),
			),
			'show_hidden'       => array(
				'type'  => 'checkbox',
				'std'   => 0,
				'label' => __( 'Show hidden products', 'vhc-wc-bestsellers' ),
			),
			'hide_out_of_stock' => array(
				'type'  => 'checkbox',
				'std'   => 0,
				'label' => __( 'Hide out of stock products', 'vhc-wc-bestsellers' ),
			),
		);

		parent::__construct();
	}

	/**
	 * Query the products and return them.
	 *
	 * @since 1.0.0
	 * @param array $args       Arguments.
	 * @param array $instance   Widget instance.
	 * @return WP_Query
	 */
	public function get_products( $args, $instance ) {
		$number = ! empty( $instance['number'] ) ? absint( $instance['number'] ) : $this->settings['number']['std'];
		$period = ! empty( $instance['period'] ) ? sanitize_title( $instance['period'] ) : $this->settings['period']['std'];
		$show   = ! empty( $instance['show'] ) ? sanitize_title( $instance['show'] ) : $this->settings['show']['std'];

		$query_args = array(
			'period'            => $period,
			'limit'             => $number,
			'hide_free'         => ! empty( $instance['hide_free'] ),
			'show_hidden'       => ! empty( $instance['show_hidden'] ),
			'hide_out_of_stock' => ! empty( $instance['hide_out_of_stock'] ),
			'show'              => $show,
			'return'            => 'query_objects',
		);

		$query_args = apply_filters( 'vhc_wc_bestsellers_widget_query_args', $query_args, $args, $instance );

		return vhc_wc_bestsellers()->get_bestsellers( $query_args );
	}

	/**
	 * Output widget.
	 *
	 * @since 1.0.0
	 * @param array $args       Arguments.
	 * @param array $instance   Widget instance.
	 * @see WP_Widget
	 */
	public function widget( $args, $instance ) {
		// Disable widget conditionally on some pages.
		$disabled = apply_filters( 'vhc_wc_bestsellers_widget_disabled', false );
		if ( $disabled ) {
			return;
		}

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
	}
}
