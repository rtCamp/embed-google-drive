<?php
/**
 * Plugin Name: Embed Google Drive
 * Description: Embed a link and preview of Google Drive Documents by pasting a shared document link into the editor.
 * Plugin URI: https://github.com/rtCamp/embed-google-drive
 * Version: 1.2.1
 * Author: rtCamp
 * Text Domain: embed-google-drive
 * Author URI: https://rtcamp.com/
 * Domain Path: /languages
 *
 * @package embed-google-drive
 */

defined( 'ABSPATH' ) || exit;

if ( ! defined( 'EMBED_GOOGLE_DRIVE_PLUGIN_FILE' ) ) {
	define( 'EMBED_GOOGLE_DRIVE_PLUGIN_FILE', __FILE__ );
}

if ( ! defined( 'EMBED_GOOGLE_DRIVE_VERSION' ) ) {
	define( 'EMBED_GOOGLE_DRIVE_VERSION', '1.2.1' );
}

if ( ! defined( 'EMBED_GOOGLE_DRIVE_PLUGIN_DIR' ) ) {
	define( 'EMBED_GOOGLE_DRIVE_PLUGIN_DIR', plugin_dir_path( EMBED_GOOGLE_DRIVE_PLUGIN_FILE ) );
}

if ( ! class_exists( 'EMBED_GOOGLE_DRIVE\Embed_Google_Drive' ) ) {
	require_once EMBED_GOOGLE_DRIVE_PLUGIN_DIR . '/includes/classes/class-embed-google-drive.php';
}
