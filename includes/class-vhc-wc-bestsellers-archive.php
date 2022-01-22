<?php
/**
 * VHC WooCommerce Bestsellers Archive
 *
 * @class VHC_WC_Bestsellers_Archive
 * @package VHC_WC_Bestsellers
 * @subpackage VHC_WC_Bestsellers/Archive
 * @version 1.0.0
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * VHC_WC_Bestsellers_Archive Class.
 */
class VHC_WC_Bestsellers_Archive {
	/**
	 * Constructor
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		add_action( 'pre_get_posts', array( $this, 'pre_get_posts' ), 1 );
		add_filter( 'template_include', array( $this, 'template_loader' ), 1 );
		add_filter( 'woocommerce_page_title', array( $this, 'page_title' ), 1 );
		add_filter( 'woocommerce_get_breadcrumb', array( $this, 'get_breadcrumb' ), 1, 2 );
		add_filter( 'document_title_parts', array( $this, 'change_page_title' ), 10 );
		add_filter( 'wp_nav_menu_objects', array( $this, 'nav_menu_item_classes' ), 10 );
		add_filter( 'icl_ls_languages', array( $this, 'translate_url' ), 99 );
		add_action( 'woocommerce_product_query', array( $this, 'parse_query' ) );
	}

	/**
	 * Modify template loader
	 *
	 * @param string $template template path.
	 * @since 1.0.0
	 * @return string
	 */
	public function template_loader( $template ) {
		$find    = array( 'woocommerce.php' );
		$file    = '';
		$page_id = $this->get_main_wpml_id( wc_get_page_id( 'vhc_bestsellers' ) );

		if ( is_page( $page_id ) ) {
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
	 * Filter post query
	 *
	 * @param object $q query object.
	 * @since 1.0.0
	 */
	public function pre_get_posts( $q ) {
		if ( ! $q->query ) {
			return;
		}

		$page_id = $this->get_main_wpml_id( wc_get_page_id( 'vhc_bestsellers' ) );

		if ( is_page( $page_id ) ) {
			$q->set( 'post_type', 'product' );
			$q->set( 'page', '' );
			$q->set( 'pagename', '' );

			// Fix conditional Functions.
			$q->is_archive                 = true;
			$q->is_post_type_archive       = true;
			$q->is_singular                = false;
			$q->is_page                    = false;
			$q->is_vhc_bestsellers_archive = true;
			add_filter( 'woocommerce_is_filtered', array( $this, 'add_is_filtered' ), 99 ); // hack for displaying when Shop Page Display is set to show categories.

			// Fix WP SEO.
			if ( class_exists( 'WPSEO_Meta' ) ) {
				add_filter( 'wpseo_metadesc', array( $this, 'wpseo_metadesc' ) );
				add_filter( 'wpseo_metakey', array( $this, 'wpseo_metakey' ) );
				add_filter( 'wpseo_title', array( $this, 'wpseo_title' ) );
			}
		}
	}

	/**
	 * Filter WC Page title
	 *
	 * @since 1.0.0
	 * @param string $title page title.
	 * @return string
	 */
	public function page_title( $title ) {
		global $wp_query;

		if ( $wp_query->is_vhc_bestsellers_archive ) {
			$page_id = $this->get_main_wpml_id( $wp_query->queried_object_id );
			$title   = get_the_title( $page_id );
		}

		return $title;
	}

	/**
	 * Filter WC breadcrumb
	 *
	 * @param array $crumbs breadcrumbs array list.
	 * @return array
	 */
	public function get_breadcrumb( $crumbs ) {
		global $wp_query;
		if ( $wp_query->is_vhc_bestsellers_archive ) {
			$page_id   = $this->get_main_wpml_id( $wp_query->queried_object_id );
			$crumbs[1] = array( get_the_title( $page_id ), get_permalink( $page_id ) );
		}
		return $crumbs;
	}

	/**
	 * Get main product id for multilanguage purpose
	 *
	 * @since 1.0.0
	 * @param int $id page id.
	 * @return int
	 */
	public static function get_main_wpml_id( $id ) {
		global $sitepress;
		if ( function_exists( 'icl_object_id' ) ) { // Polylang with use of WPML compatibility mode.
			$id = icl_object_id( $id, 'page', false );
		}
		return $id;
	}

	/**
	 * Set is filtered is true to skip displaying categories only on page.
	 *
	 * @param int $id page id.
	 * @since 1.0.0
	 * @return bool
	 */
	public function add_is_filtered( $id ) {
		return true;
	}

	/**
	 * Change title for custom archive page.
	 *
	 * @param string $title page title.
	 * @since 1.0.0
	 * @return string
	 */
	public function change_page_title( $title ) {
		global $wp_query;

		if ( ! is_woocommerce() || ! $wp_query->is_vhc_bestsellers_archive ) {
			return $title;
		}

		$title['title'] = get_the_title( $this->get_main_wpml_id( $wp_query->queried_object_id ) );

		return $title;
	}

	/**
	 * Fix active class in nav for auction page.
	 *
	 * @param array $menu_items menu items array.
	 * @since 1.0.0
	 * @return array
	 */
	public function nav_menu_item_classes( $menu_items ) {
		global $wp_query;

		if ( ! is_woocommerce() || ! $wp_query->is_vhc_bestsellers_archive ) {
			return $menu_items;
		}

		$page_id = $this->get_main_wpml_id( $wp_query->queried_object_id );

		foreach ( (array) $menu_items as $key => $menu_item ) {
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
			if ( $page_id === $menu_item->object_id && 'page' === $menu_item->object ) {
				$menu_items[ $key ]->current = true;

				$classes[] = 'current-menu-item';
				$classes[] = 'current_page_item';
			}

			$menu_items[ $key ]->classes = array_unique( $classes );
		}
		return $menu_items;
	}

	/**
	 * Translate custom archive page url.
	 *
	 * @param array $languages languages array.
	 * @param bool  $debug_mode enable/disable debug mode.
	 * @since 1.0.0
	 * @return array
	 */
	public function translate_url( $languages, $debug_mode = false ) {
		global $sitepress, $wp_query;

		$page_id = (int) $wp_query->queried_object_id;

		foreach ( $languages as $language ) {
			// shop page.
			// obsolete?
			if ( $wp_query->is_vhc_bestsellers_archive || $debug_mode ) {
				$sitepress->switch_lang( $language['language_code'] );
				$url = get_permalink( apply_filters( 'translate_object_id', $page_id, 'page', true, $language['language_code'] ) );
				$sitepress->switch_lang();
				$languages[ $language['language_code'] ]['url'] = $url;
			}
		}
		return $languages;
	}

	/**
	 * WP SEO meta description.
	 * Hooked into wpseo_ hook already, so no need for function_exist.
	 *
	 * @since 1.0.0
	 * @return string
	 */
	public function wpseo_metadesc() {
		global $wp_query;
		$page_id = (int) $wp_query->queried_object_id;
		return WPSEO_Meta::get_value( 'metadesc', $page_id );
	}

	/**
	 * WP SEO meta key.
	 * Hooked into wpseo_ hook already, so no need for function_exist.
	 *
	 * @since 1.0.0
	 * @return string
	 */
	public function wpseo_metakey() {
		global $wp_query;
		$page_id = (int) $wp_query->queried_object_id;
		return WPSEO_Meta::get_value( 'metakey', $page_id );
	}

	/**
	 * WP SEO title.
	 * Hooked into wpseo_ hook already, so no need for function_exist.
	 *
	 * @since 1.0.0
	 * @return string
	 */
	public function wpseo_title() {
		global $wp_query;
		$page_id = (int) $wp_query->queried_object_id;
		return WPSEO_Meta::get_value( 'title', $page_id );
	}

	/**
	 * Exclude restricted & excluded categories from release page
	 *
	 * @since 1.0.0
	 * @param object $q Query object.
	 */
	public function parse_query( $q ) {
		global $wp_query;

		if ( ! is_admin() && vhc_wc_bestsellers()->is_archive() && $q->is_main_query() ) {
			$args           = apply_filters( 'vhc_wc_bestsellers_archive_query_args', array() );
			$bs_product_ids = vhc_wc_bestsellers()->get_bestsellers( $args );
			$bs_product_ids = empty( $bs_product_ids ) ? array( 0 ) : (array) $bs_product_ids;
			$post_in        = (array) $q->get( 'post__in' );
			$post_in        = array_merge( $post_in, $bs_product_ids );
			$q->set( 'post__in', $post_in );
			$q->set( 'orderby', 'post__in' );
		}
	}
}

new VHC_WC_Bestsellers_Archive();
