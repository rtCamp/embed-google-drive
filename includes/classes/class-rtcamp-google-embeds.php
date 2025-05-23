<?php
/**
 * Plugin Main Class
 *
 * @package embed-google-drive
 */

namespace Embed_Google_Drive;

use WP_REST_Request;
use WP_REST_Response;

defined( 'ABSPATH' ) || exit;

/**
 * Class rtCamp_Google_Embeds
 *
 * @package embed-google-drive
 */
class rtCamp_Google_Embeds {

	/**
	 * The single instance of the class.
	 *
	 * @var rtCamp_Google_Embeds
	 */
	protected static $instance = null;

	/**
	 * rtCamp_Google_Embeds Plugin Instance.
	 *
	 * @return rtCamp_Google_Embeds.
	 */
	public static function instance() {

		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * rtCamp_Google_Embeds constructor.
	 */
	public function __construct() {

		add_action( 'plugins_loaded', array( $this, 'load_textdomain' ) );
		add_action( 'init', array( $this, 'register_embeds' ) );
		add_action( 'rest_api_init', array( $this, 'register_routes' ) );

		// Register custom oembed provider for google drive urls.
		add_filter( 'oembed_providers', array( $this, 'oembed_providers' ) );
	}

	/**
	 * Register custom oembed provider for google drive urls.
	 *
	 * @param array $providers Default providers.
	 *
	 * @return array Modified providers.
	 */
	public function oembed_providers( $providers ) {

		global $wp_rewrite;

		if ( null === $wp_rewrite ) {
			return $providers;
		}

		$formats = array(
			'#https?:\\/\\/docs\\.google\\.com\\/document\\/d\\/(.*)\\/(.*)?#i',
			'#https?:\\/\\/docs\\.google\\.com\\/forms\\/d\\/(.*)\\/(.*)?#i',
			'#https?:\\/\\/docs\\.google\\.com\\/spreadsheets\\/d\\/(.*)\\/(.*)?#i',
			'#https?:\\/\\/docs\\.google\\.com\\/presentation\\/d\\/(.*)\\/(.*)?#i',
			'#https?:\\/\\/drive\\.google\\.com\\/open\\?id\\=(.*)?#i',
			'#https?:\\/\\/drive\\.google\\.com\\/file\\/d\\/(.*)\\/(.*)?#i',
			'#https?:\\/\\/docs\\.google\\.com\\/drawings\\/d\\/(.*)\\/(.*)?#i',
		);

		foreach ( $formats as $format ) {
			$providers[ $format ] = array( get_rest_url( null, 'embed-google-drive/v1/oembed' ), true );
		}

		return $providers;
	}

	/**
	 * Loads plugin text-domain.
	 *
	 * @return void
	 */
	public function load_textdomain() {

		load_plugin_textdomain( 'embed-google-drive', false, EMBED_GOOGLE_DRIVE_PLUGIN_DIR . 'languages/' );
	}

	/**
	 * Registers all supported embeds.
	 *
	 * @return void
	 */
	public function register_embeds() {

		// Google Docs regex.
		$gdoc_oembed_pattern = '#https?:\\/\\/docs\\.google\\.com\\/document\\/d\\/(.*)\\/(.*)?#i';
		wp_embed_register_handler(
			'rt_google_docs',
			$gdoc_oembed_pattern,
			array( $this, 'wpdocs_embed_handler_google_drive' )
		);

		// Google Forms regex.
		$gdoc_oembed_pattern = '#https?:\\/\\/docs\\.google\\.com\\/forms\\/d\\/(.*)\\/(.*)?#i';
		wp_embed_register_handler(
			'rt_google_forms',
			$gdoc_oembed_pattern,
			array( $this, 'wpdocs_embed_handler_google_drive' )
		);

		// Google Sheets regex.
		$gsheet_oembed_pattern = '#https?:\\/\\/docs\\.google\\.com\\/spreadsheets\\/d\\/(.*)\\/(.*)?#i';
		wp_embed_register_handler(
			'rt_google_sheets',
			$gsheet_oembed_pattern,
			array( $this, 'wpdocs_embed_handler_google_drive' )
		);

		// Google Slides regex.
		$gslides_oembed_pattern = '#https?:\\/\\/docs\\.google\\.com\\/presentation\\/d\\/(.*)\\/(.*)?#i';
		wp_embed_register_handler(
			'rt_google_presentations',
			$gslides_oembed_pattern,
			array( $this, 'wpdocs_embed_handler_google_drive' )
		);

		// Common URL regex.
		$gdrive_common_oembed_pattern = '#https?:\\/\\/drive\\.google\\.com\\/open\\?id\\=(.*)?#i';
		wp_embed_register_handler(
			'rt_google_doc_common',
			$gdrive_common_oembed_pattern,
			array( $this, 'wpdocs_embed_handler_google_drive' )
		);

		// Common file URL regex.
		$gdrive_common_file_oembed_pattern = '#https?:\\/\\/drive\\.google\\.com\\/file\\/d\\/(.*)\\/(.*)?#i';
		wp_embed_register_handler(
			'rt_google_file_common',
			$gdrive_common_file_oembed_pattern,
			array( $this, 'wpdocs_embed_handler_google_drive' )
		);

		// Google drawings regex.
		$gdrawings_oembed_pattern = '#https?:\\/\\/docs\\.google\\.com\\/drawings\\/d\\/(.*)\\/(.*)?#i';
		wp_embed_register_handler(
			'rt_google_drawings',
			$gdrawings_oembed_pattern,
			array( $this, 'wpdocs_embed_handler_google_drive' )
		);
	}

	/**
	 * Render preview for provided URL.
	 *
	 * @param array  $matches The RegEx matches from the provided regex when calling
	 *                        wp_embed_register_handler().
	 * @param array  $attr    Embed attributes.
	 * @param string $url     The original URL that was matched by the regex.
	 *
	 * @return false|string
	 */
	public function wpdocs_embed_handler_google_drive( $matches, $attr, $url ) {

		$thumbnail_url = $this->get_thumbnail_url( $matches[1] );

		if ( ! $thumbnail_url ) {
			return '';
		}

		return $this->render_embed(
			'google-drive-file',
			array(
				'drive_file_url' => $url,
				'thumbnail_url'  => $thumbnail_url,
			)
		);
	}

	/**
	 * Wrapper function to render embed markup.
	 *
	 * @param string $type Template file to be loaded.
	 * @param array  $data File and Thumbnail URL info.
	 *
	 * @return false|string
	 */
	public function render_embed( $type, $data ) {

		ob_start();
		$template = sprintf( 'templates/embeds/%s.php', $type );

		if ( ! empty( $data ) && is_array( $data ) ) {
			extract( $data, EXTR_OVERWRITE );
		}

		include EMBED_GOOGLE_DRIVE_PLUGIN_DIR . $template; // phpcs:ignore WordPressVIPMinimum.Files.IncludingFile.UsingVariable

		return ob_get_clean();
	}

	/**
	 * If a valid URL isn't provided return a placeholder image URL.
	 *
	 * @param string $file_id Google Document File Id.
	 *
	 * @return string|boolean
	 */
	private function get_thumbnail_url( $file_id ) {

		if ( empty( $file_id ) ) {
			return false;
		}

		// Check if a preview exists for supplied file id.
		$thumbnail_url = sprintf( 'https://drive.google.com/thumbnail?id=%s&sz=w400-h400', $file_id );

		if ( function_exists( 'vip_safe_wp_remote_get' ) ) {
			$response = vip_safe_wp_remote_get( $thumbnail_url );
		} else {
			$response = wp_remote_get( $thumbnail_url );
		}

		if ( ! is_wp_error( $response ) ) {

			// Check if retrieved content is image and not google sign up page.
			$content_type = wp_remote_retrieve_header( $response, 'content-type' );
			if ( str_contains( $content_type, 'image/' ) ) {

				// Check if retrieved http code is 200.
				$status_code = wp_remote_retrieve_response_code( $response );
				if ( 200 === $status_code ) {
					return $thumbnail_url;
				}
			}
		}

		return false;
	}

	/**
	 * Register endpoints.
	 *
	 * @return void
	 */
	public function register_routes() {

		register_rest_route(
			'embed-google-drive/v1',
			'/get-preview-url',
			array(
				'methods'             => 'GET',
				'callback'            => array( $this, 'get_thumb_preview' ),
				'args'                => array(
					'media_id' => array(
						'file_id' => true,
					),
				),
				'permission_callback' => '__return_true',
			)
		);

		// Route for custom oembed provider for google drive.
		register_rest_route(
			'embed-google-drive/v1',
			'/oembed',
			array(
				'methods'             => 'GET',
				'callback'            => array( $this, 'oembed' ),
				'permission_callback' => '__return_true',
			)
		);
	}

	/**
	 * REST API callback to get drive preview URL on block editor.
	 *
	 * @param WP_REST_Request $request REST request Instance.
	 *
	 * @return WP_REST_Response
	 */
	public function oembed( $request ) {

		// Get id from url query string.
		$url = $request->get_param( 'url' );

		$file_id = $this->get_file_id_from_url( $url );
		if ( empty( $file_id ) ) {
			return new WP_REST_Response( array(), 404 );
		}

		// Get preview url.
		$thumbnail_url = $this->get_thumbnail_url( $file_id );

		// If permission is not set or invalid url, send 404.
		if ( empty( $thumbnail_url ) ) {
			return new WP_REST_Response( array(), 404 );
		}

		// Data to send as response.
		$data = array(
			'type'    => 'rich', // We want to show rich html.
			'version' => '1.0',
		);

		// Set maxheight.
		if ( ! empty( $request->get_param( 'maxheight' ) ) ) {
			$data['height'] = $request->get_param( 'maxheight' );
		}

		// Set maxwidth.
		if ( ! empty( $request->get_param( 'maxwidth' ) ) ) {
			$data['width'] = $request->get_param( 'maxwidth' );
		}

		// Set html.
		$data['html'] = $this->render_embed(
			'google-drive-file',
			array(
				'drive_file_url' => $url,
				'thumbnail_url'  => $thumbnail_url,
			)
		);

		$data['thumbnail_url'] = $thumbnail_url;

		return new WP_REST_Response( $data, 200 );
	}

	/**
	 * Gets file id from drive URL.
	 *
	 * @param string $url File URL.
	 *
	 * @return bool|string Returns false or ID.
	 */
	public function get_file_id_from_url( $url ) {

		$matches = array();
		preg_match( '/[-\w]{25,}/', $url, $matches );
		if ( empty( $matches[0] ) ) {
			return false;
		}

		return $matches[0];
	}

	/**
	 * REST API callback to get drive preview URL.
	 *
	 * @param WP_REST_Request $request REST Instance.
	 *
	 * @return WP_REST_Response
	 */
	public function get_thumb_preview( $request ) {

		$url = $request->get_param( 'url' );

		$file_id = $this->get_file_id_from_url( $url );
		if ( empty( $file_id ) ) {
			return new WP_REST_Response( array(), 404 );
		}

		$data['preview_url'] = $this->get_thumbnail_url( $file_id );
		return new WP_REST_Response( $data, 200 );
	}
}

// Initialize the class.
rtCamp_Google_Embeds::instance();
