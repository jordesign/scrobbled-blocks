<?php
/**
 * Helper functions for Scrobbled Blocks.
 *
 * @package ScrobbledBlocks
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Get relative time string.
 *
 * @param int|null $timestamp Unix timestamp.
 * @return string Relative time string.
 */
function scrobbled_blocks_get_relative_time( $timestamp ) {
	if ( ! $timestamp ) {
		return __( 'just now', 'scrobbled-blocks' );
	}

	$diff = time() - $timestamp;

	if ( $diff < 60 ) {
		return __( 'just now', 'scrobbled-blocks' );
	}

	if ( $diff < 3600 ) {
		$minutes = floor( $diff / 60 );
		return sprintf(
			/* translators: %d: number of minutes */
			_n( '%d minute ago', '%d minutes ago', $minutes, 'scrobbled-blocks' ),
			$minutes
		);
	}

	if ( $diff < 86400 ) {
		$hours = floor( $diff / 3600 );
		return sprintf(
			/* translators: %d: number of hours */
			_n( '%d hour ago', '%d hours ago', $hours, 'scrobbled-blocks' ),
			$hours
		);
	}

	$days = floor( $diff / 86400 );
	return sprintf(
		/* translators: %d: number of days */
		_n( '%d day ago', '%d days ago', $days, 'scrobbled-blocks' ),
		$days
	);
}
