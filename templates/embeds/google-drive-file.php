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
if ( file_exists( $cached_file ) ) : ?>
	<div style="border: 1px solid #000; text-align: center;">
		<a href="<?php echo esc_url( $drive_file_url ); ?>" title="<?php esc_attr_e( 'Open the Shared Document', 'rt-google-embeds' ); ?>" target="_blank" rel="noopener noreferrer" style="color: #cd2653;">
			<?php esc_html_e( 'Open Shared Document.', 'rt-google-embeds' ); ?>
		</a>
		<img src="<?php echo esc_url( $cached_url ); ?>" alt="<?php esc_attr_e( 'Shared Document Preview from Cache', 'rt-google-embeds' ); ?>" style="border: 1px solid #eee; margin: 15px auto; display: block;" />
	</div>
<?php else : ?>
	<div style="border: 1px solid #000; text-align: center;">
		<a href="<?php echo esc_url( $drive_file_url ); ?>" title="<?php esc_attr_e( 'Open the Shared Document', 'rt-google-embeds' ); ?>" target="_blank" rel="noopener noreferrer" style="color: #cd2653;">
			<?php esc_html_e( 'Open Shared Document.', 'rt-google-embeds' ); ?>
		</a>
		<img src="<?php echo esc_url( $thumbnail_url ); ?>" alt="<?php esc_attr_e( 'Shared Document Preview', 'rt-google-embeds' ); ?>" style="border: 1px solid #eee; margin: 15px auto; display: block;" />
	</div>
<?php endif; ?>
