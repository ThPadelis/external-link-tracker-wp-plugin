<?php
/**
 * Plugin activation: create custom database table.
 *
 * @package ExternalLinkTracker
 */

namespace ExternalLinkTracker;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class ELT_Activator
 */
class ELT_Activator {

	/**
	 * Create or update the clicks table on plugin activation.
	 */
	public static function activate() {
		self::create_or_update_table();
		update_option( 'elt_db_version', ELT_DB_VERSION );
	}

	/**
	 * Create or update the clicks table (shared by activation and upgrader).
	 */
	public static function create_or_update_table() {
		global $wpdb;
		$table_name      = $wpdb->prefix . 'elt_clicks';
		$charset_collate = $wpdb->get_charset_collate();

		$sql = "CREATE TABLE $table_name (
			id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
			link_url varchar(2048) NOT NULL DEFAULT '',
			anchor_text varchar(500) NOT NULL DEFAULT '',
			source_url varchar(2048) NOT NULL DEFAULT '',
			source_post_id bigint(20) unsigned DEFAULT NULL,
			domain varchar(255) NOT NULL DEFAULT '',
			clicked_at datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
			PRIMARY KEY (id),
			KEY domain (domain),
			KEY clicked_at (clicked_at)
		) $charset_collate;";

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		dbDelta( $sql );
	}

	/**
	 * Check if the clicks table exists.
	 *
	 * @return bool
	 */
	public static function table_exists() {
		global $wpdb;
		$table = $wpdb->prefix . 'elt_clicks';
		return $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $table ) ) === $table;
	}
}
