import apiFetch from '@wordpress/api-fetch';
import { __ } from '@wordpress/i18n';
import { Component, Fragment } from '@wordpress/element';
import { BlockControls } from '@wordpress/block-editor';
import { IconButton, Placeholder, Spinner, Toolbar, TextControl, withNotices } from '@wordpress/components';
import { settings } from './settings.js';

const { rtGoogleEmbedBlockData } = window;

// No preview image if preview doesn't exist.
const noPreviewURL = rtGoogleEmbedBlockData.noPreviewURL;

class Edit extends Component {
	/**
	 * Initialize the component.
	 */
	constructor() {
		super( ...arguments );

		// Our initial value for internal management.
		this.state = {
			preview: false,
			apiRequestOutstanding: false,
		};

		// Bind all the methods.
		this.apiCall = this.apiCall.bind( this );
		this.onError = this.onError.bind( this );
	}

	/**
	 * Check if we have an URL, if so set preview the doc.
	 */
	componentDidMount() {
		if ( this.props.attributes.previewURL ) {
			this.setState( { preview: true } );
		}
	}

	/**
	 * Get the file id for provided URL.
	 *
	 * @param {string} docURL Shared Document URL.
	 * @return {string} Returns filed id for preview..
	 */
	getFileID( docURL ) {
		// List of URL regex's supported by the plugin.
		const regexList = {
			doc: /https:\/\/docs\.google\.com\/document\/d\/(?<fileid>.*?)\/(.*)/,
			sheets: /https:\/\/docs\.google\.com\/spreadsheets\/d\/(?<fileid>.*?)\/(.*)/,
			sheet: /https:\/\/docs\.google\.com\/spreadsheets\/d\/(?<fileid>.*?)\/(.*)/,
			slides: /https:\/\/docs\.google\.com\/presentation\/d\/(?<fileid>.*?)\/(.*)/,
			commonDrive: /https:\/\/drive\.google\.com\/open\?id=(?<fileid>.*)/,
			normaFile: /https:\/\/drive\.google\.com\/file\/d\/(?<fileid>.*?)\/(.*)/,
		};

		// Store regex keys to iterate over.
		let isValidURL = false;
		const allRegex = Object.keys( regexList );

		// Check if the current URL passes at least on of supported patterns.
		allRegex.some( function( item, index ) { // eslint-disable-line no-unused-vars
			const regexItem = regexList[ item ];
			if ( ( regexItem.exec( docURL ) ) !== null ) {
				isValidURL = true;
				return true;
			}
			return false;
		} );

		// If no pattern matched bail.
		if ( ! isValidURL ) {
			return '';
		}

		// Get the fileid of the pasted URL if valid.
		let fileId = '';
		if ( docURL.trim().length ) {
			// Get URL data.
			const parsedUrl = new URL( docURL );

			// Get data to match against.
			const pathName = parsedUrl.pathname;
			const searchQuery = parsedUrl.search;

			// Regex to get the file id.
			const regex = /\/(document|spreadsheets|presentation|file)\/d\/(?<fileid>.*?)\/(.*)/;
			const openFileRegex = /(id)=(?<fileid>.*)/;

			// Check if we have any matches.
			const matches = pathName.match( regex );
			const openFileRegexMatches = searchQuery.match( openFileRegex );

			// If we have a match, return the file id.
			if ( null !== matches && matches.hasOwnProperty( 'groups' ) && matches.groups.fileid.length ) {
				fileId = matches.groups.fileid;
			} else if ( null !== openFileRegexMatches && openFileRegexMatches.hasOwnProperty( 'groups' ) && openFileRegexMatches.groups.fileid.length ) {
				fileId = openFileRegexMatches.groups.fileid;
			}
		}

		return fileId;
	}

	/**
	 * Call our endpoint and get the preview image URL.
	 *
	 * @param {string} docURL Added document URL.
	 */
	apiCall( docURL ) {
		this.props.setAttributes( { docURL } );
		const fileID = this.getFileID( docURL );
		const { noticeOperations } = this.props;
		if ( fileID ) {
			this.setState( { apiRequestOutstanding: true }, () => {
				// Fetch pricing info and set in attributes.
				apiFetch( {
					path: '/rt-google-embed/v1/get-preview-url?file_id=' + fileID,
				} ).then( ( response ) => {
					noticeOperations.removeAllNotices();
					this.props.setAttributes( { previewURL: response.preview_url } );
					this.setState( { apiRequestOutstanding: false, preview: true } );
				}, ( response ) => { // eslint-disable-line no-unused-vars
					this.props.setAttributes( { previewURL: noPreviewURL } );
					this.onError( __( 'Something went wrong', 'rt-google-embeds' ) );
					this.setState( { apiRequestOutstanding: false, preview: true } );
				} );
			} );
		} else {
			this.onError( __( 'Invalid Drive URL.', 'rt-google-embeds' ) );
			this.setState( { preview: false } );
		}
	}

	/**
	 * Set error for failed conditions.
	 *
	 * @param {string} message Error message.
	 */
	onError( message ) {
		const { noticeOperations } = this.props;
		noticeOperations.removeAllNotices();
		noticeOperations.createErrorNotice( message );
	}

	/**
	 * Render the markup in admin area.
	 *
	 * @return {*} Return the markup for admin area.
	 */
	render() {
		// Required variables to manage the edit UI.
		const { className, attributes, noticeUI, notices } = this.props;
		const { docURL, previewURL } = attributes;
		const { apiRequestOutstanding, preview } = this.state;

		// Block controls.
		const blockControls = (
			<BlockControls>
				<Toolbar>
					<IconButton
						icon="edit"
						label={ preview ? __( 'Edit file URL', 'rt-google-embeds' ) : __( 'Show Preview', 'rt-google-embeds' ) }
						onClick={ () => this.setState( { preview: ! preview } ) }
					/>
				</Toolbar>
			</BlockControls>
		);

		// Placeholder to show the spinner when preview is loading.
		const placholderAPIStateLoading = (
			<Placeholder icon={ settings.icon }>
				<Spinner />
			</Placeholder>
		);

		// UI to show controls when no there is no preview.
		const noDocURL = (
			<Placeholder icon={ settings.icon } label={ __( 'Google Drive File Preview', 'map-blocks' ) } notices={ notices }>
				<Fragment>
					{ previewURL && blockControls }
					<div className={ className }>
						{ __( 'Paste your Google Drive Sharing URL into the field below.', 'rt-google-embeds' ) }
					</div>
					<TextControl
						placeholder={ __( 'Paste File URL Here', 'rt-google-embeds' ) }
						value={ docURL }
						onChange={ ( newDocURL ) => this.apiCall( newDocURL ) }
					/>
				</Fragment>
			</Placeholder>
		);

		// If we have a preview URL show the image.
		const showPreview = (
			<Fragment>
				{ previewURL && blockControls }
				<div className="rt-google-doc-embed">
					<a href={ docURL } title={ __( 'Open the Shared Document', 'rt-google-embeds' ) } target="_blank" rel="noopener noreferrer">
						{ __( 'Open the Shared Document', 'rt-google-embeds' ) }
					</a>
					<img src={ previewURL } alt={ __( 'Shared Document Preview', 'rt-google-embeds' ) } />
				</div>
			</Fragment>
		);

		return (
			<Fragment>
				{ noticeUI }
				{ ! preview && apiRequestOutstanding && placholderAPIStateLoading }
				{ ! preview && ! apiRequestOutstanding && noDocURL }
				{ preview && showPreview }
			</Fragment>
		);
	}
}

export default withNotices( Edit );
