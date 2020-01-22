<?php
/**
 * Embed Template File.
 *
 * @package rt-google-embeds
 */

if ( ! defined( 'ABSPATH' ) ) {
	// prevent direct access to this file.
	exit;
}
?>
<div class="rt-google-doc-embed">
	<a href="<?php echo esc_url( $drive_file_url ); ?>" title="<?php esc_attr_e( 'Open the Shared Document', 'rt-google-embeds' ); ?>" target="_blank">
		<?php esc_html_e( 'Open Google Document.', 'rt-google-embeds' ); ?>
	</a>
	<img src="<?php echo esc_url( $thumbnail_url ); ?>" alt="<?php esc_attr_e( 'Shared Document Preview', 'rt-google-embeds' ); ?>" />
</div>
