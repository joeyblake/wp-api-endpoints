<?php
/**
 * Class Base_API
 *
 * Sets up scaffold for creating ajax endpoints, allows cacheable GET requests.
 */
abstract class Base_API {

	/**
	 * Rewrite url endpoint for api to answer to, can be overridden by implementation class.
	 *
	 * @protected
	 * @static
	 *
	 * @var string
	 */
	protected static $rewrite_endpoint = 'api';

	/**
	 * Method names that follow $rewrite_endpoint ex. /api/$front_endpoint
	 * for unauthenticated use.
	 *
	 * @protected
	 * @static
	 *
	 * @var array
	 */
	protected static $front_endpoints = array();

	/**
	 * Method names that follow $rewrite_endpoint ex. /api/$admin_endpoints
	 * for authenticated use.
	 *
	 * @protected
	 * @static
	 *
	 * @var array
	 */
	protected static $admin_endpoints = array();

	/**
	 * Allowed origins for cross domain api access.
	 *
	 * @protected
	 *
	 * @var array
	 */
	protected $allowed_origins = array();

	/**
	 * Base_API constructor.
	 */
	public function __construct() {

		add_action( 'init', array( $this, 'init' ) );
		add_action( 'template_redirect', array( $this, 'api_endpoint_template_redirect' ) );

	}

	/**
	 * Sets up rewrite endpoint
	 */
	public function init() {

		// Add API Endpoint
		add_rewrite_endpoint( static::$rewrite_endpoint, EP_ROOT );

	}

	/**
	 * Helper function for checking request type
	 *
	 * @return string
	 */
	public function request_type() {

		return $_SERVER['REQUEST_METHOD'];

	}

	/**
	 * Helper function for checking authentication status
	 *
	 * @return array
	 */
	public function is_user_admin() {

		$is_admin = false;

		if ( is_user_logged_in() ) {
			$user = wp_get_current_user();

			$allowed_roles = array(
				'editor',
				'administrator',
				'author'
			);

			/**
			 * Get the allowed roles for admin endpoint access.
			 *
			 * @param array $allowed_roles Allowed roles for admin access
			 */
			$allowed_roles = apply_filters( 'base_api_allowed_roles', $allowed_roles );

			if ( is_super_admin( $user->ID ) ) {
				$is_admin = true;
			} else {
				$intersection = array_intersect( $allowed_roles, $user->roles );

				if ( $intersection ) {
					$is_admin = true;
				}
			}
		}

		return $is_admin;

	}

	/**
	 * Handles template redirect requests. Checks if endpoint is valid and if
	 * user should be authenticated, routes request to proper handler function
	 */
	public function api_endpoint_template_redirect() {

		/**
		 * @var $wp_query \WP_Query
		 */
		global $wp_query;

		if ( empty( $wp_query->query_vars[ static::$rewrite_endpoint ] ) ) {
			return;
		}

		// Allows use of DOING_AJAX content just like admin-ajax requests
		if ( ! defined( 'DOING_AJAX' ) ) {
			define( 'DOING_AJAX', true );
		}

		$api = explode( '/', $wp_query->query_vars[ static::$rewrite_endpoint ] );

		$endpoint = array_shift( $api );

		$endpoint_method = str_replace( '-', '_', $endpoint );

		$is_front_endpoint = false;
		$is_admin_endpoint = false;

		if ( in_array( $endpoint, static::$front_endpoints ) ) {
			$is_front_endpoint = true;
		}

		if ( in_array( $endpoint, static::$admin_endpoints ) ) {
			$is_admin_endpoint = true;
		}

		if ( ( ! $is_front_endpoint && ! $is_admin_endpoint ) || ! method_exists( $this, $endpoint_method ) ) {
			wp_send_json_error( __( 'This endpoint does not exist.', 'base-api' ) );
		}

		if ( $is_admin_endpoint && ! $this->is_user_admin() ) {
			wp_send_json_error( __( 'This is an admin-only endpoint.', 'base-api' ) );
		}

		$data = call_user_func_array( array( $this, $endpoint_method ), $api );

		// Check for WP_Error response
		if ( is_wp_error( $data ) ) {
			/**
			 * @var $data \WP_Error
			 */
			wp_send_json_error( $data->get_error_message() );
		}

		status_header( 200 );

		$http_origin = $_SERVER['HTTP_ORIGIN'];

		if ( in_array( $http_origin, $this->allowed_origins, true ) ) {
			header( 'Access-Control-Allow-Credentials: true' );
			header( 'Access-Control-Allow-Origin: ' . $http_origin );
		}

		header( 'Content-type: application/json' );

		wp_send_json_success( $data );

	}

	/**
	 * With "/" delimited params there are times when defaults are needed in the url, but they are not needed
	 * once there are keys mapped to the values. This function combined with array_walk dumps default keys
	 * and values.
	 *
	 * @param string $item
	 * @param string $key
	 */
	public function empty_defaults( &$item, $key ) {

		if ( 'default' === $item ) {
			$item = null;
		}

	}

}
