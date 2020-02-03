import { __ } from '@wordpress/i18n';
import driveIcon from './icon';

export const settings = {
	title: __( 'Google Drive File Preview', 'rt-google-embeds' ),
	icon: driveIcon,
	category: 'common',
	keywords: [
		__( 'Google Drive File Preview', 'rt-google-embeds' ),
		__( 'Drive File Preview', 'rt-google-embeds' ),
		__( 'Drive', 'rt-google-embeds' ),
	],
	attributes: {
		previewURL: {
			type: 'string',
		},
		docURL: {
			type: 'string',
		},
	},
};
