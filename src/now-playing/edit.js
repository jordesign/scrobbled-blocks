/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';
import { useBlockProps, InspectorControls } from '@wordpress/block-editor';
import { PanelBody, ToggleControl, Placeholder, Spinner, Notice } from '@wordpress/components';
import { useEffect, useState } from '@wordpress/element';
import apiFetch from '@wordpress/api-fetch';

/**
 * Get relative time string
 *
 * @param {number} timestamp Unix timestamp
 * @return {string} Relative time string
 */
function getRelativeTime( timestamp ) {
	if ( ! timestamp ) {
		return __( 'just now', 'scrobbled-blocks' );
	}

	const now = Math.floor( Date.now() / 1000 );
	const diff = now - timestamp;

	if ( diff < 60 ) {
		return __( 'just now', 'scrobbled-blocks' );
	}

	if ( diff < 3600 ) {
		const minutes = Math.floor( diff / 60 );
		return minutes === 1
			? __( '1 minute ago', 'scrobbled-blocks' )
			: sprintf( __( '%d minutes ago', 'scrobbled-blocks' ), minutes );
	}

	if ( diff < 86400 ) {
		const hours = Math.floor( diff / 3600 );
		return hours === 1
			? __( '1 hour ago', 'scrobbled-blocks' )
			: sprintf( __( '%d hours ago', 'scrobbled-blocks' ), hours );
	}

	const days = Math.floor( diff / 86400 );
	return days === 1
		? __( '1 day ago', 'scrobbled-blocks' )
		: sprintf( __( '%d days ago', 'scrobbled-blocks' ), days );
}

/**
 * sprintf implementation for translations
 *
 * @param {string} format Format string
 * @param {...*}   args   Arguments
 * @return {string} Formatted string
 */
function sprintf( format, ...args ) {
	let i = 0;
	return format.replace( /%[sd]/g, () => args[ i++ ] );
}

/**
 * Edit component
 *
 * @param {Object} props               Component props
 * @param {Object} props.attributes    Block attributes
 * @param {Function} props.setAttributes Set attributes function
 * @return {JSX.Element} Edit component
 */
export default function Edit( { attributes, setAttributes } ) {
	const { showArtwork, showTimestamp, linkToLastFm } = attributes;
	const [ track, setTrack ] = useState( null );
	const [ isLoading, setIsLoading ] = useState( true );
	const [ error, setError ] = useState( null );

	const blockProps = useBlockProps( {
		className: 'wp-block-scrobble-blocks-now-playing',
	} );

	useEffect( () => {
		setIsLoading( true );
		setError( null );

		apiFetch( { path: '/scrobble-blocks/v1/recent-tracks?limit=1' } )
			.then( ( response ) => {
				if ( ! response.success ) {
					setError( response.error );
					setTrack( null );
				} else if ( response.tracks && response.tracks.length > 0 ) {
					setTrack( response.tracks[ 0 ] );
				} else {
					setError( __( 'No recent tracks found.', 'scrobbled-blocks' ) );
					setTrack( null );
				}
			} )
			.catch( ( err ) => {
				setError( err.message || __( 'Failed to fetch track data.', 'scrobbled-blocks' ) );
				setTrack( null );
			} )
			.finally( () => {
				setIsLoading( false );
			} );
	}, [] );

	const renderTrack = () => {
		if ( ! track ) {
			return null;
		}

		const TrackName = linkToLastFm ? 'a' : 'span';
		const trackProps = linkToLastFm
			? { href: track.url, target: '_blank', rel: 'noopener noreferrer' }
			: {};

		return (
			<>
				{ showArtwork && (
					<div className="scrobble-artwork">
						<img
							src={ track.artwork }
							alt={ `${ track.album } by ${ track.artist }` }
						/>
					</div>
				) }
				<div className="scrobble-info">
					<span className="scrobble-track">
						<TrackName { ...trackProps }>{ track.name }</TrackName>
					</span>
					<span className="scrobble-artist">{ track.artist }</span>
					{ showTimestamp && (
						<time className="scrobble-timestamp">
							{ track.nowplaying
								? __( 'Playing now', 'scrobbled-blocks' )
								: getRelativeTime( track.timestamp ) }
						</time>
					) }
				</div>
			</>
		);
	};

	return (
		<>
			<InspectorControls>
				<PanelBody title={ __( 'Display Settings', 'scrobbled-blocks' ) }>
					<ToggleControl
						label={ __( 'Show artwork', 'scrobbled-blocks' ) }
						checked={ showArtwork }
						onChange={ ( value ) => setAttributes( { showArtwork: value } ) }
					/>
					<ToggleControl
						label={ __( 'Show timestamp', 'scrobbled-blocks' ) }
						checked={ showTimestamp }
						onChange={ ( value ) => setAttributes( { showTimestamp: value } ) }
					/>
					<ToggleControl
						label={ __( 'Link to Last.fm', 'scrobbled-blocks' ) }
						checked={ linkToLastFm }
						onChange={ ( value ) => setAttributes( { linkToLastFm: value } ) }
					/>
				</PanelBody>
			</InspectorControls>
			<div { ...blockProps }>
				{ isLoading && (
					<Placeholder
						icon="controls-volumeon"
						label={ __( 'Now Playing', 'scrobbled-blocks' ) }
					>
						<Spinner />
					</Placeholder>
				) }
				{ ! isLoading && error && (
					<Placeholder
						icon="controls-volumeon"
						label={ __( 'Now Playing', 'scrobbled-blocks' ) }
					>
						<Notice status="warning" isDismissible={ false }>
							{ error }
						</Notice>
					</Placeholder>
				) }
				{ ! isLoading && ! error && track && renderTrack() }
			</div>
		</>
	);
}
