<?php
/**
 * Server-side rendering for the Recently Played block.
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

$number_of_items = $attributes['numberOfItems'] ?? 5;
$layout          = $attributes['layout'] ?? 'list';
$grid_columns    = $attributes['gridColumns'] ?? 3;
$show_artwork    = $attributes['showArtwork'] ?? true;
$show_timestamp  = $attributes['showTimestamp'] ?? true;
$link_to_lastfm  = $attributes['linkToLastFm'] ?? true;

$api    = Scrobbled_Blocks_API::get_instance();
$tracks = $api->get_recent_tracks( $number_of_items, 5 );

// Graceful degradation - render nothing if API fails or no tracks.
if ( is_wp_error( $tracks ) || empty( $tracks ) ) {
	return;
}

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

$class_name = 'wp-block-scrobble-blocks-recently-played is-layout-' . esc_attr( $layout );
$style      = '';

if ( 'grid' === $layout ) {
	$style = '--grid-columns: ' . absint( $grid_columns ) . ';';
}

$wrapper_attributes = get_block_wrapper_attributes( array(
	'class' => $class_name,
	'style' => $style,
) );

/**
 * Render a single track item.
 *
 * @param array  $track           Track data.
 * @param bool   $show_artwork    Whether to show artwork.
 * @param bool   $show_timestamp  Whether to show timestamp.
 * @param bool   $link_to_lastfm  Whether to link to Last.fm.
 * @return string HTML for the track item.
 */
if ( ! function_exists( 'scrobbled_blocks_render_track_item' ) ) {
function scrobbled_blocks_render_track_item( $track, $show_artwork, $show_timestamp, $link_to_lastfm ) {
	$track_name   = esc_html( $track['name'] );
	$artist_name  = esc_html( $track['artist'] );
	$album_name   = esc_html( $track['album'] );
	$artwork_url  = esc_url( $track['artwork'] );
	$track_url    = esc_url( $track['url'] );
	$is_playing   = ! empty( $track['nowplaying'] );
	$timestamp    = $track['timestamp'] ?? null;

	$iso_timestamp = $timestamp ? gmdate( 'c', $timestamp ) : '';
	$absolute_time = $timestamp ? wp_date( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), $timestamp ) : '';
	$relative_time = $is_playing ? __( 'Playing now', 'scrobbled-blocks' ) : scrobbled_blocks_get_relative_time( $timestamp );

	ob_start();
	?>
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
	<?php
	return ob_get_clean();
}
}

if ( 'list' === $layout ) :
?>
<ul <?php echo $wrapper_attributes; ?>>
	<?php foreach ( $tracks as $track ) : ?>
		<li class="scrobble-item">
			<?php echo scrobbled_blocks_render_track_item( $track, $show_artwork, $show_timestamp, $link_to_lastfm ); ?>
		</li>
	<?php endforeach; ?>
</ul>
<?php else : ?>
<div <?php echo $wrapper_attributes; ?>>
	<?php foreach ( $tracks as $track ) : ?>
		<div class="scrobble-item">
			<?php echo scrobbled_blocks_render_track_item( $track, $show_artwork, $show_timestamp, $link_to_lastfm ); ?>
		</div>
	<?php endforeach; ?>
</div>
<?php endif; ?>
