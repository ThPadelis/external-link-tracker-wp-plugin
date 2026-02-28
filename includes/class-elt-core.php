<?php
/**
 * Core bootstrap: wire REST, admin, and frontend script.
 *
 * @package ExternalLinkTracker
 */

namespace ExternalLinkTracker;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class ELT_Core
 */
class ELT_Core {

	/**
	 * Initialize the plugin.
	 */
	public static function init() {
		load_plugin_textdomain( 'external-link-tracker', false, dirname( plugin_basename( ELT_PLUGIN_FILE ) ) . '/languages' );
		ELT_Upgrader::maybe_upgrade();
		$self = new self();
		$self->load_dependencies();
		$self->register_hooks();
	}

	/**
	 * Load REST and Admin classes.
	 */
	private function load_dependencies() {
		require_once ELT_PLUGIN_DIR . 'includes/class-elt-rest.php';
		if ( is_admin() ) {
			require_once ELT_PLUGIN_DIR . 'admin/class-elt-admin.php';
		}
	}

	/**
	 * Register actions for REST, admin menu, and frontend script.
	 */
	private function register_hooks() {
		add_action( 'rest_api_init', array( ELT_REST::class, 'register_routes' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_frontend_script' ) );
		if ( is_admin() ) {
			add_action( 'admin_menu', array( ELT_Admin::class, 'add_menu_page' ) );
		}
	}

	/**
	 * Enqueue tracking script on the frontend only (not in admin).
	 */
	public function enqueue_frontend_script() {
		if ( is_admin() ) {
			return;
		}
		$handle = 'external-link-tracker';
		$src    = plugin_dir_url( ELT_PLUGIN_FILE ) . 'public/js/external-link-tracker.js';
		wp_enqueue_script( $handle, $src, array(), ELT_VERSION, true );
		wp_localize_script(
			$handle,
			'elt',
			array(
				'rest_url'        => rest_url( 'elt/v1/clicks' ),
				'nonce'           => wp_create_nonce( 'wp_rest' ),
				'source_url'      => $this->get_current_url(),
				'source_post_id'  => (int) get_queried_object_id(),
			)
		);
	}

	/**
	 * Get current page URL for source_url.
	 * Sanitized with esc_url_raw so it is safe to pass to the frontend (wp_localize_script).
	 *
	 * @return string
	 */
	private function get_current_url() {
		if ( isset( $_SERVER['HTTP_HOST'], $_SERVER['REQUEST_URI'] ) ) {
			$protocol = is_ssl() ? 'https://' : 'http://';
			$url      = $protocol . wp_unslash( $_SERVER['HTTP_HOST'] ) . wp_unslash( $_SERVER['REQUEST_URI'] );
			$url      = esc_url_raw( $url );
			if ( '' !== $url ) {
				return $url;
			}
		}
		return '';
	}
}
