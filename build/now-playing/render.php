<?php
/**
 * Server-side rendering for the Now Playing block.
 *
 * @package ScrobbledBlocks
 *
 * @var array    $attributes Block attributes.
 * @var string   $content    Block content.
 * @var WP_Block $block      Block instance.
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$settings = Scrobbled_Blocks_Settings::get_instance();

// Check if plugin is configured.
if ( ! $settings->is_configured() ) {
	// Render nothing on frontend when not configured.
	return;
}

$api   = Scrobbled_Blocks_API::get_instance();
$track = $api->get_now_playing();

// Graceful degradation - render nothing if API fails or no tracks.
if ( is_wp_error( $track ) || empty( $track ) ) {
	return;
}

$show_artwork   = $attributes['showArtwork'] ?? true;
$artwork_size   = $attributes['artworkSize'] ?? 64;
$show_timestamp = $attributes['showTimestamp'] ?? true;
$link_to_lastfm = $attributes['linkToLastFm'] ?? true;

/**
 * Get relative time string.
 *
 * @param int|null $timestamp Unix timestamp.
 * @return string Relative time string.
 */
if ( ! function_exists( 'scrobbled_blocks_get_relative_time' ) ) {
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
}

$wrapper_attributes = get_block_wrapper_attributes( array(
	'class' => 'wp-block-scrobble-blocks-now-playing',
	'style' => '--scrobble-artwork-size: ' . absint( $artwork_size ) . 'px;',
) );

$track_name   = esc_html( $track['name'] );
$artist_name  = esc_html( $track['artist'] );
$album_name   = esc_html( $track['album'] );
$artwork_url  = esc_url( $track['artwork'] );
$track_url    = esc_url( $track['url'] );
$is_playing   = ! empty( $track['nowplaying'] );
$timestamp    = $track['timestamp'] ?? null;

// Generate ISO timestamp for datetime attribute.
$iso_timestamp   = $timestamp ? gmdate( 'c', $timestamp ) : '';
$absolute_time   = $timestamp ? wp_date( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), $timestamp ) : '';
$relative_time   = $is_playing ? __( 'Playing now', 'scrobbled-blocks' ) : scrobbled_blocks_get_relative_time( $timestamp );
?>
<div <?php echo $wrapper_attributes; ?>>
	<?php if ( $show_artwork ) : ?>
		<div class="scrobble-artwork">
			<?php if ( $link_to_lastfm && $track_url ) : ?>
				<a href="<?php echo $track_url; ?>" target="_blank" rel="noopener noreferrer">
					<img src="<?php echo $artwork_url; ?>" alt="<?php echo esc_attr( sprintf( '%s by %s', $album_name, $artist_name ) ); ?>" loading="lazy" />
				</a>
			<?php else : ?>
				<img src="<?php echo $artwork_url; ?>" alt="<?php echo esc_attr( sprintf( '%s by %s', $album_name, $artist_name ) ); ?>" loading="lazy" />
			<?php endif; ?>
		</div>
	<?php endif; ?>
	<div class="scrobble-info">
		<span class="scrobble-track">
			<?php if ( $link_to_lastfm && $track_url ) : ?>
				<a href="<?php echo $track_url; ?>" target="_blank" rel="noopener noreferrer"><?php echo $track_name; ?></a>
			<?php else : ?>
				<?php echo $track_name; ?>
			<?php endif; ?>
		</span>
		<span class="scrobble-artist"><?php echo $artist_name; ?></span>
		<?php if ( $show_timestamp ) : ?>
			<time class="scrobble-timestamp" datetime="<?php echo esc_attr( $iso_timestamp ); ?>" title="<?php echo esc_attr( $absolute_time ); ?>">
				<?php echo esc_html( $relative_time ); ?>
			</time>
		<?php endif; ?>
	</div>
</div>
