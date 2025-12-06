<?php
/**
 * Uninstall Scrobbled Blocks
 *
 * Removes all plugin data when the plugin is uninstalled.
 *
 * @package ScrobbledBlocks
 */

// Exit if not called by WordPress uninstall.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

// Delete plugin options.
delete_option( 'scrobbled_blocks_username' );
delete_option( 'scrobbled_blocks_api_key' );
delete_option( 'scrobbled_blocks_placeholder_id' );

// Delete all transients created by the plugin.
global $wpdb;
$wpdb->query(
	$wpdb->prepare(
		"DELETE FROM {$wpdb->options} WHERE option_name LIKE %s OR option_name LIKE %s",
		'_transient_scrobbled_blocks_%',
		'_transient_timeout_scrobbled_blocks_%'
	)
);
