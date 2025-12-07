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

// Wrap in IIFE to avoid global variable pollution.
$scrobbled_blocks_output = ( static function ( $scrobbled_blocks_attributes ) {
	$scrobbled_settings = Scrobbled_Blocks_Settings::get_instance();

	// Check if plugin is configured.
	if ( ! $scrobbled_settings->is_configured() ) {
		return '';
	}

	$scrobbled_api   = Scrobbled_Blocks_API::get_instance();
	$scrobbled_track = $scrobbled_api->get_now_playing();

	// Graceful degradation - render nothing if API fails or no tracks.
	if ( is_wp_error( $scrobbled_track ) || empty( $scrobbled_track ) ) {
		return '';
	}

	$scrobbled_show_artwork   = $scrobbled_blocks_attributes['showArtwork'] ?? true;
	$scrobbled_artwork_size   = $scrobbled_blocks_attributes['artworkSize'] ?? 64;
	$scrobbled_show_timestamp = $scrobbled_blocks_attributes['showTimestamp'] ?? true;
	$scrobbled_link_to_lastfm = $scrobbled_blocks_attributes['linkToLastFm'] ?? true;

	$scrobbled_wrapper_attributes = get_block_wrapper_attributes(
		array(
			'class' => 'wp-block-scrobble-blocks-now-playing',
			'style' => '--scrobble-artwork-size: ' . absint( $scrobbled_artwork_size ) . 'px;',
		)
	);

	$scrobbled_track_name  = $scrobbled_track['name'];
	$scrobbled_artist_name = $scrobbled_track['artist'];
	$scrobbled_album_name  = $scrobbled_track['album'];
	$scrobbled_artwork_url = $scrobbled_track['artwork'];
	$scrobbled_track_url   = $scrobbled_track['url'];
	$scrobbled_is_playing  = ! empty( $scrobbled_track['nowplaying'] );
	$scrobbled_timestamp   = $scrobbled_track['timestamp'] ?? null;

	// Generate ISO timestamp for datetime attribute.
	$scrobbled_iso_timestamp = $scrobbled_timestamp ? gmdate( 'c', $scrobbled_timestamp ) : '';
	$scrobbled_absolute_time = $scrobbled_timestamp ? wp_date( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), $scrobbled_timestamp ) : '';
	$scrobbled_relative_time = $scrobbled_is_playing ? __( 'Playing now', 'scrobbled-blocks' ) : scrobbled_blocks_get_relative_time( $scrobbled_timestamp );

	ob_start();
	?>
<div <?php echo wp_kses_post( $scrobbled_wrapper_attributes ); ?>>
	<?php if ( $scrobbled_show_artwork ) : ?>
		<div class="scrobble-artwork">
			<?php if ( $scrobbled_link_to_lastfm && $scrobbled_track_url ) : ?>
				<a href="<?php echo esc_url( $scrobbled_track_url ); ?>" target="_blank" rel="noopener noreferrer">
					<img src="<?php echo esc_url( $scrobbled_artwork_url ); ?>" alt="<?php echo esc_attr( sprintf( '%s by %s', $scrobbled_album_name, $scrobbled_artist_name ) ); ?>" loading="lazy" />
				</a>
			<?php else : ?>
				<img src="<?php echo esc_url( $scrobbled_artwork_url ); ?>" alt="<?php echo esc_attr( sprintf( '%s by %s', $scrobbled_album_name, $scrobbled_artist_name ) ); ?>" loading="lazy" />
			<?php endif; ?>
		</div>
	<?php endif; ?>
	<div class="scrobble-info">
		<span class="scrobble-track">
			<?php if ( $scrobbled_link_to_lastfm && $scrobbled_track_url ) : ?>
				<a href="<?php echo esc_url( $scrobbled_track_url ); ?>" target="_blank" rel="noopener noreferrer"><?php echo esc_html( $scrobbled_track_name ); ?></a>
			<?php else : ?>
				<?php echo esc_html( $scrobbled_track_name ); ?>
			<?php endif; ?>
		</span>
		<span class="scrobble-artist"><?php echo esc_html( $scrobbled_artist_name ); ?></span>
		<?php if ( $scrobbled_show_timestamp ) : ?>
			<time class="scrobble-timestamp" datetime="<?php echo esc_attr( $scrobbled_iso_timestamp ); ?>" title="<?php echo esc_attr( $scrobbled_absolute_time ); ?>">
				<?php echo esc_html( $scrobbled_relative_time ); ?>
			</time>
		<?php endif; ?>
	</div>
</div>
	<?php
	return ob_get_clean();
} )( $attributes );

// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Output is escaped within the closure.
echo $scrobbled_blocks_output;
