/**
 * Scrobbled Blocks - Admin JavaScript
 *
 * @package ScrobbledBlocks
 */

( function( $ ) {
	'use strict';

	$( document ).ready( function() {
		// Toggle API key visibility
		$( '#toggle-api-key' ).on( 'click', function() {
			var $input = $( '#lastfm_api_key' );
			var $button = $( this );

			if ( $input.attr( 'type' ) === 'password' ) {
				$input.attr( 'type', 'text' );
				$button.text( $button.data( 'hide' ) || 'Hide' );
			} else {
				$input.attr( 'type', 'password' );
				$button.text( $button.data( 'show' ) || 'Show' );
			}
		} );

		// Media uploader for placeholder image
		var mediaUploader;

		$( '#select-placeholder-image' ).on( 'click', function( e ) {
			e.preventDefault();

			if ( mediaUploader ) {
				mediaUploader.open();
				return;
			}

			mediaUploader = wp.media( {
				title: 'Select Placeholder Image',
				button: {
					text: 'Use this image'
				},
				multiple: false,
				library: {
					type: 'image'
				}
			} );

			mediaUploader.on( 'select', function() {
				var attachment = mediaUploader.state().get( 'selection' ).first().toJSON();

				$( '#placeholder_image' ).val( attachment.id );

				var imageUrl = attachment.sizes && attachment.sizes.thumbnail
					? attachment.sizes.thumbnail.url
					: attachment.url;

				$( '#placeholder-image-preview' ).html(
					'<img src="' + imageUrl + '" alt="Placeholder preview" style="max-width: 150px; height: auto;" />'
				);

				$( '#remove-placeholder-image' ).show();
			} );

			mediaUploader.open();
		} );

		$( '#remove-placeholder-image' ).on( 'click', function( e ) {
			e.preventDefault();

			$( '#placeholder_image' ).val( '' );
			$( '#placeholder-image-preview' ).html( '' );
			$( this ).hide();
		} );
	} );
} )( jQuery );
