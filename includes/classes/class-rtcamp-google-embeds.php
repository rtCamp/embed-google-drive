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
		add_action( 'wp_enqueue_scripts', array( $this, 'rt_google_embed_enqueue_scripts' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'rt_google_embed_enqueue_scripts' ) );
		add_action( 'after_setup_theme', array( $this, 'rt_google_embed_add_editor_css' ) );
		add_action( 'init', array( $this, 'register_embeds' ) );
		add_action( 'init', array( $this, 'blocks_init' ) );
		
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
		$formats = array(
			'#https?:\\/\\/docs\\.google\\.com\\/document\\/d\\/(.*)\\/(.*)?#i',
			'#https?:\\/\\/docs\\.google\\.com\\/spreadsheets\\/d\\/(.*)\\/(.*)?#i',
			'#https?:\\/\\/docs\\.google\\.com\\/presentation\\/d\\/(.*)\\/(.*)?#i',
			'#https?:\\/\\/drive\\.google\\.com\\/open\\?id\\=(.*)?#i',
			'#https?:\\/\\/drive\\.google\\.com\\/file\\/d\\/(.*)\\/(.*)?#i'
		);
		
		foreach ( $formats as $format ) {
			$providers[ $format ] = array( get_rest_url() . 'rt-google-embed/v1/oembed', true );
		}
	
		return $providers;
	}

	/**
	 * Define required plugin constants.
	 */
	private function add_plugin_constants() {
		define( 'RT_GOOGLE_EMBEDS_VERSION', '0.1.0' );
		define( 'RT_GOOGLE_EMBEDS_PLUGIN_DIR', plugin_dir_path( RT_GOOGLE_EMBEDS_PLUGIN_FILE ) );
	}

	/**
	 * Loads plugin textdomain.
	 */
	public function load_textdomain() {
		load_plugin_textdomain( 'rt-google-embeds', false, RT_GOOGLE_EMBEDS_PLUGIN_DIR . 'languages/' );
	}

	/**
	 * Enqueue the styles required by the embed.
	 */
	public function rt_google_embed_enqueue_scripts() {
		wp_register_style(
			'rt-google-embed-post-view',
			plugins_url( 'build/rt-google-embed-main.css', RT_GOOGLE_EMBEDS_PLUGIN_FILE ),
			array(),
			RT_GOOGLE_EMBEDS_VERSION
		);
		wp_enqueue_style( 'rt-google-embed-post-view' );
	}

	/**
	 * Add style inside the editor required by the embed.
	 */
	public function rt_google_embed_add_editor_css() {
		add_editor_style( plugins_url( 'build/rt-google-embed-main.css', RT_GOOGLE_EMBEDS_PLUGIN_FILE ) );
	}

	/**
	 * Registers all supported embeds.
	 */
	public function register_embeds() {
		// Google Docs regex.
		$gdoc_oembed_pattern = '#https?:\\/\\/docs\\.google\\.com\\/document\\/d\\/(.*)\\/(.*)?#i';
		wp_embed_register_handler(
			'rt_google_docs',
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
	}

	/**
	 * Registers all block assets so that they can be enqueued through the block editor in the corresponding context.
	 */
	public function blocks_init() {

		add_action( 'rest_api_init', array( $this, 'register_routes' ) );

		// Load dependencies and version from build file.
		$script_asset_path = RT_GOOGLE_EMBEDS_PLUGIN_DIR . 'build/index.asset.php';
		$script_asset      = require $script_asset_path;

		// Register Blocks Script.
		wp_register_script(
			'rt-google-embed-blocks-script-assets',
			plugins_url( 'build/index.js', RT_GOOGLE_EMBEDS_PLUGIN_FILE ),
			$script_asset['dependencies'],
			$script_asset['version'],
			true
		);

		// Data to be used in blocks.
		$rt_embed_data = [
			'noPreviewURL' => plugins_url( 'assets/img/no-preview.png', RT_GOOGLE_EMBEDS_PLUGIN_FILE ),
		];

		// Pass data to block script.
		wp_localize_script(
			'rt-google-embed-blocks-script-assets',
			'rtGoogleEmbedBlockData',
			$rt_embed_data
		);

		// Register Google Drive File Preview Block.
		register_block_type(
			'rt-google-embed/drive-file-preview',
			array(
				'editor_script' => 'rt-google-embed-blocks-script-assets',
				'style'         => 'rt-google-embed-post-view',
			)
		);

		// Sets translated strings for a script.
		wp_set_script_translations(
			'rt-google-embed-blocks-script-assets',
			'rt-google-embeds',
			RT_GOOGLE_EMBEDS_PLUGIN_FILE . 'languages/'
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
	 *
	 * @return string|boolean
	 */
	private function get_thumbnail_url( $file_id ) {
		if ( empty( $file_id ) ) {
			return false;
		}

		// Check if a preview exists for supplied file id.
		$thumbnail_url = sprintf( 'https://drive.google.com/thumbnail?id=%s&sz=w400-h400', $file_id );
		$response      = wp_remote_get( $thumbnail_url );
		if ( ! is_wp_error( $response ) ) {
			
			// Check if retrieved content is image and not google sign up page.
			$content_type = wp_remote_retrieve_header( $response, 'content-type' );
			if ( false !== strpos( $content_type, 'image/' ) ) {

				// Check if retrieved http code is 200.
				$status_code = wp_remote_retrieve_response_code( $response );
				if ( 200 === $status_code ) {
					return $thumbnail_url;
				} else {
					return false;
				}
			}
		}

		return false;
	}

	/**
	 * Register endpoints.
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
			'/oembed',
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
		$url        = $request->get_param( 'url' );
		$parsed_url = wp_parse_url( $url );
		// Return 404 if no query string found.
		if ( empty( $parsed_url['query'] ) ) {
			return new \WP_REST_Response( array(), 404 );
		}

		// Return 404 if no id found.
		parse_str( $parsed_url['query'], $params );
		if ( empty( $params['id'] ) ) {
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

		// Get preview url.
		$thumbnail_url = $this->get_thumbnail_url( $params['id'] );

		// If permission is not set or invalid url, send 404.
		if ( empty( $thumbnail_url ) ) {
			return new \WP_REST_Response( array(), 404 );
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
	 * REST API callback to get drive preview URL.
	 *
	 * @param \WP_REST_Request $request REST Instance.
	 *
	 * @return \WP_REST_Response
	 */
	public function get_thumb_preview( \WP_REST_Request $request ) {
		$file_id             = $request->get_param( 'file_id' );
		$data['preview_url'] = $this->get_thumbnail_url( $file_id );
		return new \WP_REST_Response( $data, 200 );
	}
}

// Initialize the class.
rtCamp_Google_Embeds::instance();
