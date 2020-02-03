import { registerBlockType } from '@wordpress/blocks';

import { settings as blockSettings } from './settings';
import Edit from './edit';
import Save from './save';

registerBlockType( 'rt-google-embed/drive-file-preview', {
	title: blockSettings.title,
	category: blockSettings.category,
	icon: blockSettings.icon,
	keywords: blockSettings.keywords,
	attributes: blockSettings.attributes,
	edit: Edit,
	save: Save,
} );
