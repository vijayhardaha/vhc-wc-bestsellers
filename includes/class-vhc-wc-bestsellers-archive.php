<?php
/**
 * VHC WooCommerce Bestsellers Archive Class.
 *
 * @package VHC_WC_Bestsellers
 */

defined( 'ABSPATH' ) || die( 'Don\'t run this file directly!' );

if ( class_exists( 'VHC_WC_Bestsellers_Archive' ) ) {
	return new VHC_WC_Bestsellers_Archive();
}

/**
 * VHC_WC_Bestsellers_Archive Class.
 */
class VHC_WC_Bestsellers_Archive {

	/**
	 * Bestsellers page id.
	 *
	 * @var int.
	 */
	public $page_id;

	/**
	 * Constructor
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		$this->page_id = absint( wc_get_page_id( 'vhc_bestsellers' ) );

		if ( ! is_admin() ) {
			add_action( 'pre_get_posts', array( $this, 'pre_get_posts' ), 1 );
			add_filter( 'template_include', array( $this, 'template_loader' ), 1 );
			add_filter( 'woocommerce_page_title', array( $this, 'page_title' ), 1 );
			add_filter( 'woocommerce_get_breadcrumb', array( $this, 'get_breadcrumb' ), 1, 2 );
			add_filter( 'document_title_parts', array( $this, 'change_page_title' ), 10 );
			add_filter( 'wp_nav_menu_objects', array( $this, 'nav_menu_item_classes' ) );
			add_action( 'woocommerce_product_query', array( $this, 'parse_query' ), 1 );
			add_filter( 'woocommerce_product_is_visible', array( $this, 'adjust_visibility' ) );
		}
	}

	/**
	 * Check if current page is bestsellers archive page.
	 *
	 * @since 1.0.0
	 * @return bool
	 */
	public function is_page() {
		global $wp_query;

		return ! is_admin() && $wp_query->is_vhcbs_archive && $this->page_id === $wp_query->queried_object_id;
	}

	/**
	 * Modify template loader.
	 *
	 * @since 1.0.0
	 * @param string $template Template path.
	 * @return string
	 */
	public function template_loader( $template ) {
		$find = array( 'woocommerce.php' );
		$file = '';

		if ( is_page( $this->page_id ) ) {
			$file   = 'archive-product.php';
			$find[] = $file;
			$find[] = WC()->template_path() . $file;
			if ( ! empty( $file ) ) {
				$template = locate_template( array_unique( $find ) );
				if ( ! $template || WC_TEMPLATE_DEBUG_MODE ) {
					$template = WC()->plugin_path() . '/templates/' . $file;
				}
			}
		}

		return $template;
	}

	/**
	 * Filter post query.
	 *
	 * @since 1.0.0
	 * @param object $q Query object.
	 */
	public function pre_get_posts( $q ) {
		// We only want to affect the main query.
		if ( ! $q->is_main_query() ) {
			return;
		}

		if ( isset( $q->queried_object_id ) && ! empty( $this->page_id ) && $q->queried_object_id === $this->page_id ) {
			$q->set( 'post_type', 'product' );
			$q->set( 'page', '' );
			$q->set( 'pagename', '' );

			if ( isset( $q->query['paged'] ) ) {
				$q->set( 'paged', $q->query['paged'] );
			}

			// Fix conditional Functions like is_front_page.
			$q->is_singular          = false;
			$q->is_post_type_archive = true;
			$q->is_archive           = true;
			$q->is_page              = true;
			$q->is_vhcbs_archive     = true;

			add_filter( 'woocommerce_is_filtered', array( $this, 'is_filtered' ), 99 ); // hack for displaying when Shop Page Display is set to show categories.

			// Remove description.
			remove_action( 'woocommerce_archive_description', 'woocommerce_product_archive_description', 10 );

			// Fix page id for description function.
			add_filter( 'woocommerce_shop_page_id_for_archive_description', array( $this, 'fix_page_id' ) );

			// Fix WP SEO.
			if ( class_exists( 'WPSEO_Meta' ) ) {
				add_filter( 'wpseo_metadesc', array( $this, 'wpseo_metadesc' ) );
				add_filter( 'wpseo_metakey', array( $this, 'wpseo_metakey' ) );
				add_filter( 'wpseo_title', array( $this, 'wpseo_title' ) );
			}
		}
	}

	/**
	 * Fix page ID for custom archive pages description.
	 *
	 * @param int $page_id Page ID.
	 */
	public function fix_page_id( $page_id ) {
		if ( $this->is_page() ) {
			$page_id = $this->page_id;
		}

		return $page_id;
	}

	/**
	 * Filter WC Page title.
	 *
	 * @since 1.0.0
	 * @param string $title Page title.
	 * @return string
	 */
	public function page_title( $title ) {
		if ( $this->is_page() ) {
			$title = apply_filters( 'vhc_bestsellers_page_title', get_the_title( $this->page_id ) );
		}

		return $title;
	}

	/**
	 * Filter WC breadcrumb.
	 *
	 * @since 1.0.0
	 * @param array $crumbs Breadcrumbs array list.
	 * @return array
	 */
	public function get_breadcrumb( $crumbs ) {
		if ( $this->is_page() ) {
			$crumbs[1] = array( get_the_title( $this->page_id ), get_permalink( $this->page_id ) );
		}
		return $crumbs;
	}

	/**
	 * Set is filtered is true to skip displaying categories only on page.
	 *
	 * @since 1.0.0
	 * @param int $id Page ID.
	 * @return bool
	 */
	public function is_filtered( $id ) {
		return true;
	}

	/**
	 * Change title for custom archive page.
	 *
	 * @since 1.0.0
	 * @param string $title Page title.
	 * @return string
	 */
	public function change_page_title( $title ) {
		if ( ! $this->is_page() ) {
			return $title;
		}

		$title['title'] = get_the_title( $this->page_id );

		return $title;
	}

	/**
	 * Fix active class in nav for auction page.
	 *
	 * @since 1.0.0
	 * @param array $menu_items Menu items array.
	 * @return array
	 */
	public function nav_menu_item_classes( $menu_items ) {
		global $wp_query;

		if ( ! $wp_query->is_vhcbs_archive ) {
			return $menu_items;
		}

		if ( ! empty( $menu_items ) && is_array( $menu_items ) ) {

			foreach ( $menu_items as $key => $menu_item ) {
				$classes = (array) $menu_item->classes;

				// Unset active class for blog page.
				$menu_items[ $key ]->current = false;

				if ( in_array( 'current_page_parent', $classes, true ) ) {
					unset( $classes[ array_search( 'current_page_parent', $classes, true ) ] );
				}

				if ( in_array( 'current-menu-item', $classes, true ) ) {
					unset( $classes[ array_search( 'current-menu-item', $classes, true ) ] );
				}

				// Set active state if this is the shop page link.
				if ( absint( $this->page_id ) === absint( $menu_item->object_id ) && 'page' === $menu_item->object ) {
					$menu_items[ $key ]->current = true;

					$classes[] = 'current-menu-item';
					$classes[] = 'current_page_item';
				}

				$menu_items[ $key ]->classes = array_unique( $classes );
			}
		}

		return $menu_items;
	}

	/**
	 * WP SEO meta description.
	 * Hooked into wpseo_ hook already, so no need for function_exist.
	 *
	 * @since 1.0.0
	 * @return string
	 */
	public function wpseo_metadesc() {
		return WPSEO_Meta::get_value( 'metadesc', $this->page_id );
	}

	/**
	 * WP SEO meta key.
	 * Hooked into wpseo_ hook already, so no need for function_exist.
	 *
	 * @since 1.0.0
	 * @return string
	 */
	public function wpseo_metakey() {
		return WPSEO_Meta::get_value( 'metakey', $this->page_id );
	}

	/**
	 * WP SEO title.
	 * Hooked into wpseo_ hook already, so no need for function_exist.
	 *
	 * @since 1.0.0
	 * @return string
	 */
	public function wpseo_title() {
		return WPSEO_Meta::get_value( 'title', $this->page_id );
	}

	/**
	 * Exclude restricted & excluded categories from release page
	 *
	 * @since 1.0.0
	 * @param object $q Query object.
	 */
	public function parse_query( $q ) {
		if ( ! is_admin() && $q->is_main_query() && $this->is_page() ) {
			$args           = apply_filters( 'vhc_wc_bestsellers_archive_query_args', array() );
			$bs_product_ids = vhc_wc_bestsellers()->get_bestsellers( $args );
			$bs_product_ids = empty( $bs_product_ids ) ? array( 0 ) : (array) $bs_product_ids;
			$post_in        = (array) $q->get( 'post__in' );
			$post_in        = array_merge( $post_in, $bs_product_ids );

			$q->set( 'post__in', $post_in );
			$q->set( 'orderby', 'post__in' );

			$tax_query = $q->get( 'tax_query' );
			if ( ! empty( $tax_query ) ) {
				foreach ( $tax_query as $key => $value ) {
					if ( is_array( $value ) && 'product_visibility' === $value['taxonomy'] && 'NOT IN' === $value['operator'] ) {
						unset( $tax_query[ $key ] );
					}
				}
			}

			$product_visibility_terms  = wc_get_product_visibility_term_ids();
			$product_visibility_not_in = array();

			if ( 'yes' !== get_option( 'woocommerce_vhc_bestsellers_show_hidden', 'no' ) ) {
				$product_visibility_not_in[] = $product_visibility_terms['exclude-from-catalog'];
			}

			if ( 'yes' === get_option( 'woocommerce_vhc_bestsellers_hide_out_of_stock', 'no' ) ) {
				$product_visibility_not_in[] = $product_visibility_terms['outofstock'];
			}

			if ( ! empty( $product_visibility_not_in ) ) {
				$tax_query[] = array(
					'taxonomy' => 'product_visibility',
					'field'    => 'term_taxonomy_id',
					'terms'    => $product_visibility_not_in,
					'operator' => 'NOT IN',
				);
			}

			$q->set( 'tax_query', $tax_query );
		}
	}

	/**
	 * Adjust visibility if show hidden it true.
	 *
	 * @since 1.0.0
	 * @param bool $visibility Visibility status.
	 * @return bool
	 */
	public function adjust_visibility( $visibility ) {
		if ( $this->is_page() && 'yes' === get_option( 'woocommerce_vhc_bestsellers_show_hidden', 'no' ) ) {
			return true;
		}

		return $visibility;
	}
}

return new VHC_WC_Bestsellers_Archive();
