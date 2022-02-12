<?php
/**
 * VHC WooCommerce Bestsellers Updater.
 *
 * @package VHC_WC_Bestsellers
 * @subpackage VHC_WC_Bestsellers\Classes\Updater
 * @since 1.0.3
 * @version 1.0.3
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

if ( class_exists( 'VHC_WC_Bestsellers_Updater' ) ) {
	return new VHC_WC_Bestsellers_Updater();
}

/**
 * VHC_WC_Bestsellers_Updater class.
 */
class VHC_WC_Bestsellers_Updater {

	/**
	 * Constructor.
	 *
	 * @since 1.0.3
	 */
	public function __construct() {
		add_filter( 'pre_set_site_transient_update_plugins', array( $this, 'check_update' ), 10, 1 );
		add_filter( 'plugins_api', array( $this, 'check_info' ), 20, 3 );
	}

	/**
	 * Get plugin data.
	 *
	 * @since 1.0.3
	 * @return array
	 */
	private function get_plugin_data() {
		return get_plugin_data( VHC_WC_BESTSELLERS_PLUGIN_FILE );
	}

	/**
	 * Get plugin name.
	 *
	 * @since 1.0.3
	 * @return string
	 */
	private function get_plugin_name() {
		return VHC_WC_BESTSELLERS_PLUGIN_NAME;
	}

	/**
	 * Get plugin current version.
	 *
	 * @since 1.0.3
	 * @return string
	 */
	private function get_current_version() {
		return VHC_WC_BESTSELLERS_VERSION;
	}

	/**
	 * Get plugin base name.
	 *
	 * @since 1.0.3
	 * @return string
	 */
	private function get_plugin_base() {
		return VHC_WC_BESTSELLERS_PLUGIN_BASENAME;
	}

	/**
	 * Get plugin slug.
	 *
	 * @since 1.0.3
	 * @return string
	 */
	private function get_plugin_slug() {
		$slug_args = explode( '/', $this->get_plugin_base() );
		return str_replace( '.php', '', $slug_args[1] );
	}

	/**
	 * Get remote url for update check.
	 *
	 * @since 1.0.3
	 * @return string
	 */
	private function get_remote_url() {
		return sprintf( 'https://api.github.com/repos/%s/%s/releases', 'vijayhardaha', $this->get_plugin_slug() );
	}

	/**
	 * Get auth token for github.
	 *
	 * @return string|bool
	 */
	private function get_auth_token() {
		return false;
	}

	/**
	 * Build download url of plugin zip.
	 *
	 * @since 1.0.3
	 *
	 * @param string $tag Tag name.
	 *
	 * @return string
	 */
	private function build_download_url( $tag ) {
		return sprintf( 'https://github.com/%1$s/%2$s/releases/download/%3$s/%2$s.zip', 'vijayhardaha', $this->get_plugin_slug(), $tag );
	}

	/**
	 * Sanitize tag name.
	 * Removes letter "v" from start.
	 *
	 * @since 1.0.3
	 *
	 * @param string $tag Github tag name.
	 *
	 * @return string
	 */
	private function sanitize_tag( $tag ) {
		return ! empty( $tag ) && 'v' === strtolower( $tag[0] ) ? ltrim( $tag, strtolower( $tag[0] ) ) : $tag;
	}

	/**
	 * Get remote information.
	 *
	 * @since 1.0.3
	 * @return bool|object
	 */
	private function get_remote_information() {
		$filter_add = true;

		if ( function_exists( 'curl_version' ) ) {
			$version = curl_version();
			if ( version_compare( $version['version'], '7.18', '>=' ) ) {
				$filter_add = false;
			}
		}

		if ( $filter_add ) {
			add_filter( 'https_ssl_verify', '__return_false' );
		}

		$remote_args = array( 'timeout' => 30 );
		if ( ! empty( $this->get_auth_token() ) ) {
			$remote_args['headers']['Authorization'] = "token {$this->get_auth_token()}";
		}

		$request = wp_remote_get( $this->get_remote_url(), $remote_args );
		if ( $filter_add ) {
			remove_filter( 'https_ssl_verify', '__return_false' );
		}

		if ( is_wp_error( $request ) || 200 !== wp_remote_retrieve_response_code( $request ) || empty( wp_remote_retrieve_body( $request ) ) ) {
			return false;
		}

		$response = json_decode( wp_remote_retrieve_body( $request ), true );
		if ( is_array( $response ) ) {
			$response = current( $response );
			return $response;
		}

		return false;
	}

	/**
	 * Get remote current version.
	 *
	 * @since 1.0.3
	 * @return string|bool
	 */
	private function get_remote_version() {
		$remote = $this->get_remote_information();
		if ( empty( $remote ) ) {
			return false;
		}

		return $remote['tag_name'];
	}

	/**
	 * Add our self-hosted description to the filter.
	 *
	 * @since 1.0.3
	 *
	 * @param bool   $res       Plugin api response.
	 * @param array  $action    Action name.
	 * @param object $args       Plugin api args.
	 *
	 * @return bool|object
	 */
	public function check_info( $res, $action, $args ) {
		// Do nothing if this is not about getting plugin information.
		if ( 'plugin_information' !== $action ) {
			return false;
		}

		// Do nothing if it is not our plugin.
		if ( $this->get_plugin_slug() !== $args->slug ) {
			return false;
		}

		$remote = $this->get_remote_information();
		// Do nothing if remote information is empty.
		if ( empty( $remote ) ) {
			return false;
		}

		$plugin_data = $this->get_plugin_data();

		$res                    = new stdClass();
		$res->slug              = $this->get_plugin_base();
		$res->version           = $this->sanitize_tag( $remote['tag_name'] );
		$res->download_link     = $this->build_download_url( $remote['tag_name'] );
		$res->name              = $plugin_data['Name'];
		$res->added             = $remote['created_at'];
		$res->last_updated      = $remote['published_at'];
		$res->tested            = $plugin_data['RequiresWP'];
		$res->requires          = $plugin_data['RequiresWP'];
		$res->requires_php      = $plugin_data['RequiresPHP'];
		$res->author            = $plugin_data['AuthorName'];
		$res->author_profile    = $plugin_data['AuthorURI'];
		$res->homepage          = $plugin_data['PluginURI'];
		$res->short_description = $plugin_data['Description'];
		$res->sections          = array(
			'Description' => $plugin_data['Description'],
			'Updates'     => $remote['body'],
		);

		return $res;
	}

	/**
	 * Add our self-hosted autoupdate plugin to the filter transient.
	 *
	 * @since 1.0.3
	 *
	 * @param object $transient Transient object.
	 *
	 * @return object
	 */
	public function check_update( $transient ) {
		// Extra check for 3rd plugins.
		if ( isset( $transient->response[ $this->get_plugin_base() ] ) ) {
			return $transient;
		}

		// Get the remote version.
		$remote_version    = $this->get_remote_version();
		$sanitized_version = $this->sanitize_tag( $remote_version );

		// If a newer version is available, add the update.
		if ( ! empty( $remote_version ) && version_compare( $this->get_current_version(), $sanitized_version, '<' ) ) {
			$obj                                 = new stdClass();
			$obj->name                           = $this->get_plugin_name();
			$obj->slug                           = $this->get_plugin_slug();
			$obj->plugin                         = $this->get_plugin_base();
			$obj->new_version                    = $sanitized_version;
			$obj->package                        = $this->build_download_url( $remote_version );
			$transient->response[ $obj->plugin ] = $obj;
		}

		return $transient;
	}
}

return new VHC_WC_Bestsellers_Updater();
