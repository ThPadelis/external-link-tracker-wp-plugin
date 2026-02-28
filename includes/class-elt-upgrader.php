<?php
/**
 * Run database and plugin upgrades when version increases.
 *
 * @package ExternalLinkTracker
 */

namespace ExternalLinkTracker;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class ELT_Upgrader
 */
class ELT_Upgrader {

	/**
	 * Option key for stored database schema version.
	 */
	const OPTION_DB_VERSION = 'elt_db_version';

	/**
	 * Run upgrades if stored version is lower than current.
	 */
	public static function maybe_upgrade() {
		$saved = (int) get_option( self::OPTION_DB_VERSION, 0 );
		if ( $saved >= ELT_DB_VERSION ) {
			return;
		}
		self::upgrade( $saved );
		update_option( self::OPTION_DB_VERSION, ELT_DB_VERSION );
	}

	/**
	 * Execute upgrades from a given schema version to current.
	 *
	 * @param int $from_version Previously stored db version.
	 */
	private static function upgrade( $from_version ) {
		// Ensure table exists and is up to date (covers fresh installs and schema changes).
		ELT_Activator::create_or_update_table();

		// Future migrations can be added here, e.g.:
		// if ( $from_version < 2 ) { ... add new column ... }
	}
}
