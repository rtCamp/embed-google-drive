<?php
/**
 * Plugin Name: Embed Google Drive
 * Description: Embed a link and preview of Google Drive Documents by pasting a shared document link into the editor.
 * Plugin URI: https://github.com/rtCamp/rt-google-embeds
 * Version: 1.0
 * Author: rtCamp
 * Text Domain: rt-google-embeds
 * Author URI: https://rtcamp.com/
 * Domain Path: /languages/
 *
 * @package rt-google-embeds
 */

defined( 'ABSPATH' ) || exit;

if ( ! defined( 'RT_GOOGLE_EMBEDS_PLUGIN_FILE' ) ) {
	define( 'RT_GOOGLE_EMBEDS_PLUGIN_FILE', __FILE__ );
}

if ( ! class_exists( 'rtCamp_Google_Embeds' ) ) {
	require_once dirname( __FILE__ ) . '/includes/classes/class-rtcamp-google-embeds.php';
}
