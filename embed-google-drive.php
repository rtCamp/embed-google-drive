<?php
/**
 * Plugin Name: Embed Google Drive
 * Description: Embed a link and preview of Google Drive Documents by pasting a shared document link into the editor.
 * Plugin URI: https://github.com/rtCamp/embed-google-drive
 * Version: 1.0
 * Author: rtCamp
 * Text Domain: embed-google-drive
 * Author URI: https://rtcamp.com/
 * Domain Path: /languages/
 *
 * @package rt-google-embeds
 */

defined( 'ABSPATH' ) || exit;

if ( ! defined( 'RT_GOOGLE_EMBEDS_PLUGIN_FILE' ) ) {
	define( 'RT_GOOGLE_EMBEDS_PLUGIN_FILE', __FILE__ );
}

if ( ! defined( 'RT_GOOGLE_EMBEDS_VERSION' ) ) {
	define( 'RT_GOOGLE_EMBEDS_VERSION', '1.2' );
}

if ( ! defined( 'RT_GOOGLE_EMBEDS_PLUGIN_DIR' ) ) {
	define( 'RT_GOOGLE_EMBEDS_PLUGIN_DIR', plugin_dir_path( RT_GOOGLE_EMBEDS_PLUGIN_FILE ) );
}

if ( ! class_exists( 'RtCamp_Google_Embeds' ) ) {
	require_once dirname( __FILE__ ) . '/includes/classes/class-rtcamp-google-embeds.php';
}
