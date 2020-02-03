import { __ } from '@wordpress/i18n';
import { Component, Fragment } from '@wordpress/element';

class Save extends Component {
	render() {
		// Get required values.
		const { attributes } = this.props;
		const { docURL, previewURL } = attributes;

		return (
			<Fragment>
				<div className="rt-google-doc-embed">
					<a href={ docURL } title={ __( 'Open the Shared Document', 'rt-google-embeds' ) } target="_blank" rel="noopener noreferrer">
						{ __( 'Open the Shared Document', 'rt-google-embeds' ) }
					</a>
					<img src={ previewURL } alt={ __( 'Shared Document Preview', 'rt-google-embeds' ) } />
				</div>
			</Fragment>
		);
	}
}

export default Save;
