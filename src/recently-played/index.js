/**
 * WordPress dependencies
 */
import { registerBlockType } from '@wordpress/blocks';

/**
 * Internal dependencies
 */
import Edit from './edit';
import metadata from './block.json';

/**
 * Register block type
 */
registerBlockType( metadata.name, {
	edit: Edit,
	// Server-side rendering via render.php
} );
