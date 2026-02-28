<?php
/**
 * Admin menu and view container.
 *
 * @package ExternalLinkTracker
 */

namespace ExternalLinkTracker;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class ELT_Admin
 */
class ELT_Admin {

	/**
	 * Admin menu slug.
	 */
	const PAGE_SLUG = 'external-link-tracker';

	/**
	 * Add menu page and render callback.
	 */
	public static function add_menu_page() {
		$hook = add_menu_page(
			__( 'External Link Tracker', 'external-link-tracker' ),
			__( 'External Link Tracker', 'external-link-tracker' ),
			'manage_options',
			self::PAGE_SLUG,
			array( self::class, 'render_page' ),
			'dashicons-external',
			30
		);
		add_action( 'load-' . $hook, array( self::class, 'enqueue_admin_assets' ) );
	}

	/**
	 * Enqueue SPA assets on External Link Tracker page only.
	 */
	public static function enqueue_admin_assets() {
		$assets_url  = plugin_dir_url( ELT_PLUGIN_FILE ) . 'admin/dist/';
		$assets_path = ELT_PLUGIN_DIR . 'admin/dist/';

		$css_file = $assets_path . 'elt-admin.css';
		if ( file_exists( $css_file ) ) {
			wp_enqueue_style( 'elt-admin-spa', $assets_url . 'elt-admin.css', array(), (string) filemtime( $css_file ) );
		}

		$js_file = $assets_path . 'elt-admin.js';
		if ( ! file_exists( $js_file ) ) {
			return;
		}

		wp_enqueue_script( 'elt-admin-spa', $assets_url . 'elt-admin.js', array(), (string) filemtime( $js_file ), true );
		wp_script_add_data( 'elt-admin-spa', 'type', 'module' );
		wp_add_inline_script(
			'elt-admin-spa',
			'window.eltAdmin=' . wp_json_encode(
				array(
					'restBase' => esc_url_raw( rest_url( 'elt/v1/' ) ),
					'nonce'    => wp_create_nonce( 'wp_rest' ),
					'pageUrl'  => esc_url_raw( admin_url( 'admin.php?page=' . self::PAGE_SLUG ) ),
				)
			) . ';',
			'before'
		);
	}

	/**
	 * Render the admin page container for the SPA.
	 */
	public static function render_page() {
		ELT_Logger::log( 'render_page SPA container' );
		?>
		<div class="wrap elt-admin">
			<?php if ( ELT_Logger::enabled() ) : ?>
			<p class="notice notice-info" style="margin: 1em 0;"><strong><?php esc_html_e( 'External Link Tracker debug logging is on.', 'external-link-tracker' ); ?></strong> <?php esc_html_e( 'Check wp-content/debug.log (or your server error log) for [ELT] entries.', 'external-link-tracker' ); ?></p>
			<?php endif; ?>
			<h1>
				<?php esc_html_e( 'External Link Tracker', 'external-link-tracker' ); ?>
				<span class="elt-version" style="font-size:0.5em;font-weight:normal;color:#646970;"><?php echo esc_html( ELT_VERSION ); ?></span>
			</h1>
			<div id="elt-admin-app"></div>
		</div>
		<?php
	}
}
