<?php
/**
 * Plugin Main Class
 *
 * @package rt-google-embeds
 */

namespace RT_Google_Embeds;

defined( 'ABSPATH' ) || exit;

/**
 * Class rtCamp_Google_Embeds
 *
 * @package rt-google-embeds
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
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * rtCamp_Google_Embeds constructor.
	 */
	public function __construct() {
		$this->add_plugin_constants();
		add_action( 'plugins_loaded', array( $this, 'load_textdomain' ) );
		add_action( 'init', array( $this, 'register_embeds' ) );
		add_action( 'rest_api_init', array( $this, 'register_routes' ) );
		add_action( 'wp_google_login_token', array( $this, 'wp_google_login_token' ), 10, 3 );

		// Register custom oembed provider for google drive urls.
		add_filter( 'oembed_providers', array( $this, 'oembed_providers' ) );
		add_filter( 'wp_google_login_scopes', array( $this, 'wp_google_login_scopes' ) );

	}

	/**
	 * Receives the converted token after user logs in via Google account.
	 *
	 * @param array  $token      Converted access token.
	 * @param array  $user_array User information fetched from token.
	 * @param object $client     Google_Client object in use.
	 *
	 * @return void
	 */
	public function wp_google_login_token( $token, $user_info, $client ) {
		// Check if necessary details are there.
		if ( empty( $token['access_token'] ) || empty( $user_info['user_email'] ) ) {
			return;
		}

		// Get user by email.
		$user = get_user_by( 'email', $user_info['user_email'] );
		if ( empty( $user->ID ) ) {
			return;
		}

		// Store access token in user meta.
		update_user_meta( $user->ID, 'rt-google-embeds-access-token', $token['access_token'] );
	}

	/**
	 * Adds drive scope in Google scopes list.
	 *
	 * @param array $scopes Scopes used in Google sign in.
	 *
	 * @return array Modified scopes.
	 */
	public function wp_google_login_scopes( $scopes ) {
		$drive_scope = 'https://www.googleapis.com/auth/drive';
		// Add drive scope if it's not already added.
		if ( ! in_array( $drive_scope, $scopes, true ) ) {
			$scopes[] = $drive_scope;
		}

		return $scopes;
	}

	/**
	 * Register custom oembed provider for google drive urls.
	 *
	 * @param array $providers Default providers.
	 *
	 * @return array Modified providers.
	 */
	public function oembed_providers( $providers ) {
		$formats = array(
			'#https?:\\/\\/docs\\.google\\.com\\/document\\/d\\/(.*)\\/(.*)?#i',
			'#https?:\\/\\/docs\\.google\\.com\\/forms\\/d\\/(.*)\\/(.*)?#i',
			'#https?:\\/\\/docs\\.google\\.com\\/spreadsheets\\/d\\/(.*)\\/(.*)?#i',
			'#https?:\\/\\/docs\\.google\\.com\\/presentation\\/d\\/(.*)\\/(.*)?#i',
			'#https?:\\/\\/drive\\.google\\.com\\/open\\?id\\=(.*)?#i',
			'#https?:\\/\\/drive\\.google\\.com\\/file\\/d\\/(.*)\\/(.*)?#i',
			'#https?:\\/\\/docs\\.google\\.com\\/drawings\\/d\\/(.*)\\/(.*)?#i',
		);

		// Pass current user id in URL so callback can receive it.
		$user_id = \get_current_user_id();
		if ( empty( $user_id ) ) {
			$user_id = 1;
		}
		$url = sprintf( 'rt-google-embed/v1/oembed/%s', $user_id );

		foreach ( $formats as $format ) {
			$providers[ $format ] = array( get_rest_url( null, $url ), true );
		}

		return $providers;
	}

	/**
	 * Define required plugin constants.
	 *
	 * @return void
	 */
	private function add_plugin_constants() {
		define( 'RT_GOOGLE_EMBEDS_VERSION', '0.1.0' );
		define( 'RT_GOOGLE_EMBEDS_PLUGIN_DIR', plugin_dir_path( RT_GOOGLE_EMBEDS_PLUGIN_FILE ) );
	}

	/**
	 * Loads plugin textdomain.
	 *
	 * @return void
	 */
	public function load_textdomain() {
		load_plugin_textdomain( 'rt-google-embeds', false, RT_GOOGLE_EMBEDS_PLUGIN_DIR . 'languages/' );
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

		// Common Drawings regex.
		$gdrive_common_file_oembed_pattern = '#https?:\\/\\/docs\\.google\\.com\\/drawings\\/d\\/(.*)\\/(.*)?#i';
		wp_embed_register_handler(
			'rt_google_drawings',
			$gdrive_common_file_oembed_pattern,
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
		$thumbnail_url = $this->get_thumbnail_url( $matches[1], get_current_user_id() );

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
		include RT_GOOGLE_EMBEDS_PLUGIN_DIR . $template; // phpcs:ignore
		$embed_markup = ob_get_clean();

		return $embed_markup;
	}

	/**
	 * If a valid URL isn't provided return a placeholder image URL.
	 *
	 * @param string $file_id Google Document File Id.
	 * @param int    $user_id Currently logged in user id.
	 *
	 * @return string|boolean
	 */
	private function get_thumbnail_url( $file_id, $user_id = false ) {
		if ( empty( $file_id ) ) {
			return false;
		}

		// Check if a preview exists for supplied file id.
		$thumbnail_url = sprintf( 'https://drive.google.com/thumbnail?id=%s&sz=w400-h400', $file_id );
		$response      = wp_remote_get( $thumbnail_url );
		$contents      = wp_remote_retrieve_body( $response );
		if ( ! is_wp_error( $response ) ) {
			// Check if retrieved content is image and not google sign up page.
			$content_type = wp_remote_retrieve_header( $response, 'content-type' );
			if ( false !== strpos( $content_type, 'image/' ) ) {
				// Check if retrieved http code is 200.
				$status_code = wp_remote_retrieve_response_code( $response );
				if ( 200 === $status_code ) {
					// Save the thumbnail.
					$this->save_thumbnail( $file_id, $contents );

					return $thumbnail_url;
				}
			}
		}

		// If file is private, we use google drive API key and access token to fetch thumbnail link.
		if ( empty( $user_id ) || ! defined( 'WP_GOOGLE_DRIVE_API_KEY' ) ) {
			return false;
		}

		// Fetch access token from currently logged in user's meta.
		$access_token = get_user_meta( $user_id, 'rt-google-embeds-access-token', true );
		if ( empty( $access_token ) ) {
			return false;
		}

		// Set API url.
		$url  = sprintf( 'https://www.googleapis.com/drive/v2/files/%s?key=%s', $file_id, WP_GOOGLE_DRIVE_API_KEY );
		// Set headers.
		$args = array(
			'headers' => array(
				'Authorization' => sprintf( 'Bearer %s', $access_token ),
				'Referer'       => $_SERVER['SERVER_NAME'],
				'Accept'        => 'application/json',
			)
		);

		// Call API.
		$resp = wp_remote_get( $url, $args );
		// Check if response has json body.
		if ( empty( $resp['body'] ) ) {
			return false;
		}

		// Decode json and get thumbnailLink if exists.
		// Refer https://developers.google.com/drive/api/v2/reference/files/get.
		$body = json_decode( $resp['body'], true );
		if ( ! empty( $body['thumbnailLink'] ) ) {
			return $body['thumbnailLink'];
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
			'rt-google-embed/v1',
			'/get-preview-url',
			[
				'methods'  => 'GET',
				'callback' => [ $this, 'get_thumb_preview' ],
				'args'     => [
					'media_id' => [
						'file_id' => true,
					],
				],
			]
		);

		// Route for custom oembed provider for google drive.
		register_rest_route(
			'rt-google-embed/v1',
			'/oembed/(?P<id>\d+)',
			array(
				'methods'  => 'GET',
				'callback' => array( $this, 'oembed' ),
			)
		);
	}

	/**
	 * REST API callback to get drive preview URL on block editor.
	 *
	 * @param \WP_REST_Request $request REST request Instance.
	 *
	 * @return \WP_REST_Response
	 */
	public function oembed( \WP_REST_Request $request ) {
		// Get id from url query string.
		$url = $request->get_param( 'url' );
		// Current user id.
		$user_id = 1;
		// Get url params.
		$url_params = $request->get_url_params();
		// Fetch user_id from url params.
		if ( ! empty( $url_params['id'] ) ) {
			$user_id = intval( $url_params['id'] );
		}

		$file_id = $this->get_file_id_from_url( $url );
		if ( empty( $file_id ) ) {
			return new \WP_REST_Response( array(), 404 );
		}

		// Get preview url.
		$thumbnail_url = $this->get_thumbnail_url( $file_id, $user_id );

		// If permission is not set or invalid url, send 404.
		if ( empty( $thumbnail_url ) ) {
			return new \WP_REST_Response( array(), 404 );
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

		return new \WP_REST_Response( $data, 200 );
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
	 * @param \WP_REST_Request $request REST Instance.
	 *
	 * @return \WP_REST_Response
	 */
	public function get_thumb_preview( \WP_REST_Request $request ) {
		$url = $request->get_param( 'url' );

		$file_id = $this->get_file_id_from_url( $url );
		if ( empty( $file_id ) ) {
			return new \WP_REST_Response( array(), 404 );
		}

		$data['preview_url'] = $this->get_thumbnail_url( $file_id );
		return new \WP_REST_Response( $data, 200 );
	}

	/**
	 * Save thumbnail of a doc.
	 *
	 * @param string $file_id File ID.
	 * @param mixed  $contents Contents of file.
	 *
	 * @return void
	 */
	public function save_thumbnail( $file_id, $contents ) {
		$upload_dir = wp_upload_dir();
		$basedir    = '';

		if ( ! empty( $upload_dir['basedir'] ) ) {
			$basedir = $upload_dir['basedir'];
		}
		$uploadpath = $basedir . '/cache/wp-google-drive/';

		if ( ! file_exists( $uploadpath ) ) {
			mkdir( $uploadpath, 0755, true );
		}

		$uploadfile = $uploadpath . "{$file_id}.png";

		if ( file_exists( $uploadfile ) ) {
			return;
		}

		file_put_contents( $uploadfile, $contents ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_read_file_put_contents -- Safe to write image in the cache directory.
	}
}

// Initialize the class.
rtCamp_Google_Embeds::instance();
