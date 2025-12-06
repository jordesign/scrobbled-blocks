<?php
/**
 * Plugin Name:       Scrobbled Blocks
 * Plugin URI:        https://github.com/jordesign/scrobbled-blocks
 * Description:       Display your Last.fm listening activity on your WordPress site with native Gutenberg blocks. Show what you're currently playing or your recent listening history.
 * Version:           1.0.0
 * Requires at least: 6.0
 * Tested up to:      6.9
 * Requires PHP:      7.4
 * Author:            jordesign
 * Author URI:        https://jordangillman.blog
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       scrobbled-blocks
 * Domain Path:       /languages
 *
 * @package ScrobbledBlocks
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Plugin constants.
define( 'SCROBBLED_BLOCKS_VERSION', '1.0.0' );
define( 'SCROBBLED_BLOCKS_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'SCROBBLED_BLOCKS_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'SCROBBLED_BLOCKS_PLUGIN_FILE', __FILE__ );
define( 'SCROBBLED_BLOCKS_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );

/**
 * Plugin activation hook.
 *
 * @return void
 */
function scrobbled_blocks_activate() {
	// Clear any stale transients.
	global $wpdb;
	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Bulk transient cleanup requires direct query.
	$wpdb->query(
		$wpdb->prepare(
			"DELETE FROM {$wpdb->options} WHERE option_name LIKE %s OR option_name LIKE %s",
			'_transient_scrobbled_blocks_%',
			'_transient_timeout_scrobbled_blocks_%'
		)
	);
}
register_activation_hook( __FILE__, 'scrobbled_blocks_activate' );

/**
 * Plugin deactivation hook.
 *
 * @return void
 */
function scrobbled_blocks_deactivate() {
	// Clear transients on deactivation.
	global $wpdb;
	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Bulk transient cleanup requires direct query.
	$wpdb->query(
		$wpdb->prepare(
			"DELETE FROM {$wpdb->options} WHERE option_name LIKE %s OR option_name LIKE %s",
			'_transient_scrobbled_blocks_%',
			'_transient_timeout_scrobbled_blocks_%'
		)
	);
}
register_deactivation_hook( __FILE__, 'scrobbled_blocks_deactivate' );

/**
 * Main plugin class.
 */
final class Scrobbled_Blocks {

	/**
	 * Single instance of the class.
	 *
	 * @var Scrobbled_Blocks|null
	 */
	private static $instance = null;

	/**
	 * Get single instance of the class.
	 *
	 * @return Scrobbled_Blocks
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
		$this->load_dependencies();
		$this->init_hooks();
	}

	/**
	 * Load required files.
	 */
	private function load_dependencies() {
		require_once SCROBBLED_BLOCKS_PLUGIN_DIR . 'includes/functions.php';
		require_once SCROBBLED_BLOCKS_PLUGIN_DIR . 'includes/class-api.php';
		require_once SCROBBLED_BLOCKS_PLUGIN_DIR . 'includes/class-settings.php';
		require_once SCROBBLED_BLOCKS_PLUGIN_DIR . 'includes/class-rest-api.php';
	}

	/**
	 * Initialize hooks.
	 */
	private function init_hooks() {
		add_action( 'init', array( $this, 'register_blocks' ) );
		add_action( 'enqueue_block_assets', array( $this, 'enqueue_block_assets' ) );
	}

	/**
	 * Register blocks.
	 */
	public function register_blocks() {
		// Register Now Playing block.
		register_block_type( SCROBBLED_BLOCKS_PLUGIN_DIR . 'build/now-playing' );

		// Register Recently Played block.
		register_block_type( SCROBBLED_BLOCKS_PLUGIN_DIR . 'build/recently-played' );
	}

	/**
	 * Enqueue block assets.
	 */
	public function enqueue_block_assets() {
		wp_enqueue_style(
			'scrobbled-blocks-style',
			SCROBBLED_BLOCKS_PLUGIN_URL . 'assets/css/blocks.css',
			array(),
			SCROBBLED_BLOCKS_VERSION
		);
	}

	/**
	 * Get the API instance.
	 *
	 * @return Scrobbled_Blocks_API
	 */
	public function get_api() {
		return Scrobbled_Blocks_API::get_instance();
	}
}

/**
 * Initialize the plugin.
 *
 * @return Scrobbled_Blocks
 */
function scrobbled_blocks() {
	return Scrobbled_Blocks::get_instance();
}

// Initialize plugin.
scrobbled_blocks();
