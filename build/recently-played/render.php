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

// Wrap in IIFE to avoid global variable pollution.
$scrobbled_blocks_output = ( static function ( $scrobbled_blocks_attributes ) {
	$scrobbled_settings = Scrobbled_Blocks_Settings::get_instance();

	// Check if plugin is configured.
	if ( ! $scrobbled_settings->is_configured() ) {
		return '';
	}

	$scrobbled_number_of_items = $scrobbled_blocks_attributes['numberOfItems'] ?? 5;
	$scrobbled_layout          = $scrobbled_blocks_attributes['layout'] ?? 'list';
	$scrobbled_grid_columns    = $scrobbled_blocks_attributes['gridColumns'] ?? 3;
	$scrobbled_show_artwork    = $scrobbled_blocks_attributes['showArtwork'] ?? true;
	$scrobbled_show_timestamp  = $scrobbled_blocks_attributes['showTimestamp'] ?? true;
	$scrobbled_link_to_lastfm  = $scrobbled_blocks_attributes['linkToLastFm'] ?? true;

	$scrobbled_api    = Scrobbled_Blocks_API::get_instance();
	$scrobbled_tracks = $scrobbled_api->get_recent_tracks( $scrobbled_number_of_items, 5 );

	// Graceful degradation - render nothing if API fails or no tracks.
	if ( is_wp_error( $scrobbled_tracks ) || empty( $scrobbled_tracks ) ) {
		return '';
	}

	$scrobbled_class_name = 'wp-block-scrobble-blocks-recently-played is-layout-' . esc_attr( $scrobbled_layout );
	$scrobbled_style      = '';

	if ( 'grid' === $scrobbled_layout ) {
		$scrobbled_style = '--grid-columns: ' . absint( $scrobbled_grid_columns ) . ';';
	}

	$scrobbled_wrapper_attributes = get_block_wrapper_attributes(
		array(
			'class' => $scrobbled_class_name,
			'style' => $scrobbled_style,
		)
	);

	/**
	 * Render a single track item.
	 *
	 * @param array $scrobbled_track_data      Track data.
	 * @param bool  $scrobbled_show_art        Whether to show artwork.
	 * @param bool  $scrobbled_show_time       Whether to show timestamp.
	 * @param bool  $scrobbled_link_lastfm     Whether to link to Last.fm.
	 * @return string HTML for the track item.
	 */
	$scrobbled_render_track_item = static function ( $scrobbled_track_data, $scrobbled_show_art, $scrobbled_show_time, $scrobbled_link_lastfm ) {
		$scrobbled_item_track_name  = $scrobbled_track_data['name'];
		$scrobbled_item_artist_name = $scrobbled_track_data['artist'];
		$scrobbled_item_album_name  = $scrobbled_track_data['album'];
		$scrobbled_item_artwork_url = $scrobbled_track_data['artwork'];
		$scrobbled_item_track_url   = $scrobbled_track_data['url'];
		$scrobbled_item_is_playing  = ! empty( $scrobbled_track_data['nowplaying'] );
		$scrobbled_item_timestamp   = $scrobbled_track_data['timestamp'] ?? null;

		$scrobbled_item_iso_timestamp = $scrobbled_item_timestamp ? gmdate( 'c', $scrobbled_item_timestamp ) : '';
		$scrobbled_item_absolute_time = $scrobbled_item_timestamp ? wp_date( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), $scrobbled_item_timestamp ) : '';
		$scrobbled_item_relative_time = $scrobbled_item_is_playing ? __( 'Playing now', 'scrobbled-blocks' ) : scrobbled_blocks_get_relative_time( $scrobbled_item_timestamp );

		ob_start();
		?>
	<?php if ( $scrobbled_show_art ) : ?>
		<div class="scrobble-artwork">
			<?php if ( $scrobbled_link_lastfm && $scrobbled_item_track_url ) : ?>
				<a href="<?php echo esc_url( $scrobbled_item_track_url ); ?>" target="_blank" rel="noopener noreferrer">
					<img src="<?php echo esc_url( $scrobbled_item_artwork_url ); ?>" alt="<?php echo esc_attr( sprintf( '%s by %s', $scrobbled_item_album_name, $scrobbled_item_artist_name ) ); ?>" loading="lazy" />
				</a>
			<?php else : ?>
				<img src="<?php echo esc_url( $scrobbled_item_artwork_url ); ?>" alt="<?php echo esc_attr( sprintf( '%s by %s', $scrobbled_item_album_name, $scrobbled_item_artist_name ) ); ?>" loading="lazy" />
			<?php endif; ?>
		</div>
	<?php endif; ?>
	<div class="scrobble-info">
		<span class="scrobble-track">
			<?php if ( $scrobbled_link_lastfm && $scrobbled_item_track_url ) : ?>
				<a href="<?php echo esc_url( $scrobbled_item_track_url ); ?>" target="_blank" rel="noopener noreferrer"><?php echo esc_html( $scrobbled_item_track_name ); ?></a>
			<?php else : ?>
				<?php echo esc_html( $scrobbled_item_track_name ); ?>
			<?php endif; ?>
		</span>
		<span class="scrobble-artist"><?php echo esc_html( $scrobbled_item_artist_name ); ?></span>
		<?php if ( $scrobbled_show_time ) : ?>
			<time class="scrobble-timestamp" datetime="<?php echo esc_attr( $scrobbled_item_iso_timestamp ); ?>" title="<?php echo esc_attr( $scrobbled_item_absolute_time ); ?>">
				<?php echo esc_html( $scrobbled_item_relative_time ); ?>
			</time>
		<?php endif; ?>
	</div>
		<?php
		return ob_get_clean();
	};

	ob_start();

	if ( 'list' === $scrobbled_layout ) :
		?>
<ul <?php echo wp_kses_post( $scrobbled_wrapper_attributes ); ?>>
		<?php foreach ( $scrobbled_tracks as $scrobbled_track_item ) : ?>
		<li class="scrobble-item">
			<?php
			// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Output is escaped in the render function.
			echo $scrobbled_render_track_item( $scrobbled_track_item, $scrobbled_show_artwork, $scrobbled_show_timestamp, $scrobbled_link_to_lastfm );
			?>
		</li>
	<?php endforeach; ?>
</ul>
	<?php else : ?>
<div <?php echo wp_kses_post( $scrobbled_wrapper_attributes ); ?>>
		<?php foreach ( $scrobbled_tracks as $scrobbled_track_item ) : ?>
		<div class="scrobble-item">
			<?php
			// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Output is escaped in the render function.
			echo $scrobbled_render_track_item( $scrobbled_track_item, $scrobbled_show_artwork, $scrobbled_show_timestamp, $scrobbled_link_to_lastfm );
			?>
		</div>
	<?php endforeach; ?>
</div>
		<?php
	endif;

	return ob_get_clean();
} )( $attributes );

// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Output is escaped within the closure.
echo $scrobbled_blocks_output;
