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
		add_action( 'after_setup_theme', array( $this, 'rt_google_embed_add_editor_css' ) );
		add_action( 'init', array( $this, 'register_embeds' ) );
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
			'google_docs',
			$gdoc_oembed_pattern,
			array( $this, 'wpdocs_embed_handler_google_drive' )
		);

		// Google Sheets regex.
		$gsheet_oembed_pattern = '#https?:\\/\\/docs\\.google\\.com\\/spreadsheets\\/d\\/(.*)\\/(.*)?#i';
		wp_embed_register_handler(
			'google_sheets',
			$gsheet_oembed_pattern,
			array( $this, 'wpdocs_embed_handler_google_drive' )
		);

		// Google Slides regex.
		$gslides_oembed_pattern = '#https?:\\/\\/docs\\.google\\.com\\/presentation\\/d\\/(.*)\\/(.*)?#i';
		wp_embed_register_handler(
			'google_presentations',
			$gslides_oembed_pattern,
			array( $this, 'wpdocs_embed_handler_google_drive' )
		);

		// Common URL regex.
		$gdoc_common_oembed_pattern = '#https?:\\/\\/drive\\.google\\.com\\/open\\?id\\=(.*)?#i';
		wp_embed_register_handler(
			'google_doc_common',
			$gdoc_common_oembed_pattern,
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
	 * @return string
	 */
	private function get_thumbnail_url( $file_id ) {
		$no_preview_url = plugins_url( 'assets/img/no-preview.png', RT_GOOGLE_EMBEDS_PLUGIN_FILE );
		if ( empty( $file_id ) ) {
			return $no_preview_url;
		}
		// Check if a preview exists for supplied file id.
		$thumbnail_url = sprintf( 'https://drive.google.com/thumbnail?id=%s&sz=w400-h400', $file_id );
		$response      = wp_remote_get( $thumbnail_url );
		if ( ! is_wp_error( $response ) ) {
			$status_code = wp_remote_retrieve_response_code( $response );
			if ( 200 === $status_code ) {
				return $thumbnail_url;
			} else {
				return $no_preview_url;
			}
		}

		return $no_preview_url;
	}
}

// Initialize the class.
rtCamp_Google_Embeds::instance();
