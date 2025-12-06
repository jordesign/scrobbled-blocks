<?php
/**
 * REST API endpoints for Scrobbled Blocks.
 *
 * @package ScrobbledBlocks
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * REST API class.
 */
class Scrobbled_Blocks_REST_API {

	/**
	 * REST namespace.
	 */
	const REST_NAMESPACE = 'scrobble-blocks/v1';

	/**
	 * Single instance of the class.
	 *
	 * @var Scrobbled_Blocks_REST_API|null
	 */
	private static $instance = null;

	/**
	 * Get single instance of the class.
	 *
	 * @return Scrobbled_Blocks_REST_API
	 */
	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Constructor.
	 */
	private function __construct() {
		add_action( 'rest_api_init', array( $this, 'register_routes' ) );
	}

	/**
	 * Register REST routes.
	 */
	public function register_routes() {
		register_rest_route(
			self::REST_NAMESPACE,
			'/recent-tracks',
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => array( $this, 'get_recent_tracks' ),
				'permission_callback' => array( $this, 'check_permission' ),
				'args'                => array(
					'limit' => array(
						'type'              => 'integer',
						'default'           => 5,
						'minimum'           => 1,
						'maximum'           => 20,
						'sanitize_callback' => 'absint',
					),
				),
			)
		);
	}

	/**
	 * Check if user has permission to access the endpoint.
	 *
	 * @return bool True if user has permission.
	 */
	public function check_permission() {
		return current_user_can( 'edit_posts' );
	}

	/**
	 * Get recent tracks.
	 *
	 * @param WP_REST_Request $request Request object.
	 * @return WP_REST_Response Response object.
	 */
	public function get_recent_tracks( $request ) {
		$settings = Scrobbled_Blocks_Settings::get_instance();

		if ( ! $settings->is_configured() ) {
			return new WP_REST_Response(
				array(
					'success' => false,
					'error'   => __( 'API key not configured', 'scrobbled-blocks' ),
				),
				200
			);
		}

		$limit = $request->get_param( 'limit' );
		$api   = Scrobbled_Blocks_API::get_instance();

		// Use 5 minute cache for recently played.
		$tracks = $api->get_recent_tracks( $limit, 5 );

		if ( is_wp_error( $tracks ) ) {
			return new WP_REST_Response(
				array(
					'success' => false,
					'error'   => $tracks->get_error_message(),
				),
				200
			);
		}

		return new WP_REST_Response(
			array(
				'success' => true,
				'tracks'  => $tracks,
			),
			200
		);
	}
}

// Initialize REST API.
Scrobbled_Blocks_REST_API::get_instance();
